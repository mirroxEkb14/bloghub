import { useCallback, useEffect, useLayoutEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  creatorProfilesApi,
  feedApi,
  postsApi,
  subscriptionsApi,
  type CreatorProfile,
  type Post,
  type SubscriptionWithTier,
} from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';
import LoadingPage from '../components/LoadingPage';
import PostMediaContainer from '../components/PostMediaContainer';
import { stripHtml } from '../components/PostContent';
import { LockCircleIcon, ShareIcon } from '../components/icons';
import { formatDateTimeLocal } from '../utils/date';
import '../styles/public-feed.css';
import '../styles/profile-page.css';

const PER_PAGE = 15;
const HOME_SCROLL_KEY = 'homeFeedScroll';

function roundToHalf(n: number): number {
  return Math.round(n * 2) / 2;
}

function formatHalf(n: number): string {
  const rounded = roundToHalf(n);
  const s = Number.isInteger(rounded) ? String(rounded) : String(rounded).replace('.', ',');
  return s;
}

function formatPostAgo(iso: string | null | undefined): string {
  if (!iso) return 'NO POSTS YET';
  const d = new Date(iso);
  const t = d.getTime();
  if (Number.isNaN(t)) return 'NO POSTS YET';
  const diffMs = Date.now() - t;
  const diffMin = Math.max(0, Math.floor(diffMs / 60000));
  if (diffMin < 60) return `POST ${diffMin}M AGO`;
  const diffH = Math.floor(diffMin / 60);
  if (diffH < 24) return `POST ${diffH}H AGO`;
  const diffD = Math.floor(diffH / 24);
  if (diffD < 7) return `POST ${diffD}D AGO`;

  const weeks = diffD / 7;
  if (weeks < 4.5) return `POST ${formatHalf(weeks)}W AGO`;

  const months = diffD / 30;
  if (months < 12) return `POST ${formatHalf(months)}M AGO`;

  const years = diffD / 365;
  return `POST ${formatHalf(years)}Y AGO`;
}

function saveHomeScroll(currentPage: number) {
  try {
    sessionStorage.setItem(
      HOME_SCROLL_KEY,
      JSON.stringify({ y: window.scrollY, page: currentPage })
    );
  } catch {
    // ignore
  }
}

export default function Home() {
  const { user, loading: authLoading } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const [posts, setPosts] = useState<Post[]>([]);
  const [meta, setMeta] = useState<{ current_page: number; last_page: number; total: number } | null>(null);
  const [loading, setLoading] = useState(true);
  const [sidebarLoading, setSidebarLoading] = useState(true);
  const [subscriptions, setSubscriptions] = useState<SubscriptionWithTier[]>([]);
  const [following, setFollowing] = useState<{ creator_profile: CreatorProfile; followed_at: string | null }[]>([]);
  const [page, setPage] = useState(1);
  const prevPageRef = useRef(0);
  const prevLoadingRef = useRef(false);
  const skipRestoreRef = useRef(false);
  const paginationRef = useRef<HTMLDivElement>(null);
  const headerRef = useRef<HTMLElement>(null);
  const [sidebarTopOffsetPx, setSidebarTopOffsetPx] = useState(0);
  const [openMenuPostId, setOpenMenuPostId] = useState<number | null>(null);
  const [likingPostId, setLikingPostId] = useState<number | null>(null);

  const getPostUrl = useCallback((creatorSlug: string, postSlug: string) => {
    return `${typeof window !== 'undefined' ? window.location.origin : ''}/creator/${creatorSlug}/post/${postSlug}`;
  }, []);

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { replace: true });
      return;
    }
    if (!user) return;
    let cancelled = false;
    setLoading(true);
    feedApi
      .getHomeFeed({ page, per_page: PER_PAGE })
      .then((res) => {
        if (!cancelled) {
          setPosts(res.data);
          setMeta(res.meta);
        }
      })
      .catch(() => {
        if (!cancelled) setPosts([]);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, [user, authLoading, navigate, page]);

  useEffect(() => {
    if (!user) return;
    let cancelled = false;
    setSidebarLoading(true);
    Promise.all([
      subscriptionsApi.list().catch(() => [] as SubscriptionWithTier[]),
      creatorProfilesApi.getFollowing().catch(() => [] as { creator_profile: CreatorProfile; followed_at: string | null }[]),
    ])
      .then(([subs, fol]) => {
        if (cancelled) return;
        setSubscriptions(Array.isArray(subs) ? subs : []);
        setFollowing(Array.isArray(fol) ? fol : []);
      })
      .finally(() => {
        if (!cancelled) setSidebarLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, [user]);

  useEffect(() => {
    if (!user) return;
    let tick: ReturnType<typeof setTimeout> | null = null;
    const save = () => {
      if (tick !== null) clearTimeout(tick);
      tick = setTimeout(() => {
        if (!skipRestoreRef.current) saveHomeScroll(page);
      }, 150);
    };
    window.addEventListener('scroll', save, { passive: true });
    return () => {
      window.removeEventListener('scroll', save);
      if (tick !== null) clearTimeout(tick);
    };
  }, [user, page]);

  useLayoutEffect(() => {
    if (loading) {
      prevLoadingRef.current = true;
      return;
    }
    const wasJustLoading = prevLoadingRef.current;
    prevLoadingRef.current = false;

    if (skipRestoreRef.current) {
      if (!wasJustLoading) return;
      skipRestoreRef.current = false;
      try {
        sessionStorage.removeItem(HOME_SCROLL_KEY);
      } catch {
        // ignore
      }
      const prevPage = prevPageRef.current;
      prevPageRef.current = page;
      const scrollToBottom = page < prevPage;

      const scrollToBottomOfPage = () => {
        const docHeight = Math.max(
          document.documentElement.scrollHeight,
          document.body.scrollHeight
        );
        const maxScroll = Math.max(0, docHeight - window.innerHeight);
        window.scrollTo(0, maxScroll);
        document.documentElement.scrollTop = maxScroll;
        paginationRef.current?.scrollIntoView({ behavior: 'auto', block: 'end' });
      };
      const scrollToTop = () => {
        window.scrollTo(0, 0);
        document.documentElement.scrollTop = 0;
      };

      if (scrollToBottom) {
        const id = requestAnimationFrame(() => requestAnimationFrame(scrollToBottomOfPage));
        const t1 = setTimeout(scrollToBottomOfPage, 50);
        const t2 = setTimeout(scrollToBottomOfPage, 200);
        const t3 = setTimeout(scrollToBottomOfPage, 500);
        const t4 = setTimeout(scrollToBottomOfPage, 900);
        return () => {
          cancelAnimationFrame(id);
          clearTimeout(t1);
          clearTimeout(t2);
          clearTimeout(t3);
          clearTimeout(t4);
        };
      } else {
        scrollToTop();
        return undefined;
      }
    }

    const raw = sessionStorage.getItem(HOME_SCROLL_KEY);
    let saved: { y: number; page: number } | null = null;
    if (raw) {
      try {
        const parsed = JSON.parse(raw) as { y?: number; page?: number };
        if (typeof parsed?.y === 'number' && typeof parsed?.page === 'number') saved = { y: parsed.y, page: parsed.page };
      } catch {
        // ignore
      }
      sessionStorage.removeItem(HOME_SCROLL_KEY);
    }

    if (saved !== null && saved.page === page) {
      const y = Math.max(0, saved.y);
      const apply = () => {
        window.scrollTo(0, y);
        document.documentElement.scrollTop = y;
      };
      const id = requestAnimationFrame(() => requestAnimationFrame(apply));
      const t = setTimeout(apply, 80);
      return () => {
        cancelAnimationFrame(id);
        clearTimeout(t);
      };
    }

    const prevPage = prevPageRef.current;
    if (page === prevPage) return;
    prevPageRef.current = page;
    const scrollToBottom = page < prevPage;

    if (scrollToBottom) {
      const scrollToBottomOfPage = () => {
        const docHeight = Math.max(
          document.documentElement.scrollHeight,
          document.body.scrollHeight
        );
        const maxScroll = Math.max(0, docHeight - window.innerHeight);
        window.scrollTo(0, maxScroll);
        document.documentElement.scrollTop = maxScroll;
        paginationRef.current?.scrollIntoView({ behavior: 'auto', block: 'end' });
      };
      const id = requestAnimationFrame(() => requestAnimationFrame(scrollToBottomOfPage));
      const t1 = setTimeout(scrollToBottomOfPage, 50);
      const t2 = setTimeout(scrollToBottomOfPage, 200);
      const t3 = setTimeout(scrollToBottomOfPage, 500);
      const t4 = setTimeout(scrollToBottomOfPage, 900);
      return () => {
        cancelAnimationFrame(id);
        clearTimeout(t1);
        clearTimeout(t2);
        clearTimeout(t3);
        clearTimeout(t4);
      };
    } else {
      window.scrollTo(0, 0);
      document.documentElement.scrollTop = 0;
    }
  }, [loading, page]);

  useLayoutEffect(() => {
    const el = headerRef.current;
    if (!el) return;
    const measure = () => {
      const rect = el.getBoundingClientRect();
      const computed = window.getComputedStyle(el);
      const mb = Number.parseFloat(computed.marginBottom || '0') || 0;
      setSidebarTopOffsetPx(Math.max(0, rect.height + mb));
    };
    measure();
    window.addEventListener('resize', measure);
    return () => window.removeEventListener('resize', measure);
  }, [posts.length, meta?.total]);

  useEffect(() => {
    if (openMenuPostId === null) return;
    const close = () => setOpenMenuPostId(null);
    document.addEventListener('click', close);
    return () => document.removeEventListener('click', close);
  }, [openMenuPostId]);

  async function handleSharePost(post: Post) {
    const slug = post.creator_profile?.slug;
    if (!slug) return;
    const url = getPostUrl(slug, post.slug);
    setOpenMenuPostId(null);
    try {
      await navigator.clipboard.writeText(url);
      showToast('Link copied to clipboard', 'success');
    } catch {
      // ignore
    }
  }

  async function handleTogglePostLike(post: Post) {
    const slug = post.creator_profile?.slug;
    if (!slug || !user) return;
    const nextLiked = !post.user_has_liked;
    setLikingPostId(post.id);
    setPosts((prev) =>
      prev.map((p) =>
        p.id === post.id
          ? { ...p, likes_count: (p.likes_count ?? 0) + (nextLiked ? 1 : -1), user_has_liked: nextLiked }
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
            ? { ...p, likes_count: (p.likes_count ?? 0) + (nextLiked ? -1 : 1), user_has_liked: !nextLiked }
            : p
        )
      );
    } finally {
      setLikingPostId(null);
    }
  }

  if (authLoading || !user) {
    return <LoadingPage message="Loading..." />;
  }

  if (loading && posts.length === 0) {
    return <LoadingPage message="Loading feed..." />;
  }

  const displayName = (post: Post) => post.creator_profile?.display_name ?? 'Creator';
  const visibilityLabel = (post: Post) => (post.required_tier?.tier_name ? post.required_tier.tier_name : 'Public');
  const isLocked = (post: Post) => !!post.required_tier && post.user_has_access === false;

  const activeSubscriptions = subscriptions.filter((sub) => {
    if (sub.sub_status !== 'Active') return false;
    const endDate = sub.end_date ? new Date(sub.end_date) : null;
    return endDate === null || endDate >= new Date();
  });

  const topSubscriptionCreators = (() => {
    const bySlug = new Map<string, SubscriptionWithTier['creator']>();
    for (const sub of activeSubscriptions) {
      const c = sub.creator;
      if (!c?.slug) continue;
      const prev = bySlug.get(c.slug);
      const prevScore = prev?.followers_count ?? 0;
      const nextScore = c.followers_count ?? 0;
      if (!prev || nextScore > prevScore) bySlug.set(c.slug, c);
    }
    return Array.from(bySlug.values())
      .sort((a, b) => (b.followers_count ?? 0) - (a.followers_count ?? 0))
      .slice(0, 3);
  })();

  const topFollowingCreators = (() => {
    return following
      .map((i) => i.creator_profile)
      .filter((p) => !!p?.slug)
      .sort((a, b) => (b.followers_count ?? 0) - (a.followers_count ?? 0))
      .slice(0, 3);
  })();

  return (
    <div className="profile-page public-feed-page home-feed-page">
      <div className="public-feed-layout">
        <div className="profile-main">
          <header ref={headerRef} className="profile-header public-feed-header">
            <div className="public-feed-header-inner">
              <h1 className="profile-name">Home</h1>
              <p className="profile-meta">
                Public and Tier posts from creators you&apos;re subscribed to
              </p>
              {meta != null && meta.total > 0 && (
                <p className="public-feed-count">
                  {meta.total} post{meta.total !== 1 ? 's' : ''}
                </p>
              )}
            </div>
          </header>

          {posts.length === 0 ? (
            <div className="profile-posts-empty">
              <p>You&apos;re not subscribed to any creators yet</p>
              <p className="profile-meta" style={{ marginTop: '0.5rem' }}>
                Explore to find creators and subscribe to see their posts here
              </p>
              <Link to="/explore" className="btn btn-primary" style={{ marginTop: '1rem' }}>
                Explore creators
              </Link>
            </div>
          ) : (
            <>
              <ul className="post-card-list public-feed-list">
                {posts.map((post) => {
                  const creatorSlug = post.creator_profile?.slug ?? '';
                  const postUrl = creatorSlug ? `/creator/${creatorSlug}/post/${post.slug}` : '#';
                  const locked = isLocked(post);
                  return (
                    <li key={post.id} className="post-card-wrapper">
                      <article className={`post-card ${locked ? 'post-card-locked' : ''}`}>
                        <header className="post-card-header">
                          {post.creator_profile?.profile_avatar_url ? (
                            <img
                              src={post.creator_profile.profile_avatar_url}
                              alt=""
                              className="post-card-avatar"
                            />
                          ) : (
                            <div className="post-card-avatar post-card-avatar-placeholder">
                              {displayName(post).charAt(0).toUpperCase()}
                            </div>
                          )}
                          <div className="post-card-header-meta">
                            <Link
                              to={creatorSlug ? `/creator/${creatorSlug}` : '#'}
                              className="post-card-creator-name"
                              onClick={(e) => e.stopPropagation()}
                            >
                              {displayName(post)}
                            </Link>
                            <span className="post-card-visibility">
                              {locked ? (
                                <>
                                  <LockCircleIcon size={24} className="post-card-lock-icon" />
                                  {post.required_tier?.tier_name}
                                </>
                              ) : (
                                visibilityLabel(post)
                              )}
                              {post.created_at && (
                                <>
                                  <span className="post-card-sep"> • </span>
                                  <span className="post-card-time">{formatDateTimeLocal(post.created_at)}</span>
                                </>
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
                                    <ShareIcon size={18} />
                                  </span>
                                  Share
                                </button>
                              </div>
                            )}
                          </div>
                        </header>
                        <div className="post-card-body">
                          {locked ? (
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
                                  <LockCircleIcon className="post-card-lock-svg" />
                                </div>
                                <h3 className="post-card-unlock-title">Subscriber-only post</h3>
                                <p className="post-card-unlock-desc">
                                  Subscribe to {post.required_tier?.tier_name} to unlock this post
                                </p>
                                <Link
                                  to={creatorSlug ? `/creator/${creatorSlug}#profile-tiers` : '/explore'}
                                  className="btn btn-primary post-card-join-btn"
                                  onClick={() => saveHomeScroll(page)}
                                >
                                  View tiers
                                </Link>
                              </div>
                            </>
                          ) : (
                            <>
                              <Link
                                to={postUrl}
                                className="post-card-title-row"
                                style={{ textDecoration: 'none', color: 'inherit' }}
                                onClick={() => saveHomeScroll(page)}
                              >
                                <h3 className="post-card-title">{post.title}</h3>
                              </Link>
                              {post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif') && (
                                <Link to={postUrl} onClick={() => saveHomeScroll(page)}>
                                  <PostMediaContainer
                                    mediaUrl={post.media_url}
                                    mediaType={post.media_type}
                                    figureClassName="post-card-media"
                                    videoWrapClassName="post-card-media-video-wrap"
                                  />
                                </Link>
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
                              {post.excerpt && (
                                <p className="post-card-excerpt">{post.excerpt}</p>
                              )}
                              <footer className="post-card-footer">
                            <span className="post-card-stat" title="Views">
                              <span className="post-card-stat-icon" aria-hidden>👁</span> {post.views_count ?? 0}
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
                                  <span className="post-card-stat-icon" aria-hidden>♥</span> {post.likes_count ?? 0}
                                </>
                              )}
                            </span>
                            <span className="post-card-stat">
                              <span className="post-card-stat-icon" aria-hidden>💬</span>{' '}
                              {creatorSlug ? (
                                <Link
                                  to={`/creator/${creatorSlug}/post/${post.slug}#comments`}
                                  className="post-card-stat-link"
                                  title="Comments"
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    saveHomeScroll(page);
                                  }}
                                >
                                  {post.comments_count ?? 0}
                                </Link>
                              ) : (
                                post.comments_count ?? 0
                              )}
                            </span>
                            <span className="post-card-stat post-card-stat-bookmark" title="Bookmarks">
                              <span className="post-card-stat-icon" aria-hidden>🔖</span>{' '}
                              {post.bookmarks_count ?? 0}
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
              {meta && meta.last_page > 1 && (
                <div className="post-list-pagination" ref={paginationRef}>
                  <button
                    type="button"
                    className="btn btn-secondary btn-sm"
                    disabled={page <= 1}
                    onClick={() => {
                      skipRestoreRef.current = true;
                      try {
                        sessionStorage.removeItem(HOME_SCROLL_KEY);
                      } catch {
                        // ignore
                      }
                      setPage((p) => Math.max(1, p - 1));
                    }}
                  >
                    Previous
                  </button>
                  <span className="post-list-pagination-meta">
                    Page {meta.current_page} of {meta.last_page}
                  </span>
                  <button
                    type="button"
                    className="btn btn-secondary btn-sm"
                    disabled={page >= meta.last_page}
                    onClick={() => {
                      try {
                        sessionStorage.removeItem(HOME_SCROLL_KEY);
                      } catch {
                        // ignore
                      }
                      setPage((p) => p + 1);
                    }}
                  >
                    Next
                  </button>
                </div>
              )}
            </>
          )}
        </div>

        <aside
          className="home-feed-sidebar-wrap"
          aria-label="Subscriptions and Following"
          style={{ marginTop: sidebarTopOffsetPx }}
        >
          <div className="home-feed-sidebar">
            <section className="home-feed-sidebar-section">
              <header className="home-feed-sidebar-section-header">
                <div className="home-feed-sidebar-section-title">Subscriptions</div>
                <div className="home-feed-sidebar-section-count">{activeSubscriptions.length} Active</div>
              </header>
              <div className="home-feed-sidebar-section-body">
                {sidebarLoading ? (
                  <div className="home-feed-sidebar-muted">Loading…</div>
                ) : topSubscriptionCreators.length === 0 ? (
                  <div className="home-feed-sidebar-muted">No active subscriptions yet</div>
                ) : (
                  <ul className="home-feed-creator-list">
                    {topSubscriptionCreators.map((c) => {
                      const name = c.display_name ?? c.slug ?? 'Creator';
                      return (
                        <li key={c.slug} className="home-feed-creator-row">
                          <div className="home-feed-creator-row-inner">
                            <Link to={`/creator/${c.slug}`} className="home-feed-creator-avatar-link" aria-label={name}>
                              {c.profile_avatar_url ? (
                                <img src={c.profile_avatar_url} alt="" className="home-feed-creator-avatar" />
                              ) : (
                                <span className="home-feed-creator-avatar home-feed-creator-avatar--placeholder">
                                  {name.charAt(0).toUpperCase()}
                                </span>
                              )}
                            </Link>
                            <span className="home-feed-creator-meta">
                              <Link to={`/creator/${c.slug}`} className="home-feed-creator-name-link">
                                {name}
                              </Link>
                              <span className="home-feed-creator-last-post">{formatPostAgo(c.last_post_at)}</span>
                            </span>
                          </div>
                        </li>
                      );
                    })}
                  </ul>
                )}
              </div>
              <div className="home-feed-sidebar-section-footer">
                <Link to="/subscriptions/supporting" className="home-feed-sidebar-footer-link">
                  View all subscriptions
                </Link>
              </div>
            </section>

            <section className="home-feed-sidebar-section">
              <header className="home-feed-sidebar-section-header">
                <div className="home-feed-sidebar-section-title">Following</div>
                <div className="home-feed-sidebar-section-count">{following.length} Active</div>
              </header>
              <div className="home-feed-sidebar-section-body">
                {sidebarLoading ? (
                  <div className="home-feed-sidebar-muted">Loading…</div>
                ) : topFollowingCreators.length === 0 ? (
                  <div className="home-feed-sidebar-muted">You aren’t following anyone yet</div>
                ) : (
                  <ul className="home-feed-creator-list">
                    {topFollowingCreators.map((p) => {
                      const name = p.display_name ?? p.slug ?? 'Creator';
                      return (
                        <li key={p.slug} className="home-feed-creator-row">
                          <div className="home-feed-creator-row-inner">
                            <Link to={`/creator/${p.slug}`} className="home-feed-creator-avatar-link" aria-label={name}>
                              {p.profile_avatar_url ? (
                                <img src={p.profile_avatar_url} alt="" className="home-feed-creator-avatar" />
                              ) : (
                                <span className="home-feed-creator-avatar home-feed-creator-avatar--placeholder">
                                  {name.charAt(0).toUpperCase()}
                                </span>
                              )}
                            </Link>
                            <span className="home-feed-creator-meta">
                              <Link to={`/creator/${p.slug}`} className="home-feed-creator-name-link">
                                {name}
                              </Link>
                              <span className="home-feed-creator-last-post">{formatPostAgo(p.last_post_at)}</span>
                            </span>
                          </div>
                        </li>
                      );
                    })}
                  </ul>
                )}
              </div>
              <div className="home-feed-sidebar-section-footer">
                <Link to="/subscriptions/following" className="home-feed-sidebar-footer-link">
                  View all following
                </Link>
              </div>
            </section>
          </div>
        </aside>
      </div>
    </div>
  );
}
