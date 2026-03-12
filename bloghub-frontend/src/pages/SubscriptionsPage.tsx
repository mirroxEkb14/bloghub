import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { subscriptionsApi, type SubscriptionWithTier } from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { CircleCheckIcon } from '../components/icons';
import { useAuth } from '../contexts/AuthContext';

export default function SubscriptionsPage() {
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [subscriptions, setSubscriptions] = useState<SubscriptionWithTier[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [cancelingId, setCancelingId] = useState<number | null>(null);
  const [cancelToastMessage, setCancelToastMessage] = useState<string | null>(null);

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/login', { state: { from: '/subscriptions' }, replace: true });
    }
  }, [user, authLoading, navigate]);

  useEffect(() => {
    if (!user) return;
    let cancelled = false;
    setLoading(true);
    setError(null);
    (async () => {
      try {
        const list = await subscriptionsApi.list();
        if (!cancelled) setSubscriptions(Array.isArray(list) ? list : []);
      } catch (e) {
        if (!cancelled) setError(e instanceof Error ? e.message : 'Failed to load subscriptions');
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [user]);

  const TOAST_BAR_S = 4;
  const TOAST_VISIBLE_MS = 4100;

  useEffect(() => {
    if (!cancelToastMessage) return;
    const t = setTimeout(() => setCancelToastMessage(null), TOAST_VISIBLE_MS);
    return () => clearTimeout(t);
  }, [cancelToastMessage]);

  async function handleCancel(sub: SubscriptionWithTier) {
    setError(null);
    setCancelingId(sub.id);
    try {
      const res = await subscriptionsApi.cancel(sub.id);
      setSubscriptions((prev) =>
        prev.map((s) => (s.id === sub.id ? res.subscription : s))
      );
      const label = res.subscription?.tier?.tier_name && res.subscription?.creator?.display_name
        ? `${res.subscription.creator.display_name} – ${res.subscription.tier.tier_name}`
        : 'Subscription';
      setCancelToastMessage(`${label} canceled`);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to cancel subscription');
    } finally {
      setCancelingId(null);
    }
  }

  if (authLoading || !user) {
    return <LoadingPage />;
  }

  return (
    <div className="page-center" style={{ padding: '2rem 1rem' }}>
      {cancelToastMessage && (
        <div
          key={cancelToastMessage}
          className="subscription-toast subscription-toast-success"
          role="status"
          aria-live="polite"
          aria-label="Canceled"
          style={{ ['--toast-duration' as string]: `${TOAST_BAR_S}s` }}
        >
          <span className="subscription-toast-icon subscription-toast-icon-success" aria-hidden>
            <CircleCheckIcon size={20} />
          </span>
          <p className="subscription-toast-msg">
            {cancelToastMessage}
          </p>
          <button
            type="button"
            className="subscription-toast-close"
            onClick={() => setCancelToastMessage(null)}
            aria-label="Dismiss"
          >
            ×
          </button>
          <div className="subscription-toast-timer" aria-hidden />
        </div>
      )}

      <div className="card" style={{ maxWidth: 640, width: '100%' }}>
        <h1 className="form-title">My Subscriptions</h1>
        <p className="form-subtitle" style={{ marginBottom: '1.5rem' }}>
          Creators and tiers you’re subscribed to. You can cancel anytime
        </p>

        {error && (
          <p className="form-subtitle" style={{ color: 'var(--color-error, #f87171)', marginBottom: '1rem' }}>
            {error}
          </p>
        )}

        {loading ? (
          <LoadingPage message="Loading subscriptions…" />
        ) : subscriptions.length === 0 ? (
          <p className="form-subtitle" style={{ marginTop: '1rem' }}>
            You don’t have any subscriptions yet
          </p>
        ) : (
          <ul style={{ listStyle: 'none', padding: 0, margin: 0, display: 'flex', flexDirection: 'column', gap: '1rem' }}>
            {subscriptions.map((sub) => {
              const endDate = sub.end_date ? new Date(sub.end_date) : null;
              const isExpired = endDate !== null && endDate < new Date();
              const isActive = sub.sub_status === 'Active' && !isExpired;
              const statusLabel = sub.sub_status === 'Canceled'
                ? 'Canceled'
                : isExpired
                  ? `Expired ${endDate.toLocaleDateString(undefined, { dateStyle: 'medium' })}`
                  : `Active until ${endDate?.toLocaleDateString(undefined, { dateStyle: 'medium' }) ?? '—'}`;
              return (
                <li
                  key={sub.id}
                  style={{
                    padding: '1rem',
                    borderRadius: 'var(--radius-sm)',
                    border: '1px solid var(--border)',
                    background: 'var(--bg-input)',
                    display: 'flex',
                    flexWrap: 'wrap',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    gap: '0.75rem',
                  }}
                >
                  <div style={{ flex: '1 1 200px' }}>
                    {sub.creator?.slug ? (
                      <Link
                        to={`/creator/${sub.creator.slug}`}
                        style={{ fontWeight: 600, color: 'inherit' }}
                      >
                        {sub.creator.display_name ?? sub.creator.slug}
                      </Link>
                    ) : (
                      <span style={{ fontWeight: 600 }}>
                        {sub.creator?.display_name ?? 'Creator'}
                      </span>
                    )}
                    <span style={{ color: 'var(--text-secondary)', marginLeft: '0.5rem' }}>
                      · {sub.tier?.tier_name ?? 'Tier'}
                    </span>
                    <div style={{ fontSize: '0.875rem', color: 'var(--text-secondary)', marginTop: '0.25rem' }}>
                      <span style={isExpired ? { opacity: 0.85, fontStyle: 'italic' } : undefined}>
                        {statusLabel}
                      </span>
                      {isExpired && (
                        <span style={{ display: 'block', fontSize: '0.8125rem', marginTop: '0.25rem', opacity: 0.8 }}>
                          Resubscribe from the creator’s profile
                        </span>
                      )}
                    </div>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    {sub.creator?.slug ? (
                      <Link to={`/creator/${sub.creator.slug}`} className="btn btn-secondary btn-sm">
                        View profile
                      </Link>
                    ) : null}
                    {isActive && (
                      <button
                        type="button"
                        className="btn btn-secondary btn-sm"
                        style={{ borderColor: 'var(--color-error, #f87171)', color: 'var(--color-error, #f87171)' }}
                        disabled={cancelingId === sub.id}
                        onClick={() => handleCancel(sub)}
                      >
                        {cancelingId === sub.id ? 'Canceling…' : 'Cancel'}
                      </button>
                    )}
                  </div>
                </li>
              );
            })}
          </ul>
        )}
      </div>
    </div>
  );
}
