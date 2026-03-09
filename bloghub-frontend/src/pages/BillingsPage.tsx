import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { paymentsApi, type PaymentForUser } from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import LoadingPage from '../components/LoadingPage';

function formatDate(iso: string | null): string {
  if (!iso) return '—';
  return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
}

function formatAmount(amount: number, currency: string | null): string {
  return new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: currency || 'USD',
  }).format(amount);
}

export default function BillingsPage() {
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [payments, setPayments] = useState<PaymentForUser[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { replace: true });
      return;
    }
  }, [user, authLoading, navigate]);

  useEffect(() => {
    if (!user) return;
    let cancelled = false;
    setLoading(true);
    setError(null);
    paymentsApi
      .list()
      .then((list) => {
        if (!cancelled) setPayments(Array.isArray(list) ? list : []);
      })
      .catch((e) => {
        if (!cancelled) setError(e instanceof Error ? e.message : 'Failed to load billing history');
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => { cancelled = true; };
  }, [user]);

  if (authLoading || !user) {
    return <LoadingPage />;
  }

  return (
    <div className="page-center memberships-page">
      <div className="memberships-content">
        <div className="memberships-header">
          <h1 className="profile-name">Billings</h1>
          <p className="memberships-subtitle">
            Your payment and billing history for tier subscriptions
          </p>
        </div>

        {error && (
          <p className="memberships-error" role="alert">
            {error}
          </p>
        )}

        {loading ? (
          <LoadingPage message="Loading billing history…" />
        ) : payments.length === 0 ? (
          <div className="card memberships-empty" style={{ marginTop: '1rem' }}>
            <p className="memberships-empty-text">No payments yet</p>
            <p className="memberships-empty-hint">
              When you subscribe to a creator’s tier, payment records will appear here
            </p>
            <Link to="/explore" className="btn btn-primary" style={{ marginTop: '1rem' }}>
              Explore creators
            </Link>
          </div>
        ) : (
          <ul className="billings-list">
            {payments.map((payment) => {
              const creator = payment.subscription?.creator;
              const tierName = payment.subscription?.tier_name ?? 'Tier';
              const creatorLabel = creator?.display_name ?? creator?.slug ?? 'Creator';
              const creatorSlug = creator?.slug;

              return (
                <li key={payment.id} className="billing-card">
                  <div className="billing-card-main">
                    <div className="billing-card-info">
                      <span className="billing-card-amount">
                        {formatAmount(payment.amount, payment.currency)}
                      </span>
                      <span className="billing-card-date">
                        {formatDate(payment.checkout_date)}
                      </span>
                      <span className="billing-card-context">
                        {creatorSlug ? (
                          <Link to={`/creator/${creatorSlug}`} className="billing-card-link">
                            {creatorLabel}
                          </Link>
                        ) : (
                          creatorLabel
                        )}
                        {tierName && ` · ${tierName}`}
                      </span>
                      {payment.card_last4 && (
                        <span className="billing-card-card">•••• {payment.card_last4}</span>
                      )}
                      <span className={`billing-card-status billing-card-status-${(payment.payment_status || '').toLowerCase()}`}>
                        {payment.payment_status || '—'}
                      </span>
                    </div>
                  </div>
                  {creatorSlug && (
                    <Link to={`/creator/${creatorSlug}`} className="btn btn-secondary btn-sm billing-card-action">
                      View creator
                    </Link>
                  )}
                </li>
              );
            })}
          </ul>
        )}
      </div>
    </div>
  );
}
