import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { creatorProfilesApi, tagsApi, type CreatorProfile, type Tag } from '../api/client';

export default function Discovery() {
  const [profiles, setProfiles] = useState<CreatorProfile[]>([]);
  const [tags, setTags] = useState<Tag[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [tagSlug, setTagSlug] = useState<string | null>(null);
  const [meta, setMeta] = useState<{ current_page: number; last_page: number; total: number } | null>(null);

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

  return (
    <div className="discovery-page">
      <div className="discovery-header">
        <h1 className="discovery-title">Discover creators</h1>
        <p className="discovery-subtitle">Find and follow your favorite creators</p>
      </div>

      <div className="discovery-toolbar">
        <input
          type="search"
          className="discovery-search"
          placeholder="Search by name or username..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label="Search creators"
        />
        <div className="discovery-tags" role="group" aria-label="Filter by tag">
          <button
            type="button"
            className={`tag-chip ${tagSlug === null ? 'active' : ''}`}
            onClick={() => setTagSlug(null)}
          >
            All
          </button>
          {tags.map((tag) => (
            <button
              key={tag.id}
              type="button"
              className={`tag-chip ${tagSlug === tag.slug ? 'active' : ''}`}
              onClick={() => setTagSlug(tagSlug === tag.slug ? null : tag.slug)}
            >
              {tag.name}
            </button>
          ))}
        </div>
      </div>

      {loading ? (
        <div className="page-center">
          <p className="form-subtitle">Loading...</p>
        </div>
      ) : profiles.length === 0 ? (
        <div className="discovery-empty">
          <p>No creators found. Try another search or tag</p>
        </div>
      ) : (
        <>
          {meta && (
            <p className="discovery-meta">
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
