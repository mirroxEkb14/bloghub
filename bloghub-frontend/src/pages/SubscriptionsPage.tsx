import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { subscriptionsApi, type SubscriptionWithTier } from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { useAuth } from '../contexts/AuthContext';

export default function SubscriptionsPage() {
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [subscriptions, setSubscriptions] = useState<SubscriptionWithTier[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [cancelingId, setCancelingId] = useState<number | null>(null);
  const [cancelToast, setCancelToast] = useState<boolean>(false);

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

  const TOAST_DURATION_MS = 4000;

  useEffect(() => {
    if (!cancelToast) return;
    const t = setTimeout(() => setCancelToast(false), TOAST_DURATION_MS);
    return () => clearTimeout(t);
  }, [cancelToast]);

  async function handleCancel(sub: SubscriptionWithTier) {
    setError(null);
    setCancelingId(sub.id);
    try {
      const res = await subscriptionsApi.cancel(sub.id);
      setSubscriptions((prev) =>
        prev.map((s) => (s.id === sub.id ? res.subscription : s))
      );
      setCancelToast(true);
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
      {cancelToast && (
        <div
          className="subscription-toast subscription-toast-success"
          role="status"
          aria-live="polite"
          aria-label="Canceled"
          style={{ ['--toast-duration' as string]: '4s' }}
        >
          <span className="subscription-toast-icon subscription-toast-icon-success" aria-hidden>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="10" cy="10" r="9" stroke="currentColor" strokeWidth="2" />
              <path d="M6 10l3 3 5-6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </span>
          <p className="subscription-toast-msg">
            Subscription canceled
          </p>
          <button
            type="button"
            className="subscription-toast-close"
            onClick={() => setCancelToast(false)}
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
            {subscriptions.map((sub) => (
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
                    {sub.sub_status === 'Active' ? (
                      <>Active until {sub.end_date ? new Date(sub.end_date).toLocaleDateString(undefined, { dateStyle: 'medium' }) : '—'}</>
                    ) : (
                      <span style={{ opacity: 0.8 }}>Canceled</span>
                    )}
                  </div>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                  {sub.creator?.slug ? (
                    <Link to={`/creator/${sub.creator.slug}`} className="btn btn-secondary btn-sm">
                      View profile
                    </Link>
                  ) : null}
                  {sub.sub_status === 'Active' && (
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
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
