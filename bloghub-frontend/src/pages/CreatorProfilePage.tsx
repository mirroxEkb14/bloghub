import { useEffect, useRef, useState, useCallback } from 'react';
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom';
import {
  ApiError,
  creatorProfilesApi,
  postsApi,
  subscriptionsApi,
  tiersApi,
  type CreatorProfile,
  type PaginatedMeta,
  type Post,
  type SubscriptionStatusResponse,
  type Tier,
} from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import LoadingPage from '../components/LoadingPage';
import { formatDateTimeLocal } from '../utils/date';

const POSTS_PAGE_SIZE = 12;

function relativeTime(dateStr: string): string {
  const d = new Date(dateStr);
  const now = new Date();
  const diffMs = now.getTime() - d.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins} min ago`;
  if (diffHours < 24) return `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays} days ago`;
  return d.toLocaleDateString(undefined, { dateStyle: 'medium' });
}

export default function CreatorProfilePage() {
  const { slug } = useParams<{ slug: string }>();
  const location = useLocation();
  const navigate = useNavigate();
  const { user } = useAuth();
  const [profile, setProfile] = useState<CreatorProfile | null>(null);
  const [tiers, setTiers] = useState<Tier[]>([]);
  const [posts, setPosts] = useState<Post[]>([]);
  const [postsMeta, setPostsMeta] = useState<PaginatedMeta | null>(null);
  const [postsPage, setPostsPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [loadingPosts, setLoadingPosts] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [previewPost, setPreviewPost] = useState<Post | null>(null);
  const [showScrollToTop, setShowScrollToTop] = useState(false);
  const [subscriptionStatus, setSubscriptionStatus] = useState<SubscriptionStatusResponse | null>(null);
  const [subscribingTierId, setSubscribingTierId] = useState<number | null>(null);
  const [subscriptionError, setSubscriptionError] = useState<{ tierId: number; message: string } | null>(null);
  const [subscriptionSuccess, setSubscriptionSuccess] = useState<string | null>(null);
  const [highlightTierId, setHighlightTierId] = useState<number | null>(null);
  const [openMenuPostId, setOpenMenuPostId] = useState<number | null>(null);
  const [shareToast, setShareToast] = useState(false);
  const [postsRefetchTrigger, setPostsRefetchTrigger] = useState(0);
  const highlightTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const prevPostsPageRef = useRef(1);
  const sidebarRef = useRef<HTMLElement | null>(null);
  const paginationRef = useRef<HTMLDivElement | null>(null);
  const savedScrollRef = useRef<number | null>(null);

  useEffect(() => {
    if (!slug) return;
    const key = `creator-scroll-${slug}`;
    const y = sessionStorage.getItem(key);
    if (y !== null) {
      sessionStorage.removeItem(key);
      savedScrollRef.current = parseInt(y, 10);
    } else {
      window.scrollTo(0, 0);
    }
  }, [slug]);

  useEffect(() => {
    if (!loading && !loadingPosts && savedScrollRef.current !== null) {
      const y = savedScrollRef.current;
      savedScrollRef.current = null;
      const id = setTimeout(() => {
        window.scrollTo(0, y);
      }, 100);
      return () => clearTimeout(id);
    }
  }, [loading, loadingPosts]);

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
    if (!slug || !user) {
      setSubscriptionStatus(null);
      return;
    }
    let cancelled = false;
    (async () => {
      try {
        const status = await subscriptionsApi.getStatusByCreator(slug);
        if (!cancelled) setSubscriptionStatus(status);
      } catch {
        if (!cancelled) setSubscriptionStatus({ subscribed: false, active_subscription: null });
      }
    })();
    return () => { cancelled = true; };
  }, [slug, user]);

  useEffect(() => {
    if (!user) setPreviewPost(null);
  }, [user]);

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const subscribeResult = params.get('subscribe');
    const sessionId = params.get('session_id');
    if (!slug || subscribeResult === null) return;
    if (subscribeResult === 'success') {
      const pathname = location.pathname;
      if (sessionId) {
        subscriptionsApi
          .confirmCheckout(sessionId)
          .then((res) => {
            if (res.status === 'active' && 'subscription' in res) {
              setSubscriptionStatus({ subscribed: true, active_subscription: res.subscription });
              setSubscriptionSuccess('Thank you for subscribing!');
              setPostsRefetchTrigger((t) => t + 1);
            } else if ('message' in res) {
              setSubscriptionError({ tierId: 0, message: res.message });
            }
          })
          .catch((e) => {
            const msg =
              e instanceof ApiError && e.status === 422 && e.body && typeof e.body === 'object' && 'message' in e.body
                ? (e.body as { message: string }).message
                : e instanceof Error
                  ? e.message
                  : 'Could not verify subscription.';
            setSubscriptionError({ tierId: 0, message: msg });
          })
          .finally(() => {
            window.history.replaceState({}, '', pathname);
          });
      } else {
        setSubscriptionSuccess('Thank you for subscribing!');
        subscriptionsApi
          .getStatusByCreator(slug)
          .then((status) => {
            setSubscriptionStatus(status);
            setPostsRefetchTrigger((t) => t + 1);
          })
          .catch(() => {});
        window.history.replaceState({}, '', pathname);
      }
      return;
    }
    if (subscribeResult === 'cancel') {
      setSubscriptionError({ tierId: 0, message: 'Checkout was canceled' });
      window.history.replaceState({}, '', `${location.pathname}`);
    }
  }, [slug, location.search]);

  useEffect(() => {
    if (highlightTierId === null) return;
    highlightTimeoutRef.current = setTimeout(() => {
      setHighlightTierId(null);
    }, 1500);
    return () => {
      if (highlightTimeoutRef.current) {
        clearTimeout(highlightTimeoutRef.current);
        highlightTimeoutRef.current = null;
      }
    };
  }, [highlightTierId]);

  useEffect(() => {
    if (openMenuPostId === null) return;
    const close = () => setOpenMenuPostId(null);
    document.addEventListener('click', close);
    return () => document.removeEventListener('click', close);
  }, [openMenuPostId]);

  const TOAST_BAR_S = 4;
  const TOAST_VISIBLE_MS = 4100;

  useEffect(() => {
    if (!shareToast) return;
    const t = setTimeout(() => setShareToast(false), TOAST_VISIBLE_MS);
    return () => clearTimeout(t);
  }, [shareToast]);

  useEffect(() => {
    if (!subscriptionSuccess || loading) return;
    const t = setTimeout(() => setSubscriptionSuccess(null), TOAST_VISIBLE_MS);
    return () => clearTimeout(t);
  }, [subscriptionSuccess, loading]);

  useEffect(() => {
    if (!subscriptionError || loading) return;
    const t = setTimeout(() => setSubscriptionError(null), TOAST_VISIBLE_MS);
    return () => clearTimeout(t);
  }, [subscriptionError, loading]);

  const getPostUrl = useCallback((creatorSlug: string, postSlug: string) => {
    return `${typeof window !== 'undefined' ? window.location.origin : ''}/creator/${creatorSlug}/post/${postSlug}`;
  }, []);

  async function handleSharePost(post: Post) {
    if (!slug) return;
    const url = getPostUrl(slug, post.slug);
    setOpenMenuPostId(null);
    try {
      await navigator.clipboard.writeText(url);
      setShareToast(true);
    } catch {
      // ignore
    }
  }

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
  }, [slug, postsPage, postsRefetchTrigger, user]);

  useEffect(() => {
    if (postsPage === prevPostsPageRef.current) return;
    const prev = prevPostsPageRef.current;
    prevPostsPageRef.current = postsPage;
    if (postsPage > prev) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      paginationRef.current?.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
  }, [postsPage]);

  useEffect(() => {
    const el = sidebarRef.current;
    if (!el) return;
    const obs = new IntersectionObserver(
      ([entry]) => setShowScrollToTop(!entry.isIntersecting),
      { threshold: 0, rootMargin: '0px' }
    );
    obs.observe(el);
    return () => obs.disconnect();
  }, [profile]);

  const scrollToTop = useCallback(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, []);

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
    return <LoadingPage message="Loading creator..." />;
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
              <p className="profile-meta">Loading posts...</p>
            ) : posts.length === 0 ? (
              <div className="profile-posts-empty" role="status" aria-live="polite">
                <div className="profile-posts-empty-icon" aria-hidden>
                  <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M12 8v32M36 8v32M8 12h32M8 36h32" />
                    <rect x="14" y="14" width="10" height="10" rx="1" />
                    <rect x="24" y="24" width="10" height="10" rx="1" />
                  </svg>
                </div>
                <h3 className="profile-posts-empty-title">No posts yet</h3>
                <p className="profile-posts-empty-desc">
                  {displayName} hasn’t published anything here. Check back later or explore their subscription tiers below
                </p>
                {tiers.length > 0 && (
                  <a
                    href="#profile-tiers"
                    className="btn btn-secondary btn-sm profile-posts-empty-cta"
                    onClick={(e) => {
                      e.preventDefault();
                      document.getElementById('profile-tiers')?.scrollIntoView({ behavior: 'smooth' });
                    }}
                  >
                    View tiers
                  </a>
                )}
              </div>
            ) : (
              <>
                <ul className="post-card-list">
                  {posts.map((post) => {
                    const isLocked = !!post.required_tier && !post.user_has_access;
                    return (
                      <li key={post.id} className="post-card-wrapper">
                        <article className={`post-card ${isLocked ? 'post-card-locked' : ''}`}>
                          <header className="post-card-header">
                            {profile.profile_avatar_url ? (
                              <img
                                src={profile.profile_avatar_url}
                                alt=""
                                className="post-card-avatar"
                              />
                            ) : (
                              <div className="post-card-avatar post-card-avatar-placeholder">
                                {displayName.charAt(0).toUpperCase()}
                              </div>
                            )}
                            <div className="post-card-header-meta">
                              <span className="post-card-creator-name">{displayName}</span>
                              <span className="post-card-visibility">
                                {isLocked ? (
                                  <>
                                    <svg className="post-card-lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
                                      <rect x="4" y="11" width="16" height="10" rx="2.5" ry="2.5" />
                                      <path d="M8 11V7.5a4 4 0 1 1 8 0V11" />
                                      <circle cx="12" cy="15.5" r="1.25" fill="currentColor" />
                                    </svg>
                                    {(() => {
                                      const maxTierLevel = tiers.length ? Math.max(...tiers.map((t) => t.level)) : 0;
                                      const tierLabel = post.required_tier!.level === maxTierLevel
                                        ? post.required_tier!.tier_name
                                        : `${post.required_tier!.tier_name} & Above`;
                                      return tierLabel;
                                    })()}
                                  </>
                                ) : (
                                  'Public'
                                )}
                                {post.created_at && (
                                  <span className="post-card-sep"> • </span>
                                )}
                                {post.created_at && (
                                  <span className="post-card-time">{formatDateTimeLocal(post.created_at)}</span>
                                )}
                              </span>
                            </div>
                            <div
                              className="post-card-actions post-card-menu-wrap"
                              onClick={(e) => e.stopPropagation()}
                            >
                              <button
                                type="button"
                                className="post-card-menu-btn"
                                aria-label="More options"
                                aria-expanded={openMenuPostId === post.id}
                                aria-haspopup="true"
                                onClick={() => setOpenMenuPostId((prev) => (prev === post.id ? null : post.id))}
                              >
                                ⋮
                              </button>
                              {openMenuPostId === post.id && (
                                <div className="post-card-dropdown" role="menu">
                                  <button
                                    type="button"
                                    role="menuitem"
                                    className="post-card-dropdown-item"
                                    onClick={() => handleSharePost(post)}
                                  >
                                    Share
                                  </button>
                                </div>
                              )}
                            </div>
                          </header>
                          <div className="post-card-body">
                            {isLocked ? (
                              <>
                                {post.content_text && (
                                  <div className="post-card-preview-blur" aria-hidden>
                                    {post.content_text}
                                  </div>
                                )}
                                <div
                                  className={`post-card-lock-overlay${post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif') ? ' post-card-lock-overlay-with-image' : ''}`}
                                >
                                  {post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif') && (
                                    <div
                                      className="post-card-lock-overlay-bg"
                                      style={{ backgroundImage: `url(${post.media_url})` }}
                                      aria-hidden
                                    />
                                  )}
                                  <div className="post-card-lock-icon-circle" aria-hidden>
                                    <svg className="post-card-lock-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
                                      <rect x="4" y="11" width="16" height="10" rx="2.5" ry="2.5" />
                                      <path d="M8 11V7.5a4 4 0 1 1 8 0V11" />
                                      <circle cx="12" cy="15.5" r="1.25" fill="currentColor" />
                                    </svg>
                                  </div>
                                  <h3 className="post-card-unlock-title">Unlock this post</h3>
                                  <p className="post-card-unlock-desc">
                                    Join the {post.required_tier?.tier_name} to get instant access to this post
                                    and other exclusive updates
                                  </p>
                                  <button
                                    type="button"
                                    className="btn btn-primary post-card-join-btn"
                                    onClick={() => document.getElementById('profile-tiers')?.scrollIntoView({ behavior: 'smooth' })}
                                  >
                                    Join Now
                                  </button>
                                </div>
                              </>
                            ) : (
                              <>
                                <div className="post-card-title-row">
                                  <h3 className="post-card-title">{post.title}</h3>
                                  <button
                                    type="button"
                                    className="btn btn-secondary btn-sm"
                                    onClick={() => setPreviewPost(post)}
                                  >
                                    Preview
                                  </button>
                                </div>
                                {post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif') && (
                                  <figure className="post-card-media">
                                    <img src={post.media_url} alt="" />
                                  </figure>
                                )}
                                {post.media_url && (post.media_type === 'Video' || post.media_type === 'Audio') && (
                                  <figure className={`post-card-media${post.media_type === 'Audio' ? ' post-card-media-audio' : ''}`}>
                                    {post.media_type === 'Video' ? (
                                      <video src={post.media_url} controls />
                                    ) : (
                                      <audio src={post.media_url} controls />
                                    )}
                                  </figure>
                                )}
                                <footer className="post-card-footer">
                                  <span className="post-card-stat" title="Unique views (full page)">
                                    <span className="post-card-stat-icon" aria-hidden>👁</span>{' '}
                                    {post.views_count ?? 0}
                                  </span>
                                  <span className="post-card-stat">
                                    <span className="post-card-stat-icon" aria-hidden>♥</span> 0
                                  </span>
                                  <span className="post-card-stat">
                                    <span className="post-card-stat-icon" aria-hidden>💬</span>{' '}
                                    {(post.comments_count ?? 0) > 0 && slug ? (
                                      <Link
                                        to={`/creator/${slug}/post/${post.slug}#comments`}
                                        className="post-card-stat-link"
                                        title="Jump to comments"
                                        onClick={() => {
                                          sessionStorage.setItem(`creator-scroll-${slug}`, String(window.scrollY));
                                        }}
                                      >
                                        {post.comments_count}
                                      </Link>
                                    ) : (
                                      post.comments_count ?? 0
                                    )}
                                  </span>
                                  <span className="post-card-stat post-card-stat-bookmark">
                                    <span className="post-card-stat-icon" aria-hidden>🔖</span> 0
                                  </span>
                                  {post.user_has_viewed && (
                                    <span className="post-card-seen" title="You've already viewed this post">
                                      Seen
                                    </span>
                                  )}
                                </footer>
                              </>
                            )}
                          </div>
                        </article>
                      </li>
                    );
                  })}
                </ul>
                {postsMeta && postsMeta.last_page > 1 && (
                  <div ref={paginationRef} className="post-list-pagination">
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
        <aside ref={sidebarRef} className="profile-sidebar">
          <section className="profile-about">
            <h2 className="profile-section-title">
              About {displayName.replace(/\s+.*$/, '')}
            </h2>
            {profile.about ? (
              <p className="profile-about-text">{profile.about}</p>
            ) : (
              <p className="profile-about-text profile-about-empty">No description yet</p>
            )}
            {profile.tags && profile.tags.length > 0 && (
              <div className="profile-tag-list">
                {profile.tags.map((t) => (
                  <Link
                    key={t.id}
                    to={`/discover?tag=${encodeURIComponent(t.slug)}`}
                    className="creator-tag creator-tag-pill creator-tag-link"
                  >
                    {t.name}
                  </Link>
                ))}
              </div>
            )}
          </section>
          {tiers.length > 0 && (
            <section id="profile-tiers" className="profile-tiers">
              <h2 className="profile-section-title">Subscription Tiers</h2>
              <ul className="tier-list tier-list-sidebar">
                {tiers.map((tier) => {
                  const isSubscribed = subscriptionStatus?.subscribed && subscriptionStatus?.active_subscription?.tier_id === tier.id;
                  const isSubscribing = subscribingTierId === tier.id;
                  const tierHighlighted = highlightTierId === tier.id;
                  const handleJoin = async () => {
                    if (!user) {
                      navigate('/login', { state: { from: location.pathname } });
                      return;
                    }
                    setSubscriptionError(null);
                    setSubscriptionSuccess(null);
                    setSubscribingTierId(tier.id);
                    try {
                      const result = await subscriptionsApi.createCheckoutSession(tier.id);
                      if (result.type === 'checkout' && result.checkout_url) {
                        window.location.href = result.checkout_url;
                        return;
                      }
                      if (result.type === 'free' && result.subscription) {
                        const status = await subscriptionsApi.getStatusByCreator(slug!);
                        setSubscriptionStatus(status);
                        setSubscriptionSuccess(`Subscribed to ${tier.tier_name}.`);
                        setPostsRefetchTrigger((t) => t + 1);
                      }
                    } catch (e) {
                      setSubscriptionError({
                        tierId: tier.id,
                        message: e instanceof Error ? e.message : 'Failed to subscribe',
                      });
                      setHighlightTierId(tier.id);
                    } finally {
                      setSubscribingTierId(null);
                    }
                  };
                  return (
                    <li
                      key={tier.id}
                      className={`tier-card tier-card-stacked${tierHighlighted ? ' tier-card-error' : ''}`}
                    >
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
                          <p className="tier-card-desc" style={{ whiteSpace: 'pre-line' }}>
                            {tier.tier_desc}
                          </p>
                        )}
                        <p className="tier-card-price">
                          {tier.price === 0
                            ? 'Free'
                            : `${tier.tier_currency ?? ''} ${tier.price}`}
                        </p>
                        {user ? (
                          isSubscribed ? (
                            <span className="btn btn-secondary btn-sm tier-card-join" style={{ opacity: 0.9, cursor: 'default' }}>
                              Subscribed
                            </span>
                          ) : (
                            <button
                              type="button"
                              className="btn btn-secondary btn-sm tier-card-join"
                              disabled={isSubscribing}
                              onClick={handleJoin}
                            >
                              {isSubscribing ? 'Joining…' : 'Join'}
                            </button>
                          )
                        ) : (
                          <button
                            type="button"
                            className="btn btn-secondary btn-sm tier-card-join"
                            onClick={() => navigate('/login', { state: { from: location.pathname } })}
                          >
                            Log in to Subscribe
                          </button>
                        )}
                      </div>
                    </li>
                  );
                })}
              </ul>
            </section>
          )}
        </aside>
      </div>

      {shareToast && (
        <div
          className="subscription-toast subscription-toast-success"
          role="status"
          aria-live="polite"
          aria-label="Link copied"
          style={{ ['--toast-duration' as string]: `${TOAST_BAR_S}s` }}
        >
          <span className="subscription-toast-icon subscription-toast-icon-success" aria-hidden>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="10" cy="10" r="9" stroke="currentColor" strokeWidth="2" />
              <path d="M6 10l3 3 5-6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </span>
          <p className="subscription-toast-msg">Link copied to clipboard</p>
          <button
            type="button"
            className="subscription-toast-close"
            onClick={() => setShareToast(false)}
            aria-label="Dismiss"
          >
            ×
          </button>
          <div className="subscription-toast-timer" aria-hidden />
        </div>
      )}

      {subscriptionSuccess && (
        <div
          className="subscription-toast subscription-toast-success"
          role="status"
          aria-live="polite"
          aria-label="Subscribed"
          style={{ ['--toast-duration' as string]: `${TOAST_BAR_S}s` }}
        >
          <span className="subscription-toast-icon subscription-toast-icon-success" aria-hidden>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="10" cy="10" r="9" stroke="currentColor" strokeWidth="2" />
              <path d="M6 10l3 3 5-6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </span>
          <p className="subscription-toast-msg">
            {subscriptionSuccess}
          </p>
          <button
            type="button"
            className="subscription-toast-close"
            onClick={() => setSubscriptionSuccess(null)}
            aria-label="Dismiss"
          >
            ×
          </button>
          <div className="subscription-toast-timer" aria-hidden />
        </div>
      )}

      {subscriptionError && (
        <div
          className="subscription-toast subscription-toast-error"
          role="status"
          aria-live="polite"
          aria-label="Subscription notice"
          style={{ ['--toast-duration' as string]: `${TOAST_BAR_S}s` }}
        >
          <span className="subscription-toast-icon subscription-toast-icon-error" aria-hidden>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10 3.5L2 16.5h16L10 3.5z" stroke="currentColor" strokeWidth="2" strokeLinejoin="round" fill="none" />
              <path d="M10 8v3M10 13v1" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            </svg>
          </span>
          <p className="subscription-toast-msg">
            {subscriptionError.message}
          </p>
          <button
            type="button"
            className="subscription-toast-close"
            onClick={() => setSubscriptionError(null)}
            aria-label="Dismiss"
          >
            ×
          </button>
          <div className="subscription-toast-timer" aria-hidden />
        </div>
      )}

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
              {previewPost.required_tier ? (
                <span className="post-tier-badge">{previewPost.required_tier.tier_name}</span>
              ) : (
                <span className="post-tier-badge">Public</span>
              )}
              {previewPost.created_at && (
                <span className="post-preview-date">
                  {formatDateTimeLocal(previewPost.created_at)}
                </span>
              )}
            </div>
            <div className="post-preview-body">
              {previewPost.media_url && (previewPost.media_type === 'Image' || previewPost.media_type === 'Gif') && (
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
              {(previewPost.excerpt ?? previewPost.content_text) && (
                <div className="post-preview-content">
                  <p style={{ whiteSpace: 'pre-line' }}>
                    {previewPost.excerpt
                      ? previewPost.excerpt
                      : previewPost.content_text!.length > 300
                        ? `${previewPost.content_text!.slice(0, 300)}...`
                        : previewPost.content_text}
                  </p>
                </div>
              )}
            </div>
            <div className="post-preview-actions">
              <Link
                to={`/creator/${slug}/post/${previewPost.slug}`}
                className="btn btn-primary"
                onClick={() => {
                  if (slug) sessionStorage.setItem(`creator-scroll-${slug}`, String(window.scrollY));
                  closePreview();
                }}
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

      {profile && showScrollToTop && (
        <button
          type="button"
          className="scroll-to-top-btn"
          onClick={scrollToTop}
          aria-label="Scroll to top"
        >
          ↑
        </button>
      )}
    </div>
  );
}
