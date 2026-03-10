import { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import {
  creatorProfilesApi,
  exploreApi,
  tagsApi,
  type CreatorProfile,
  type Post,
  type Tag,
} from '../api/client';
import LoadingPage from '../components/LoadingPage';
import PostMediaContainer from '../components/PostMediaContainer';

function useHorizontalDragScroll() {
  const ref = useRef<HTMLUListElement>(null);
  const didDragRef = useRef(false);
  const startXRef = useRef(0);
  const startScrollRef = useRef(0);

  const onMouseDown = useCallback((e: React.MouseEvent<HTMLUListElement>) => {
    if (e.button !== 0) return;
    const el = ref.current;
    if (!el) return;
    if (!el.contains(e.target as Node)) return;
    didDragRef.current = false;
    startXRef.current = e.clientX;
    startScrollRef.current = el.scrollLeft;

    el.classList.add('is-dragging');
    const pid = (e.nativeEvent as MouseEvent & { pointerId?: number }).pointerId;
    if (typeof pid === 'number' && el.setPointerCapture) {
      try {
        el.setPointerCapture(pid);
      } catch {
        // ignore
      }
    }

    const onMove = (moveEvent: MouseEvent) => {
      if ((moveEvent.buttons & 1) === 0) {
        onUp();
        return;
      }
      const dx = startXRef.current - moveEvent.clientX;
      if (Math.abs(dx) > 3) didDragRef.current = true;
      el.scrollLeft = Math.max(0, startScrollRef.current + dx);
    };
    const onUp = () => {
      el.classList.remove('is-dragging');
      if (typeof pid === 'number' && el.releasePointerCapture) {
        try {
          el.releasePointerCapture(pid);
        } catch {
          // ignore
        }
      }
      window.removeEventListener('mousemove', onMove);
      window.removeEventListener('mouseup', onUp);
    };
    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onUp);
  }, []);

  const onClickCapture = useCallback((e: React.MouseEvent) => {
    if (didDragRef.current) {
      e.preventDefault();
      e.stopPropagation();
      didDragRef.current = false;
    }
  }, []);

  return { ref, onMouseDown, onClickCapture };
}

const TAG_PARAM = 'tag';
const EXPLORE_SCROLL_KEY = 'explore-scroll';

const MEDIA_TYPE_LABELS: Record<string, string> = {
  Image: 'Image',
  Gif: 'Gif',
  Video: 'Video',
  Audio: 'Audio',
};

function LockIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
      <path d="M7 11V7a5 5 0 0 1 10 0v4" />
    </svg>
  );
}

function UnlockIcon() {
  return (
    <svg viewBox="0 0 36 36" fill="currentColor" stroke="currentColor" strokeWidth="1.2" strokeLinejoin="round" className="unlock-icon" aria-hidden>
      <path d="M26,2a8.2,8.2,0,0,0-8,8.36V15H2V32a2,2,0,0,0,2,2H22a2,2,0,0,0,2-2V15H20V10.36A6.2,6.2,0,0,1,26,4a6.2,6.2,0,0,1,6,6.36v6.83a1,1,0,0,0,2,0V10.36A8.2,8.2,0,0,0,26,2ZM7,17L20,17Q22,17,22,20L22,29Q22,32,19,32L7,32Q4,32,4,29L4,20Q4,17,7,17Z" />
    </svg>
  );
}

export default function ExplorePage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const tagFromUrl = searchParams.get(TAG_PARAM);

  const [popularCreators, setPopularCreators] = useState<CreatorProfile[]>([]);
  const [trendingPosts, setTrendingPosts] = useState<Post[]>([]);
  const [loadingPopular, setLoadingPopular] = useState(true);
  const [loadingTrending, setLoadingTrending] = useState(true);

  const [profiles, setProfiles] = useState<CreatorProfile[]>([]);
  const [tags, setTags] = useState<Tag[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [tagSlug, setTagSlug] = useState<string | null>(tagFromUrl);
  const [browsePage, setBrowsePage] = useState(1);
  const [meta, setMeta] = useState<{ current_page: number; last_page: number; total: number } | null>(null);
  const savedScrollRef = useRef<number | null>(null);
  const prevBrowsePageRef = useRef(1);
  const scrollToPaginationAfterLoadRef = useRef(false);
  const browsePaginationRef = useRef<HTMLDivElement | null>(null);

  const dragPopular = useHorizontalDragScroll();
  const dragTrending = useHorizontalDragScroll();

  useEffect(() => {
    const y = sessionStorage.getItem(EXPLORE_SCROLL_KEY);
    if (y !== null && !tagFromUrl) {
      sessionStorage.removeItem(EXPLORE_SCROLL_KEY);
      savedScrollRef.current = parseInt(y, 10);
    } else if (tagFromUrl && y !== null) {
      sessionStorage.removeItem(EXPLORE_SCROLL_KEY);
    }
  }, [tagFromUrl]);

  useEffect(() => {
    let tick: ReturnType<typeof setTimeout> | null = null;
    const saveScroll = () => {
      sessionStorage.setItem(EXPLORE_SCROLL_KEY, String(window.scrollY));
    };
    const onScroll = () => {
      if (tick !== null) clearTimeout(tick);
      tick = setTimeout(saveScroll, 100);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => {
      window.removeEventListener('scroll', onScroll);
      if (tick !== null) clearTimeout(tick);
    };
  }, []);

  useEffect(() => {
    if (!loading && savedScrollRef.current !== null) {
      const y = savedScrollRef.current;
      savedScrollRef.current = null;
      const id = setTimeout(() => {
        window.scrollTo(0, y);
      }, 50);
      return () => clearTimeout(id);
    }
  }, [loading]);

  useEffect(() => {
    setTagSlug(tagFromUrl);
  }, [tagFromUrl]);

  useEffect(() => {
    setBrowsePage(1);
  }, [search, tagSlug]);

  const browseSectionRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!tagFromUrl || loading) return;
    const el = browseSectionRef.current;
    if (!el) return;
    const timeoutId = setTimeout(() => {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
    return () => clearTimeout(timeoutId);
  }, [tagFromUrl, loading]);

  useEffect(() => {
    let cancelled = false;
    exploreApi.getPopularCreators().then((data) => {
      if (!cancelled) setPopularCreators(data ?? []);
    }).catch(() => {
      if (!cancelled) setPopularCreators([]);
    }).finally(() => {
      if (!cancelled) setLoadingPopular(false);
    });
    return () => { cancelled = true; };
  }, []);

  useEffect(() => {
    let cancelled = false;
    exploreApi.getTrendingPosts().then((data) => {
      if (!cancelled) setTrendingPosts(data ?? []);
    }).catch(() => {
      if (!cancelled) setTrendingPosts([]);
    }).finally(() => {
      if (!cancelled) setLoadingTrending(false);
    });
    return () => { cancelled = true; };
  }, []);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    (async () => {
      try {
        const [tagsRes, listRes] = await Promise.all([
          tagsApi.list(),
          creatorProfilesApi.list({
            search: search || undefined,
            tag: tagSlug ?? undefined,
            per_page: 12,
            page: browsePage,
          }),
        ]);
        if (!cancelled) {
          setTags(Array.isArray(tagsRes) ? tagsRes : []);
          setProfiles(listRes.data);
          setMeta(listRes.meta);
        }
      } catch {
        if (!cancelled) setProfiles([]);
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [search, tagSlug, browsePage]);

  useEffect(() => {
    if (browsePage > prevBrowsePageRef.current) {
      prevBrowsePageRef.current = browsePage;
      window.scrollTo(0, browseSectionRef.current?.offsetTop ?? 0);
    } else if (browsePage < prevBrowsePageRef.current) {
      prevBrowsePageRef.current = browsePage;
      scrollToPaginationAfterLoadRef.current = true;
    }
  }, [browsePage]);

  useEffect(() => {
    if (!loading && scrollToPaginationAfterLoadRef.current) {
      scrollToPaginationAfterLoadRef.current = false;
      browsePaginationRef.current?.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
  }, [loading]);

  const setTagFilter = (slug: string | null) => {
    setSearchParams((prev) => {
      const next = new URLSearchParams(prev);
      if (slug) next.set(TAG_PARAM, slug);
      else next.delete(TAG_PARAM);
      return next;
    });
  };

  const showMainContent = !loading;

  return (
    <div className="explore-page">
      <div className="explore-header">
        <h1 className="explore-title">Explore creators</h1>
        <p className="explore-subtitle">Find and follow your favorite creators</p>
      </div>

      {loadingPopular ? (
        <div className="explore-section">
          <h2 className="explore-section-title">Popular creators</h2>
          <LoadingPage message="Loading..." />
        </div>
      ) : popularCreators.length > 0 ? (
        <section className="explore-section" aria-label="Popular creators">
          <h2 className="explore-section-title">
            Popular creators <span className="explore-section-title-note">(by active subscribers)</span>
          </h2>
          <ul
            ref={dragPopular.ref}
            className="creator-grid creator-grid-row"
            onMouseDown={dragPopular.onMouseDown}
            onClickCapture={dragPopular.onClickCapture}
          >
            {popularCreators.map((profile) => (
              <li key={profile.id}>
                <Link to={`/creator/${profile.slug}`} className="creator-card">
                  <div
                    className="creator-card-cover"
                    style={
                      profile.profile_cover_url
                        ? { backgroundImage: `url(${profile.profile_cover_url})` }
                        : undefined
                    }
                  />
                  <div className="creator-card-body">
                    {profile.profile_avatar_url ? (
                      <img
                        src={profile.profile_avatar_url}
                        alt=""
                        className="creator-card-avatar"
                      />
                    ) : (
                      <div className="creator-card-avatar creator-card-avatar-placeholder">
                        {profile.display_name?.charAt(0)?.toUpperCase() ?? '?'}
                      </div>
                    )}
                    <h3 className="creator-card-name">{profile.display_name}</h3>
                    {profile.user?.username && (
                      <span className="creator-card-username">@{profile.user.username}</span>
                    )}
                    {profile.about && (
                      <p className="creator-card-about">{profile.about}</p>
                    )}
                    {profile.tags && profile.tags.length > 0 && (
                      <div className="creator-card-tags">
                        {profile.tags.slice(0, 3).map((t) => (
                          <span key={t.id} className="creator-tag">
                            {t.name}
                          </span>
                        ))}
                      </div>
                    )}
                    <span className="creator-card-count">
                      {typeof profile.subscriptions_count === 'number'
                        ? `${profile.subscriptions_count} subscriber${profile.subscriptions_count !== 1 ? 's' : ''}`
                        : typeof profile.posts_count === 'number'
                          ? `${profile.posts_count} post${profile.posts_count !== 1 ? 's' : ''}`
                          : null}
                    </span>
                  </div>
                </Link>
              </li>
            ))}
          </ul>
        </section>
      ) : null}

      {loadingTrending ? (
        <div className="explore-section">
          <h2 className="explore-section-title">Trending posts</h2>
          <LoadingPage message="Loading..." />
        </div>
      ) : trendingPosts.length > 0 ? (
        <section className="explore-section" aria-label="Trending posts">
          <h2 className="explore-section-title">
            Trending posts <span className="explore-section-title-note">(last 30 days)</span>
          </h2>
          <ul
            ref={dragTrending.ref}
            className="trending-grid"
            onMouseDown={dragTrending.onMouseDown}
            onClickCapture={dragTrending.onClickCapture}
          >
            {trendingPosts.map((post) => {
              const creatorSlug = post.creator_profile?.slug ?? '';
              const hasAccess = post.user_has_access !== false;
              const href = hasAccess && creatorSlug
                ? `/creator/${creatorSlug}/post/${post.slug}`
                : creatorSlug
                  ? `/creator/${creatorSlug}#profile-tiers`
                  : '#';
              const mediaTypeLabel = post.media_type ? MEDIA_TYPE_LABELS[post.media_type] ?? post.media_type : null;
              return (
                <li key={post.id}>
                  <Link to={href} className="trending-card">
                    {post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif') && (
                        <PostMediaContainer
                          mediaUrl={post.media_url}
                          mediaType={post.media_type}
                          figureClassName="trending-card-media-wrap"
                          videoWrapClassName="trending-card-media-wrap-video"
                          as="div"
                        >
                          {post.required_tier && (
                            <span
                              className={`trending-card-lock${hasAccess ? ' trending-card-lock-unlocked' : ''}`}
                              aria-label={hasAccess ? 'Tier post (you have access)' : 'Tier required'}
                              title={hasAccess ? 'Tier post' : 'Subscribe to a tier to access'}
                            >
                              {hasAccess ? <UnlockIcon /> : <LockIcon />}
                            </span>
                          )}
                        </PostMediaContainer>
                      )}
                      {post.media_url && post.media_type === 'Video' && (
                        <PostMediaContainer
                          mediaUrl={post.media_url}
                          mediaType="Video"
                          figureClassName="trending-card-media-wrap"
                          videoWrapClassName="trending-card-media-wrap-video"
                          videoAttrs={{ muted: true, playsInline: true, autoPlay: true, loop: true }}
                          as="div"
                        >
                          {post.required_tier && (
                            <span
                              className={`trending-card-lock${hasAccess ? ' trending-card-lock-unlocked' : ''}`}
                              aria-label={hasAccess ? 'Tier post (you have access)' : 'Tier required'}
                              title={hasAccess ? 'Tier post' : 'Subscribe to a tier to access'}
                            >
                              {hasAccess ? <UnlockIcon /> : <LockIcon />}
                            </span>
                          )}
                        </PostMediaContainer>
                      )}
                      {(post.media_type === 'Audio' || !post.media_url) && (
                        <div className={`trending-card-media-wrap${post.media_type === 'Audio' ? ' trending-card-media-audio' : ''}`}>
                          {post.media_url && post.media_type === 'Audio' && (
                            <div style={{ padding: '0.5rem', fontSize: '0.8rem', color: 'var(--text-muted)' }}>Audio</div>
                          )}
                          {!post.media_url && (
                            <div style={{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-muted)', fontSize: '0.85rem' }}>
                              {mediaTypeLabel ?? 'Post'}
                            </div>
                          )}
                          {post.required_tier && (
                            <span
                              className={`trending-card-lock${hasAccess ? ' trending-card-lock-unlocked' : ''}`}
                              aria-label={hasAccess ? 'Tier post (you have access)' : 'Tier required'}
                              title={hasAccess ? 'Tier post' : 'Subscribe to a tier to access'}
                            >
                              {hasAccess ? <UnlockIcon /> : <LockIcon />}
                            </span>
                          )}
                        </div>
                      )}
                    <div className="trending-card-body">
                      <h3 className="trending-card-title">{post.title}</h3>
                      {post.excerpt && (
                        <p className="trending-card-excerpt">{post.excerpt}</p>
                      )}
                      {post.creator_profile && (
                        <span className="trending-card-creator">{post.creator_profile.display_name}</span>
                      )}
                    </div>
                  </Link>
                </li>
              );
            })}
          </ul>
        </section>
      ) : null}

      <div className="explore-toolbar">
        <input
          type="search"
          className="explore-search"
          placeholder="Search by name or username..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label="Search creators"
        />
        <div className="explore-tags" role="group" aria-label="Filter by tag">
          <button
            type="button"
            className={`tag-chip ${tagSlug === null ? 'active' : ''}`}
            onClick={() => setTagFilter(null)}
          >
            All
          </button>
          {tags.map((tag) => (
            <button
              key={tag.id}
              type="button"
              className={`tag-chip ${tagSlug === tag.slug ? 'active' : ''}`}
              onClick={() => setTagFilter(tagSlug === tag.slug ? null : tag.slug)}
            >
              {tag.name}
            </button>
          ))}
        </div>
      </div>

      <div ref={browseSectionRef} id="browse-creators">
        {!showMainContent ? (
          <LoadingPage message="Exploring creators..." />
        ) : profiles.length === 0 ? (
          <div className="explore-empty">
            <p>No creators found. Try another search or tag</p>
          </div>
        ) : (
          <>
            <h2 className="explore-section-title">Browse creators</h2>
            {meta && (
              <p className="explore-meta">
                Showing {profiles.length} of {meta.total} creator{meta.total !== 1 ? 's' : ''}
              </p>
            )}
            <ul className="creator-grid">
            {profiles.map((profile) => (
              <li key={profile.id}>
                <Link to={`/creator/${profile.slug}`} className="creator-card">
                  <div
                    className="creator-card-cover"
                    style={
                      profile.profile_cover_url
                        ? { backgroundImage: `url(${profile.profile_cover_url})` }
                        : undefined
                    }
                  />
                  <div className="creator-card-body">
                    {profile.profile_avatar_url ? (
                      <img
                        src={profile.profile_avatar_url}
                        alt=""
                        className="creator-card-avatar"
                      />
                    ) : (
                      <div className="creator-card-avatar creator-card-avatar-placeholder">
                        {profile.display_name?.charAt(0)?.toUpperCase() ?? '?'}
                      </div>
                    )}
                    <h3 className="creator-card-name">{profile.display_name}</h3>
                    {profile.user?.username && (
                      <span className="creator-card-username">@{profile.user.username}</span>
                    )}
                    {profile.about && (
                      <p className="creator-card-about">{profile.about}</p>
                    )}
                    {profile.tags && profile.tags.length > 0 && (
                      <div className="creator-card-tags">
                        {profile.tags.slice(0, 3).map((t) => (
                          <span key={t.id} className="creator-tag">
                            {t.name}
                          </span>
                        ))}
                      </div>
                    )}
                    {typeof profile.posts_count === 'number' && (
                      <span className="creator-card-count">
                        {profile.posts_count} post{profile.posts_count !== 1 ? 's' : ''}
                      </span>
                    )}
                  </div>
                </Link>
              </li>
            ))}
          </ul>
            {meta && meta.last_page > 1 && (
              <div ref={browsePaginationRef} className="post-list-pagination" style={{ marginTop: '1rem' }}>
                <button
                  type="button"
                  className="btn btn-secondary btn-sm"
                  disabled={browsePage <= 1}
                  onClick={() => setBrowsePage((p) => Math.max(1, p - 1))}
                >
                  Previous
                </button>
                <span className="post-list-pagination-meta">
                  Page {meta.current_page} of {meta.last_page}
                </span>
                <button
                  type="button"
                  className="btn btn-secondary btn-sm"
                  disabled={browsePage >= meta.last_page}
                  onClick={() => setBrowsePage((p) => p + 1)}
                >
                  Next
                </button>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
