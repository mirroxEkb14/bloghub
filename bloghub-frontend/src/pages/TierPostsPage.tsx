import { useCallback, useEffect, useRef, useState } from 'react';
import { flushSync } from 'react-dom';
import { Link, useNavigate } from 'react-router-dom';
import { feedApi, postsApi, type Post } from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';
import LoadingPage from '../components/LoadingPage';
import PostMediaContainer from '../components/PostMediaContainer';
import { stripHtml } from '../components/PostContent';
import { LockCircleIcon, ShareIcon, SearchIcon } from '../components/icons';
import { formatDateTimeLocal } from '../utils/date';
import '../styles/profile-page.css';

const PER_PAGE = 15;
const SEARCH_DEBOUNCE_MS = 300;

export default function TierPostsPage() {
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
  const scrollToPaginationAfterLoadRef = useRef(false);
  const paginationRef = useRef<HTMLDivElement | null>(null);

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
      .getTierFeed({ page, per_page: PER_PAGE, q: searchParam || undefined })
      .then((res) => {
        if (!cancelled) {
          if (scrollToPaginationAfterLoadRef.current) {
            scrollToPaginationAfterLoadRef.current = false;
            flushSync(() => {
              setPosts(res.data);
              setMeta(res.meta);
              setLoading(false);
            });
            paginationRef.current?.scrollIntoView({ behavior: 'auto', block: 'end' });
          } else {
            setPosts(res.data);
            setMeta(res.meta);
          }
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
      scrollToPaginationAfterLoadRef.current = true;
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
    return <LoadingPage message="Loading tier posts..." />;
  }

  const displayName = (post: Post) => post.creator_profile?.display_name ?? 'Creator';
  const tierLabel = (post: Post) => post.required_tier?.tier_name ?? 'Tier';
  const isLocked = (post: Post) => !!post.required_tier && post.user_has_access === false;

  return (
    <div className="profile-page public-feed-page tier-feed-page">
      <div className="public-feed-layout">
        <div className="profile-main">
          <header className="profile-header public-feed-header">
            <div className="public-feed-header-inner">
              <h1 className="profile-name">Tier posts</h1>
              <p className="profile-meta">
                Tier posts from creators you follow or subscribe to
              </p>
              <div className="public-feed-filter-row">
                <div className="public-feed-search-wrap">
                  <span className="public-feed-search-icon" aria-hidden>
                    <SearchIcon size={18} />
                  </span>
                  <input
                    type="search"
                    className="public-feed-search-input"
                  placeholder="Search by creator or post title"
                  value={searchInput}
                  onChange={(e) => setSearchInput(e.target.value)}
                  aria-label="Search by creator or post title"
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
              <p>No tier posts yet</p>
              <p className="profile-meta" style={{ marginTop: '0.5rem' }}>
                Follow or subscribe to creators to see their tier posts here
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
                                tierLabel(post)
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
                                >
                                  View tiers
                                </Link>
                              </div>
                            </>
                          ) : (
                            <>
                              <Link to={postUrl} className="post-card-title-row" style={{ textDecoration: 'none', color: 'inherit' }}>
                                <h3 className="post-card-title">{post.title}</h3>
                              </Link>
                              {post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif') && (
                                <Link to={postUrl}>
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
                            </>
                          )}
                        </div>
                      </article>
                    </li>
                  );
                })}
              </ul>
              {meta && meta.last_page > 1 && (
                <div ref={paginationRef} className="post-list-pagination">
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
      </div>
    </div>
  );
}
