import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { creatorProfilesApi, type CreatorProfile } from '../api/client';

export default function CreatorProfilePage() {
  const { slug } = useParams<{ slug: string }>();
  const [profile, setProfile] = useState<CreatorProfile | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!slug) return;
    let cancelled = false;
    (async () => {
      setLoading(true);
      setError(null);
      try {
        const data = await creatorProfilesApi.getBySlug(slug);
        if (!cancelled) setProfile(data);
      } catch (e) {
        if (!cancelled) setError(e instanceof Error ? e.message : 'Failed to load profile');
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [slug]);

  if (loading) {
    return (
      <div className="page-center">
        <p className="form-subtitle">Loading…</p>
      </div>
    );
  }

  if (error || !profile) {
    return (
      <div className="page-center">
        <div className="card" style={{ maxWidth: 420 }}>
          <h1 className="form-title">Creator not found</h1>
          <p className="form-subtitle">{error ?? 'This profile may have been removed.'}</p>
          <Link to="/discover" className="btn btn-primary" style={{ display: 'inline-block', marginTop: '1rem' }}>
            Discover creators
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="profile-page">
      <div
        className="profile-cover"
        style={
          profile.profile_cover_url
            ? { backgroundImage: `url(${profile.profile_cover_url})` }
            : undefined
        }
      />
      <div className="profile-container">
        <div className="profile-header">
          {profile.profile_avatar_url ? (
            <img
              src={profile.profile_avatar_url}
              alt=""
              className="profile-avatar"
            />
          ) : (
            <div className="profile-avatar profile-avatar-placeholder">
              {(profile.display_name || profile.user?.name || profile.slug || '?').charAt(0).toUpperCase()}
            </div>
          )}
          <div className="profile-heading">
            <h1 className="profile-name">
              {profile.display_name || profile.user?.name || profile.slug || 'Creator'}
            </h1>
            {profile.user?.username && (
              <span className="profile-username">@{profile.user.username}</span>
            )}
          </div>
        </div>
        {profile.about && (
          <section className="profile-about">
            <h2 className="profile-section-title">About</h2>
            <p className="profile-about-text">{profile.about}</p>
          </section>
        )}
        {profile.tags && profile.tags.length > 0 && (
          <section className="profile-tags">
            <h2 className="profile-section-title">Tags</h2>
            <div className="profile-tag-list">
              {profile.tags.map((t) => (
                <span key={t.id} className="creator-tag">
                  {t.name}
                </span>
              ))}
            </div>
          </section>
        )}
        {typeof profile.posts_count === 'number' && (
          <p className="profile-meta">
            {profile.posts_count} post{profile.posts_count !== 1 ? 's' : ''}
          </p>
        )}
      </div>
    </div>
  );
}
