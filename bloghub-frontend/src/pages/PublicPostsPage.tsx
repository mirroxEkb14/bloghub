import { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { feedApi, postsApi, type Post } from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';
import LoadingPage from '../components/LoadingPage';
import { formatDateTimeLocal } from '../utils/date';

const PER_PAGE = 15;
const SHOW_PUBLIC_FEED_ORNAMENT = false;
const SEARCH_DEBOUNCE_MS = 300;

export default function PublicPostsPage() {
  const { user, loading: authLoading } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const [posts, setPosts] = useState<Post[]>([]);
  const [meta, setMeta] = useState<{ current_page: number; last_page: number; total: number } | null>(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [openMenuPostId, setOpenMenuPostId] = useState<number | null>(null);
  const [likingPostId, setLikingPostId] = useState<number | null>(null);
  const [searchInput, setSearchInput] = useState('');
  const [searchParam, setSearchParam] = useState('');
  const prevPageRef = useRef(1);

  const getPostUrl = useCallback((creatorSlug: string, postSlug: string) => {
    return `${typeof window !== 'undefined' ? window.location.origin : ''}/creator/${creatorSlug}/post/${postSlug}`;
  }, []);

  useEffect(() => {
    if (openMenuPostId === null) return;
    const close = () => setOpenMenuPostId(null);
    document.addEventListener('click', close);
    return () => document.removeEventListener('click', close);
  }, [openMenuPostId]);

  useEffect(() => {
    const trimmed = searchInput.trim();
    const t = setTimeout(() => {
      setSearchParam(trimmed);
      setPage(1);
    }, SEARCH_DEBOUNCE_MS);
    return () => clearTimeout(t);
  }, [searchInput]);

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { replace: true });
      return;
    }
    if (!user) return;
    let cancelled = false;
    setLoading(true);
    feedApi
      .getPublicFeed({ page, per_page: PER_PAGE, q: searchParam || undefined })
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
  }, [user, authLoading, navigate, page, searchParam]);

  useEffect(() => {
    if (page > prevPageRef.current) {
      prevPageRef.current = page;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else if (page < prevPageRef.current) {
      prevPageRef.current = page;
    }
  }, [page]);

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

  if (authLoading || !user) {
    return <LoadingPage message="Loading..." />;
  }

  if (loading && posts.length === 0) {
    return <LoadingPage message="Loading public posts..." />;
  }

  const displayName = (post: Post) => post.creator_profile?.display_name ?? 'Creator';

  return (
    <div className="profile-page public-feed-page">
      <div className="public-feed-layout">
        <div className="profile-main">
          <header className="profile-header public-feed-header">
          <div className="public-feed-header-inner">
            <h1 className="profile-name">Public posts</h1>
            <p className="profile-meta">
              Public posts from creators you&apos;re subscribed to
            </p>
            <div className="public-feed-filter-row">
              <div className="public-feed-search-wrap">
                <span className="public-feed-search-icon" aria-hidden>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                  </svg>
                </span>
                <input
                  type="search"
                  className="public-feed-search-input"
                  placeholder="Search by creator or post title"
                  value={searchInput}
                  onChange={(e) => setSearchInput(e.target.value)}
                  aria-label="Search by creator name or post title"
                />
              </div>
              {meta != null && (
                <span className="public-feed-count-inline">
                  {meta.total} post{meta.total !== 1 ? 's' : ''}
                </span>
              )}
            </div>
          </div>
        </header>

        {posts.length === 0 ? (
          <div className="profile-posts-empty">
            <p>No public posts yet</p>
            <p className="profile-meta" style={{ marginTop: '0.5rem' }}>
              Subscribe to creators to see their public posts here
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
                return (
                  <li key={post.id} className="post-card-wrapper">
                    <article className="post-card">
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
                            Public
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
                        <Link to={postUrl} className="post-card-title-row" style={{ textDecoration: 'none', color: 'inherit' }}>
                          <h3 className="post-card-title">{post.title}</h3>
                        </Link>
                        {post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif') && (
                          <Link to={postUrl}>
                            <figure className="post-card-media">
                              <img src={post.media_url} alt="" />
                            </figure>
                          </Link>
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
                                onClick={(e) => e.stopPropagation()}
                              >
                                {post.comments_count ?? 0}
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
                      </div>
                    </article>
                  </li>
                );
              })}
            </ul>
            {meta && meta.last_page > 1 && (
              <div className="post-list-pagination">
                <button
                  type="button"
                  className="btn btn-secondary btn-sm"
                  disabled={page <= 1}
                  onClick={() => setPage((p) => Math.max(1, p - 1))}
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
                  onClick={() => setPage((p) => p + 1)}
                >
                  Next
                </button>
              </div>
            )}
          </>
        )}
        </div>
        {SHOW_PUBLIC_FEED_ORNAMENT && (
          <aside className="public-feed-ornament-wrap" aria-hidden>
            <img
              src="/images/public-feed-ornament.png"
              alt=""
              className="public-feed-ornament"
              width={600}
              height={1080}
            />
          </aside>
        )}
      </div>
    </div>
  );
}
