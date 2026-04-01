import { useEffect, useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { subscriptionsApi, type SubscriptionWithTier } from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { useAuth } from '../contexts/AuthContext';
import '../styles/memberships.css';

function formatDate(iso: string | null): string {
  if (!iso) return '—';
  return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
}

export default function SupportingPage() {
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [subscriptions, setSubscriptions] = useState<SubscriptionWithTier[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/login', { state: { from: '/subscriptions/supporting' }, replace: true });
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
      .then((list) => { if (!cancelled) setSubscriptions(Array.isArray(list) ? list : []); })
      .catch((e) => {
        if (!cancelled) setError(e instanceof Error ? e.message : 'Failed to load subscriptions');
      })
      .finally(() => { if (!cancelled) setLoading(false); });
    return () => { cancelled = true; };
  }, [user]);

  const activeSubscriptions = useMemo(() => {
    return subscriptions.filter((sub) => {
      if (sub.sub_status !== 'Active') return false;
      const endDate = sub.end_date ? new Date(sub.end_date) : null;
      return endDate === null || endDate >= new Date();
    });
  }, [subscriptions]);

  if (authLoading || !user) {
    return <LoadingPage />;
  }

  return (
    <div className="page-center memberships-page">
      <div className="memberships-content">
        <div className="memberships-header">
          <h1 className="profile-name">Supporting</h1>
          <p className="memberships-subtitle">
            Creators you’re subscribed to. You have access to their tier posts
          </p>
        </div>

        {error && (
          <p className="memberships-error" role="alert">{error}</p>
        )}

        {loading ? (
          <LoadingPage message="Loading…" />
        ) : activeSubscriptions.length === 0 ? (
          <div className="card memberships-empty">
            <p className="memberships-empty-text">You aren’t supporting any creators yet</p>
            <p className="memberships-empty-hint">
              Subscribe to a creator’s tier from their profile
            </p>
            <Link to="/explore" className="btn btn-primary" style={{ marginTop: '1rem' }}>
              Explore creators
            </Link>
          </div>
        ) : (
          <ul className="memberships-list creator-link-list">
            {activeSubscriptions.map((sub) => {
              const creatorName = sub.creator?.display_name ?? sub.creator?.slug ?? 'Creator';
              const creatorSlug = sub.creator?.slug;
              const tierName = sub.tier?.tier_name ?? 'Tier';
              return (
                <li key={sub.id} className="creator-link-card">
                  <div className="membership-card-main">
                    <div className="membership-card-avatar-wrap">
                      {sub.creator?.profile_avatar_url ? (
                        <img src={sub.creator.profile_avatar_url} alt="" className="membership-card-avatar" />
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
                      <div className="creator-link-date">
                        <span className="creator-link-date-prefix">Supporting since </span>
                        <span className="creator-link-date-value">{formatDate(sub.start_date)}</span>
                      </div>
                    </div>
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
