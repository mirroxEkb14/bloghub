import { useEffect, useRef, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { creatorProfilesApi, tagsApi, type CreatorProfile, type Tag } from '../api/client';
import LoadingPage from '../components/LoadingPage';

const TAG_PARAM = 'tag';
const EXPLORE_SCROLL_KEY = 'explore-scroll';

export default function ExplorePage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const tagFromUrl = searchParams.get(TAG_PARAM);

  const [profiles, setProfiles] = useState<CreatorProfile[]>([]);
  const [tags, setTags] = useState<Tag[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [tagSlug, setTagSlug] = useState<string | null>(tagFromUrl);
  const [meta, setMeta] = useState<{ current_page: number; last_page: number; total: number } | null>(null);
  const savedScrollRef = useRef<number | null>(null);

  useEffect(() => {
    const y = sessionStorage.getItem(EXPLORE_SCROLL_KEY);
    if (y !== null) {
      sessionStorage.removeItem(EXPLORE_SCROLL_KEY);
      savedScrollRef.current = parseInt(y, 10);
    }
  }, []);

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
    let cancelled = false;
    (async () => {
      try {
        const [tagsRes, listRes] = await Promise.all([
          tagsApi.list(),
          creatorProfilesApi.list({
            search: search || undefined,
            tag: tagSlug ?? undefined,
            per_page: 12,
            page: 1,
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
  }, [search, tagSlug]);

  const setTagFilter = (slug: string | null) => {
    setSearchParams((prev) => {
      const next = new URLSearchParams(prev);
      if (slug) next.set(TAG_PARAM, slug);
      else next.delete(TAG_PARAM);
      return next;
    });
  };

  return (
    <div className="explore-page">
      <div className="explore-header">
        <h1 className="explore-title">Explore creators</h1>
        <p className="explore-subtitle">Find and follow your favorite creators</p>
      </div>

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

      {loading ? (
        <LoadingPage message="Exploring creators..." />
      ) : profiles.length === 0 ? (
        <div className="explore-empty">
          <p>No creators found. Try another search or tag</p>
        </div>
      ) : (
        <>
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
                    <h2 className="creator-card-name">{profile.display_name}</h2>
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
        </>
      )}
    </div>
  );
}
