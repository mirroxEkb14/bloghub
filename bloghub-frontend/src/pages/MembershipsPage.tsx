import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { subscriptionsApi, type SubscriptionWithTier } from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';

function formatDate(iso: string | null): string {
  if (!iso) return '—';
  return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
}

export default function MembershipsPage() {
  const { user, loading: authLoading } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const [subscriptions, setSubscriptions] = useState<SubscriptionWithTier[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [cancelingId, setCancelingId] = useState<number | null>(null);

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
    subscriptionsApi
      .list()
      .then((list) => {
        if (!cancelled) setSubscriptions(Array.isArray(list) ? list : []);
      })
      .catch((e) => {
        if (!cancelled) setError(e instanceof Error ? e.message : 'Failed to load memberships');
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => { cancelled = true; };
  }, [user]);

  async function handleCancel(sub: SubscriptionWithTier) {
    setError(null);
    setCancelingId(sub.id);
    try {
      const res = await subscriptionsApi.cancel(sub.id);
      setSubscriptions((prev) =>
        prev.map((s) => (s.id === sub.id ? res.subscription : s))
      );
      const label = res.subscription?.creator?.display_name && res.subscription?.tier?.tier_name
        ? `${res.subscription.creator.display_name} – ${res.subscription.tier.tier_name}`
        : 'Membership';
      showToast(`${label} canceled`, 'success');
    } catch (e) {
      showToast(e instanceof Error ? e.message : 'Failed to cancel membership', 'error');
    } finally {
      setCancelingId(null);
    }
  }

  if (authLoading || !user) {
    return <LoadingPage />;
  }

  return (
    <div className="page-center memberships-page">
      <div className="memberships-content">
        <div className="memberships-header">
          <h1 className="profile-name">Memberships</h1>
          <p className="memberships-subtitle">
            Your active and past tier subscriptions. You can cancel anytime
          </p>
        </div>

        {error && (
          <p className="memberships-error" role="alert">
            {error}
          </p>
        )}

        {loading ? (
          <LoadingPage message="Loading memberships…" />
        ) : subscriptions.length === 0 ? (
          <div className="card memberships-empty">
            <p className="memberships-empty-text">You don’t have any memberships yet</p>
            <p className="memberships-empty-hint">
              Subscribe to a creator’s tier from their page to see it here
            </p>
            <Link to="/explore" className="btn btn-primary" style={{ marginTop: '1rem' }}>
              Explore creators
            </Link>
          </div>
        ) : (
          <ul className="memberships-list">
          {subscriptions.map((sub) => {
            const endDate = sub.end_date ? new Date(sub.end_date) : null;
            const isExpired = endDate !== null && endDate < new Date();
            const isActive = sub.sub_status === 'Active' && !isExpired;
            const statusLabel = sub.sub_status === 'Canceled'
              ? 'Canceled'
              : isExpired
                ? 'Expired'
                : 'Active';
            const creatorSlug = sub.creator?.slug;
            const creatorName = sub.creator?.display_name ?? sub.creator?.slug ?? 'Creator';
            const tierName = sub.tier?.tier_name ?? 'Tier';
            const price = sub.tier?.price;
            const currency = sub.tier?.tier_currency ?? 'USD';

            return (
              <li key={sub.id} className={`membership-card ${!isActive ? 'membership-card-inactive' : ''}`}>
                <div className="membership-card-main">
                  <div className="membership-card-avatar-wrap">
                    {sub.creator?.profile_avatar_url ? (
                      <img
                        src={sub.creator.profile_avatar_url}
                        alt=""
                        className="membership-card-avatar"
                      />
                    ) : (
                      <span className="membership-card-avatar-placeholder">
                        {creatorName.charAt(0).toUpperCase()}
                      </span>
                    )}
                  </div>
                  <div className="membership-card-info">
                    {creatorSlug ? (
                      <Link to={`/creator/${creatorSlug}`} className="membership-card-creator">
                        {creatorName}
                      </Link>
                    ) : (
                      <span className="membership-card-creator">{creatorName}</span>
                    )}
                    <span className="membership-card-tier">{tierName}</span>
                    {price != null && (
                      <span className="membership-card-price">
                        {new Intl.NumberFormat(undefined, {
                          style: 'currency',
                          currency: currency || 'USD',
                        }).format(price)}
                        {isActive && ' / period'}
                      </span>
                    )}
                    <div className="membership-card-dates">
                      {formatDate(sub.start_date)} – {formatDate(sub.end_date)}
                    </div>
                    <span className={`membership-card-status membership-card-status-${statusLabel.toLowerCase()}`}>
                      {statusLabel}
                    </span>
                  </div>
                </div>
                <div className="membership-card-actions">
                  {creatorSlug && (
                    <Link to={`/creator/${creatorSlug}`} className="btn btn-secondary btn-sm">
                      View profile
                    </Link>
                  )}
                  {isActive && (
                    <button
                      type="button"
                      className="btn btn-secondary btn-sm membership-card-cancel"
                      disabled={cancelingId === sub.id}
                      onClick={() => handleCancel(sub)}
                    >
                      {cancelingId === sub.id ? 'Canceling…' : 'Cancel subscription'}
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
