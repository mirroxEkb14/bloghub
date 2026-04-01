import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { creatorProfilesApi, type CreatorProfile } from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { useAuth } from '../contexts/AuthContext';
import '../styles/memberships.css';

type FollowingItem = { creator_profile: CreatorProfile; followed_at: string | null };

function formatDate(iso: string | null): string {
  if (!iso) return '—';
  return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
}

export default function FollowingPage() {
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [items, setItems] = useState<FollowingItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/login', { state: { from: '/subscriptions/following' }, replace: true });
      return;
    }
  }, [user, authLoading, navigate]);

  useEffect(() => {
    if (!user) return;
    let cancelled = false;
    setLoading(true);
    setError(null);
    creatorProfilesApi
      .getFollowing()
      .then((list) => { if (!cancelled) setItems(list); })
      .catch((e) => {
        if (!cancelled) setError(e instanceof Error ? e.message : 'Failed to load following');
      })
      .finally(() => { if (!cancelled) setLoading(false); });
    return () => { cancelled = true; };
  }, [user]);

  if (authLoading || !user) {
    return <LoadingPage />;
  }

  return (
    <div className="page-center memberships-page">
      <div className="memberships-content">
        <div className="memberships-header">
          <h1 className="profile-name">Following</h1>
          <p className="memberships-subtitle">
            Creators you follow. Their public posts appear in your feeds
          </p>
        </div>

        {error && (
          <p className="memberships-error" role="alert">{error}</p>
        )}

        {loading ? (
          <LoadingPage message="Loading…" />
        ) : items.length === 0 ? (
          <div className="card memberships-empty">
            <p className="memberships-empty-text">You aren’t following anyone yet</p>
            <p className="memberships-empty-hint">
              Explore creators and follow them from their profile
            </p>
            <Link to="/explore" className="btn btn-primary" style={{ marginTop: '1rem' }}>
              Explore creators
            </Link>
          </div>
        ) : (
          <ul className="memberships-list creator-link-list">
            {items.map(({ creator_profile: profile, followed_at }) => {
              const name = profile.display_name ?? profile.slug ?? 'Creator';
              return (
                <li key={profile.id} className="creator-link-card">
                  <div className="membership-card-main">
                    <div className="membership-card-avatar-wrap">
                      {profile.profile_avatar_url ? (
                        <img src={profile.profile_avatar_url} alt="" className="membership-card-avatar" />
                      ) : (
                        <span className="membership-card-avatar-placeholder">
                          {name.charAt(0).toUpperCase()}
                        </span>
                      )}
                    </div>
                    <div className="membership-card-info">
                      <Link to={`/creator/${profile.slug}`} className="membership-card-creator">
                        {name}
                      </Link>
                      <div className="creator-link-date">
                        <span className="creator-link-date-prefix">Following since </span>
                        <span className="creator-link-date-value">{formatDate(followed_at)}</span>
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
