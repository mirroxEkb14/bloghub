import { useEffect, useState, useCallback } from 'react';
import { Link, useParams } from 'react-router-dom';
import {
  creatorProfilesApi,
  postsApi,
  tiersApi,
  type CreatorProfile,
  type PaginatedMeta,
  type Post,
  type Tier,
} from '../api/client';

const POSTS_PAGE_SIZE = 12;

export default function CreatorProfilePage() {
  const { slug } = useParams<{ slug: string }>();
  const [profile, setProfile] = useState<CreatorProfile | null>(null);
  const [tiers, setTiers] = useState<Tier[]>([]);
  const [posts, setPosts] = useState<Post[]>([]);
  const [postsMeta, setPostsMeta] = useState<PaginatedMeta | null>(null);
  const [postsPage, setPostsPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [loadingPosts, setLoadingPosts] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [previewPost, setPreviewPost] = useState<Post | null>(null);

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

  useEffect(() => {
    if (!slug) return;
    let cancelled = false;
    (async () => {
      try {
        const list = await tiersApi.listByCreator(slug);
        if (!cancelled) setTiers(Array.isArray(list) ? list : []);
      } catch {
        if (!cancelled) setTiers([]);
      }
    })();
    return () => { cancelled = true; };
  }, [slug]);

  useEffect(() => {
    if (!slug) return;
    let cancelled = false;
    setLoadingPosts(true);
    (async () => {
      try {
        const res = await postsApi.listByCreator(slug, {
          per_page: POSTS_PAGE_SIZE,
          page: postsPage,
        });
        if (!cancelled) {
          setPosts(res.data);
          setPostsMeta(res.meta);
        }
      } catch {
        if (!cancelled) setPosts([]);
      } finally {
        if (!cancelled) setLoadingPosts(false);
      }
    })();
    return () => { cancelled = true; };
  }, [slug, postsPage]);

  const closePreview = useCallback(() => setPreviewPost(null), []);

  useEffect(() => {
    if (!previewPost) return;
    const onKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') closePreview();
    };
    window.addEventListener('keydown', onKeyDown);
    return () => window.removeEventListener('keydown', onKeyDown);
  }, [previewPost, closePreview]);

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

  const displayName = profile.display_name || profile.user?.name || profile.slug || 'Creator';

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
        <div className="profile-main">
          <div className="profile-header">
            {profile.profile_avatar_url ? (
              <img
                src={profile.profile_avatar_url}
                alt=""
                className="profile-avatar"
              />
            ) : (
              <div className="profile-avatar profile-avatar-placeholder">
                {displayName.charAt(0).toUpperCase()}
              </div>
            )}
            <div className="profile-heading">
              <h1 className="profile-name">{displayName}</h1>
              {profile.user?.username && (
                <span className="profile-username">@{profile.user.username}</span>
              )}
            </div>
          </div>
          <section className="profile-posts">
            <h2 className="profile-section-title">Posts</h2>
            {loadingPosts ? (
              <p className="profile-meta">Loading posts…</p>
            ) : posts.length === 0 ? (
              <p className="profile-meta">No posts yet.</p>
            ) : (
              <>
                <ul className="post-list">
                  {posts.map((post) => (
                    <li key={post.id}>
                      <button
                        type="button"
                        className="post-list-item"
                        onClick={() => setPreviewPost(post)}
                      >
                        <span className="post-list-title">{post.title}</span>
                        {post.required_tier && (
                          <span className="post-list-tier">{post.required_tier.tier_name}</span>
                        )}
                        {post.created_at && (
                          <span className="post-list-date">
                            {new Date(post.created_at).toLocaleDateString(undefined, { dateStyle: 'medium' })}
                          </span>
                        )}
                      </button>
                    </li>
                  ))}
                </ul>
                {postsMeta && postsMeta.last_page > 1 && (
                  <div className="post-list-pagination">
                    <button
                      type="button"
                      className="btn btn-secondary btn-sm"
                      disabled={postsPage <= 1}
                      onClick={() => setPostsPage((p) => Math.max(1, p - 1))}
                    >
                      Previous
                    </button>
                    <span className="post-list-pagination-meta">
                      Page {postsMeta.current_page} of {postsMeta.last_page}
                    </span>
                    <button
                      type="button"
                      className="btn btn-secondary btn-sm"
                      disabled={postsPage >= postsMeta.last_page}
                      onClick={() => setPostsPage((p) => p + 1)}
                    >
                      Next
                    </button>
                  </div>
                )}
              </>
            )}
          </section>
        </div>
        <aside className="profile-sidebar">
          <section className="profile-about">
            <h2 className="profile-section-title">
              About {displayName.replace(/\s+.*$/, '')}
            </h2>
            {profile.about ? (
              <p className="profile-about-text">{profile.about}</p>
            ) : (
              <p className="profile-about-text profile-about-empty">No description yet.</p>
            )}
            {profile.tags && profile.tags.length > 0 && (
              <div className="profile-tag-list">
                {profile.tags.map((t) => (
                  <span key={t.id} className="creator-tag creator-tag-pill">
                    {t.name}
                  </span>
                ))}
              </div>
            )}
          </section>
          {tiers.length > 0 && (
            <section className="profile-tiers">
              <h2 className="profile-section-title">Subscription Tiers</h2>
              <ul className="tier-list tier-list-sidebar">
                {tiers.map((tier) => (
                  <li key={tier.id} className="tier-card tier-card-stacked">
                    {tier.tier_cover_url ? (
                      <div className="tier-card-cover tier-card-cover-img">
                        <img src={tier.tier_cover_url} alt="" />
                      </div>
                    ) : (
                      <div className="tier-card-cover tier-card-cover-placeholder" />
                    )}
                    <div className="tier-card-body">
                      <h3 className="tier-card-name">{tier.tier_name}</h3>
                      {tier.tier_desc && (
                        <p className="tier-card-desc">{tier.tier_desc}</p>
                      )}
                      <p className="tier-card-price">
                        {tier.price === 0
                          ? 'Free'
                          : `${tier.tier_currency ?? ''} ${tier.price}`}
                      </p>
                      <button type="button" className="btn btn-secondary btn-sm tier-card-join" disabled>
                        Join {tier.tier_name.replace(/\s+.*$/, '')}
                      </button>
                    </div>
                  </li>
                ))}
              </ul>
            </section>
          )}
        </aside>
      </div>

      {previewPost && (
        <div
          className="post-preview-overlay"
          role="dialog"
          aria-modal="true"
          aria-labelledby="post-preview-title"
          onClick={closePreview}
        >
          <div
            className="post-preview-modal"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="post-preview-header">
              <h2 id="post-preview-title" className="post-preview-title">{previewPost.title}</h2>
              <button
                type="button"
                className="post-preview-close"
                onClick={closePreview}
                aria-label="Close preview"
              >
                ×
              </button>
            </div>
            <div className="post-preview-meta">
              {previewPost.required_tier && (
                <span className="post-tier-badge">{previewPost.required_tier.tier_name}</span>
              )}
              {previewPost.created_at && (
                <span className="post-preview-date">
                  {new Date(previewPost.created_at).toLocaleDateString(undefined, { dateStyle: 'medium' })}
                </span>
              )}
            </div>
            {previewPost.media_url && previewPost.media_type === 'Image' && (
              <figure className="post-preview-media">
                <img src={previewPost.media_url} alt="" />
              </figure>
            )}
            {previewPost.media_url && (previewPost.media_type === 'Audio' || previewPost.media_type === 'Video') && (
              <figure className="post-preview-media">
                {previewPost.media_type === 'Video' ? (
                  <video src={previewPost.media_url} controls />
                ) : (
                  <audio src={previewPost.media_url} controls />
                )}
              </figure>
            )}
            {previewPost.content_text && (
              <div className="post-preview-content">
                {previewPost.content_text.split('\n').map((line, i) => (
                  <p key={i}>{line || '\u00A0'}</p>
                ))}
              </div>
            )}
            <div className="post-preview-actions">
              <Link
                to={`/creator/${slug}/post/${previewPost.slug}`}
                className="btn btn-primary"
                onClick={closePreview}
              >
                View full post
              </Link>
              <button
                type="button"
                className="btn btn-secondary"
                onClick={closePreview}
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
