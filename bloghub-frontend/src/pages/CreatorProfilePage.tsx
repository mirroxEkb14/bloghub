import { useCallback, useEffect, useRef, useState } from 'react';
import { flushSync } from 'react-dom';
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
import PostMediaContainer from '../components/PostMediaContainer';
import PostContent, { stripHtml } from '../components/PostContent';
import { formatDateTimeLocal } from '../utils/date';

const POSTS_PAGE_SIZE = 12;

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
  const [likingPostId, setLikingPostId] = useState<number | null>(null);
  const [followLoading, setFollowLoading] = useState(false);
  const highlightTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const prevPostsPageRef = useRef(1);
  const scrollToPaginationAfterLoadRef = useRef(false);
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

  async function handleTogglePostLike(post: Post) {
    if (!slug || !user) return;
    const nextLiked = !post.user_has_liked;
    setLikingPostId(post.id);
    setPosts((prev) =>
      prev.map((p) =>
        p.id === post.id
          ? {
              ...p,
              likes_count: (p.likes_count ?? 0) + (nextLiked ? 1 : -1),
              user_has_liked: nextLiked,
            }
          : p
      )
    );
    try {
      if (nextLiked) {
        await postsApi.like(slug, post.slug);
      } else {
        await postsApi.unlike(slug, post.slug);
      }
    } catch {
      setPosts((prev) =>
        prev.map((p) =>
          p.id === post.id
            ? {
                ...p,
                likes_count: (p.likes_count ?? 0) + (nextLiked ? -1 : 1),
                user_has_liked: !nextLiked,
              }
            : p
        )
      );
    } finally {
      setLikingPostId(null);
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
          if (scrollToPaginationAfterLoadRef.current) {
            scrollToPaginationAfterLoadRef.current = false;
            flushSync(() => {
              setPosts(res.data);
              setPostsMeta(res.meta);
              setLoadingPosts(false);
            });
            paginationRef.current?.scrollIntoView({ behavior: 'auto', block: 'end' });
          } else {
            setPosts(res.data);
            setPostsMeta(res.meta);
          }
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
      scrollToPaginationAfterLoadRef.current = true;
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

  const canFollow = Boolean(user && profile && user.creator_profile?.slug !== profile.slug);
  const handleFollow = useCallback(async () => {
    if (!slug || !canFollow || !profile) return;
    setFollowLoading(true);
    try {
      if (profile.is_following) {
        await creatorProfilesApi.unfollow(slug);
        setProfile((p) => (p ? { ...p, is_following: false, followers_count: Math.max(0, (p.followers_count ?? 0) - 1) } : p));
      } else {
        await creatorProfilesApi.follow(slug);
        setProfile((p) => (p ? { ...p, is_following: true, followers_count: (p.followers_count ?? 0) + 1 } : p));
      }
    } catch {
      // state unchanged
    } finally {
      setFollowLoading(false);
    }
  }, [slug, canFollow, profile?.is_following]);

  if (loading) {
    return <LoadingPage message="Loading creator..." />;
  }

  if (error || !profile) {
    return (
      <div className="page-center">
        <div className="card" style={{ maxWidth: 420 }}>
          <h1 className="form-title">Creator not found</h1>
          <p className="form-subtitle">{error ?? 'This profile may have been removed.'}</p>
          <Link to="/explore" className="btn btn-primary" style={{ display: 'inline-block', marginTop: '1rem' }}>
            Explore creators
          </Link>
        </div>
      </div>
    );
  }

  const displayName = profile.display_name || profile.user?.name || profile.slug || 'Creator';
  const isOwnProfile = user?.creator_profile?.slug === profile.slug;

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
              <div className="profile-stats-row">
                <div className="profile-stats">
                  {canFollow ? (
                    <button
                      type="button"
                      className="profile-stat profile-stat-follow"
                      onClick={handleFollow}
                      disabled={followLoading}
                      aria-busy={followLoading}
                      title={profile.is_following ? 'Unfollow' : 'Follow'}
                      aria-label={profile.is_following ? 'Unfollow this creator' : 'Follow this creator'}
                    >
                      <span className="profile-stat-icon" aria-hidden>
                        {profile.is_following ? (
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <circle cx="12" cy="7" r="4" />
                            <path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2" />
                            <path d="M16 19l2 2 4-5" className="profile-stat-icon-check" />
                          </svg>
                        ) : (
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                          </svg>
                        )}
                      </span>
                      <span className="profile-stat-value">{profile.followers_count ?? 0}</span>
                      <span className="profile-stat-label">Followers</span>
                    </button>
                  ) : (
                    <span className="profile-stat" title="Followers">
                      <span className="profile-stat-icon" aria-hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                          <circle cx="9" cy="7" r="4" />
                          <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                      </span>
                      <span className="profile-stat-value">{profile.followers_count ?? 0}</span>
                      <span className="profile-stat-label">Followers</span>
                    </span>
                  )}
                  <span className="profile-stat" title="Subscribers">
                    <span className="profile-stat-icon" aria-hidden>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                        <path d="M12 3v4" />
                        <path d="M12 15v4" />
                        <path d="M8 11h8" />
                      </svg>
                    </span>
                    <span className="profile-stat-value">{profile.subscribers_count ?? 0}</span>
                    <span className="profile-stat-label">Subscribers</span>
                  </span>
                  <span className="profile-stat" title="Posts">
                    <span className="profile-stat-icon" aria-hidden>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                      </svg>
                    </span>
                    <span className="profile-stat-value">{profile.posts_count ?? 0}</span>
                    <span className="profile-stat-label">Posts</span>
                  </span>
                </div>
                {(profile.telegram_url || profile.instagram_url || profile.facebook_url || profile.youtube_url || profile.twitch_url || profile.website_url) && (
                  <div className="profile-social-links">
                  {profile.telegram_url && (
                    <a href={profile.telegram_url} target="_blank" rel="noopener noreferrer" className="profile-social-link" title="Telegram" aria-label="Telegram">
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    </a>
                  )}
                  {profile.instagram_url && (
                    <a href={profile.instagram_url} target="_blank" rel="noopener noreferrer" className="profile-social-link" title="Instagram" aria-label="Instagram">
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                  )}
                  {profile.facebook_url && (
                    <a href={profile.facebook_url} target="_blank" rel="noopener noreferrer" className="profile-social-link" title="Facebook" aria-label="Facebook">
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                  )}
                  {profile.youtube_url && (
                    <a href={profile.youtube_url} target="_blank" rel="noopener noreferrer" className="profile-social-link" title="YouTube" aria-label="YouTube">
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                  )}
                  {profile.twitch_url && (
                    <a href={profile.twitch_url} target="_blank" rel="noopener noreferrer" className="profile-social-link" title="Twitch" aria-label="Twitch">
                      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/></svg>
                    </a>
                  )}
                  {profile.website_url && (
                    <a href={profile.website_url} target="_blank" rel="noopener noreferrer" className="profile-social-link" title="Website" aria-label="Website">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </a>
                  )}
                  </div>
                )}
              </div>
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
                                    <span className="post-card-dropdown-item-icon" aria-hidden>
                                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                        <circle cx="6" cy="12" r="3" />
                                        <circle cx="18" cy="5" r="3" />
                                        <circle cx="18" cy="19" r="3" />
                                        <line x1="6" y1="12" x2="18" y2="5" />
                                        <line x1="6" y1="12" x2="18" y2="19" />
                                      </svg>
                                    </span>
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
                                    {stripHtml(post.content_text)}
                                  </div>
                                )}
                                <div
                                  className={`post-card-lock-overlay${post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif' || post.media_type === 'Video') ? ' post-card-lock-overlay-with-image' : ''}`}
                                >
                                  {post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif') && (
                                    <div
                                      className="post-card-lock-overlay-bg"
                                      style={{ backgroundImage: `url(${post.media_url})` }}
                                      aria-hidden
                                    />
                                  )}
                                  {post.media_url && post.media_type === 'Video' && (
                                    <div className="post-card-lock-overlay-bg post-card-lock-overlay-video" aria-hidden>
                                      <video src={post.media_url} muted loop playsInline autoPlay />
                                    </div>
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
                                  <PostMediaContainer
                                    mediaUrl={post.media_url}
                                    mediaType={post.media_type}
                                    figureClassName="post-card-media"
                                    videoWrapClassName="post-card-media-video-wrap"
                                  />
                                )}
                                {post.media_url && post.media_type === 'Video' && (
                                  <PostMediaContainer
                                    mediaUrl={post.media_url}
                                    mediaType="Video"
                                    figureClassName="post-card-media"
                                    videoWrapClassName="post-card-media-video-wrap"
                                    videoAttrs={{ controls: true }}
                                  />
                                )}
                                {post.media_url && post.media_type === 'Audio' && (
                                  <figure className="post-card-media post-card-media-audio">
                                    <audio src={post.media_url} controls />
                                  </figure>
                                )}
                                <footer className="post-card-footer">
                                  <span className="post-card-stat" title="Unique views">
                                    <span className="post-card-stat-icon" aria-hidden>👁</span>{' '}
                                    {post.views_count ?? 0}
                                  </span>
                                  <span className="post-card-stat" title="Likes">
                                    {user ? (
                                      <button
                                        type="button"
                                        className="post-card-stat-btn post-card-stat-like"
                                        aria-pressed={post.user_has_liked ?? false}
                                        aria-label={post.user_has_liked ? 'Unlike' : 'Like'}
                                        disabled={likingPostId === post.id}
                                        onClick={(e) => {
                                          e.preventDefault();
                                          e.stopPropagation();
                                          handleTogglePostLike(post);
                                        }}
                                      >
                                        <span className="post-card-stat-icon" aria-hidden>
                                          {post.user_has_liked ? '♥' : '♡'}
                                        </span>{' '}
                                        {post.likes_count ?? 0}
                                      </button>
                                    ) : (
                                      <>
                                        <span className="post-card-stat-icon" aria-hidden>♥</span>{' '}
                                        {post.likes_count ?? 0}
                                      </>
                                    )}
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
                    to={`/explore?tag=${encodeURIComponent(t.slug)}`}
                    className="creator-tag creator-tag-pill creator-tag-link"
                  >
                    {t.name}
                  </Link>
                ))}
              </div>
            )}
          </section>
          <section id="profile-tiers" className="profile-tiers">
            <h2 className="profile-section-title">Subscription Tiers</h2>
            {tiers.length === 0 ? (
              <div className="profile-posts-empty" role="status" aria-live="polite">
                <div className="profile-posts-empty-icon" aria-hidden>
                  <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
                    <rect x="8" y="8" width="32" height="8" rx="1" />
                    <rect x="8" y="20" width="32" height="8" rx="1" />
                    <rect x="8" y="32" width="32" height="8" rx="1" />
                  </svg>
                </div>
                <h3 className="profile-posts-empty-title">No subscription tiers yet</h3>
                <p className="profile-posts-empty-desc">
                  {displayName} hasn’t set up any subscription tiers yet. Check back later
                </p>
              </div>
            ) : (
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
                      if (result.type === 'already_subscribed') {
                        setSubscriptionSuccess(result.message ?? 'You already have access to all tiers');
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
                        <div className="tier-card-price-row tier-card-price-row-public">
                          <p className="tier-card-price">
                            {tier.price === 0
                              ? 'Free'
                              : `${tier.tier_currency ?? ''} ${tier.price}`}
                          </p>
                          <span className="tier-card-level-badge" aria-label={`Level ${tier.level}`}>
                            Level {tier.level}
                          </span>
                        </div>
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
            )}
          </section>
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
                <PostMediaContainer
                  mediaUrl={previewPost.media_url}
                  mediaType={previewPost.media_type}
                  figureClassName="post-preview-media"
                  videoWrapClassName="post-preview-media-video-wrap"
                />
              )}
              {previewPost.media_url && previewPost.media_type === 'Video' && (
                <PostMediaContainer
                  mediaUrl={previewPost.media_url}
                  mediaType="Video"
                  figureClassName="post-preview-media"
                  videoWrapClassName="post-preview-media-video-wrap"
                  videoAttrs={{ controls: true }}
                />
              )}
              {previewPost.media_url && previewPost.media_type === 'Audio' && (
                <figure className="post-preview-media">
                  <audio src={previewPost.media_url} controls />
                </figure>
              )}
              {(previewPost.excerpt ?? previewPost.content_text) && (
                <div className="post-preview-content">
                  {previewPost.excerpt ? (
                    <p style={{ whiteSpace: 'pre-line' }}>{previewPost.excerpt}</p>
                  ) : (
                    <PostContent html={previewPost.content_text!} />
                  )}
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
