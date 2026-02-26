import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import {
  creatorProfilesApi,
  tagsApi,
  type CreatorProfile,
  type Tag,
} from '../api/client';

type Props = {
  mode: 'create' | 'edit';
};

function slugify(text: string): string {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export default function CreatorProfileForm({ mode }: Props) {
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [profile, setProfile] = useState<CreatorProfile | null>(null);
  const [profileLoading, setProfileLoading] = useState(mode === 'edit');
  const [tags, setTags] = useState<Tag[]>([]);
  const [form, setForm] = useState({
    display_name: '',
    slug: '',
    about: '',
    tag_ids: [] as number[],
    profile_avatar_path: undefined as string | null | undefined,
    profile_cover_path: undefined as string | null | undefined,
    avatar_preview_url: null as string | null,
    cover_preview_url: null as string | null,
  });
  const [submitting, setSubmitting] = useState(false);
  const [uploading, setUploading] = useState<'avatar' | 'cover' | null>(null);
  const [error, setError] = useState<string | null>(null);

  const ACCEPT_IMAGE = 'image/jpeg,image/png,image/webp';
  const MAX_FILE_MB = 5;
  const MAX_FILE_BYTES = MAX_FILE_MB * 1024 * 1024;

  const loadTags = useCallback(async () => {
    try {
      const list = await tagsApi.list();
      setTags(Array.isArray(list) ? list : []);
    } catch {
      setTags([]);
    }
  }, []);

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { replace: true });
      return;
    }
    loadTags();
  }, [user, authLoading, navigate, loadTags]);

  useEffect(() => {
    if (mode !== 'edit' || !user) return;
    let cancelled = false;
    (async () => {
      setProfileLoading(true);
      try {
        const data = await creatorProfilesApi.me();
        if (!cancelled) {
          setProfile(data);
          setForm((prev) => ({
            ...prev,
            display_name: data.display_name ?? '',
            slug: data.slug ?? '',
            about: data.about ?? '',
            tag_ids: data.tags?.map((t) => t.id) ?? [],
            avatar_preview_url: data.profile_avatar_url ?? null,
            cover_preview_url: data.profile_cover_url ?? null,
          }));
        }
      } catch {
        if (!cancelled) setProfile(null);
      } finally {
        if (!cancelled) setProfileLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [mode, user]);

  const update = useCallback(
    (field: keyof typeof form, value: string | number[]) => {
      setForm((prev) => {
        const next = { ...prev, [field]: value };
        if (mode === 'create' && field === 'display_name' && typeof value === 'string') {
          next.slug = slugify(value);
        }
        return next;
      });
      setError(null);
    },
    [mode]
  );

  const toggleTag = useCallback((id: number) => {
    setForm((prev) =>
      prev.tag_ids.includes(id)
        ? { ...prev, tag_ids: prev.tag_ids.filter((x) => x !== id) }
        : { ...prev, tag_ids: [...prev.tag_ids, id] }
    );
  }, []);

  const handleAvatarFile = useCallback(
    async (e: React.ChangeEvent<HTMLInputElement>) => {
      const file = e.target.files?.[0];
      e.target.value = '';
      if (!file) return;
      if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
        setError('Please choose a JPEG, PNG, or WebP image.');
        return;
      }
      if (file.size > MAX_FILE_BYTES) {
        setError(`Image must be under ${MAX_FILE_MB} MB`);
        return;
      }
      setError(null);
      setUploading('avatar');
      try {
        const { path, url } = await creatorProfilesApi.uploadAvatar(file);
        if (!path || !path.trim()) {
          setError('Upload failed: no file path returned.');
          return;
        }
        setForm((prev) => ({
          ...prev,
          profile_avatar_path: path,
          avatar_preview_url: url,
        }));
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Avatar upload failed');
      } finally {
        setUploading(null);
      }
    },
    []
  );

  const handleCoverFile = useCallback(
    async (e: React.ChangeEvent<HTMLInputElement>) => {
      const file = e.target.files?.[0];
      e.target.value = '';
      if (!file) return;
      if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
        setError('Please choose a JPEG, PNG, or WebP image');
        return;
      }
      if (file.size > MAX_FILE_BYTES) {
        setError(`Image must be under ${MAX_FILE_MB} MB`);
        return;
      }
      setError(null);
      setUploading('cover');
      try {
        const { path, url } = await creatorProfilesApi.uploadCover(file);
        if (!path || !path.trim()) {
          setError('Upload failed: no file path returned.');
          return;
        }
        setForm((prev) => ({
          ...prev,
          profile_cover_path: path,
          cover_preview_url: url,
        }));
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Cover upload failed');
      } finally {
        setUploading(null);
      }
    },
    []
  );

  const clearAvatar = useCallback(() => {
    setForm((prev) => ({
      ...prev,
      profile_avatar_path: null,
      avatar_preview_url: null,
    }));
    setError(null);
  }, []);

  const clearCover = useCallback(() => {
    setForm((prev) => ({
      ...prev,
      profile_cover_path: null,
      cover_preview_url: null,
    }));
    setError(null);
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      const buildPayload = () => {
        const p: Parameters<typeof creatorProfilesApi.create>[0] = {
          display_name: form.display_name.trim(),
          slug: form.slug.trim() || undefined,
          about: form.about.trim() || null,
          tag_ids: form.tag_ids,
        };
        if (typeof form.profile_avatar_path === 'string' && form.profile_avatar_path.trim()) {
          p.profile_avatar_path = form.profile_avatar_path;
        } else if (form.profile_avatar_path === null) {
          p.profile_avatar_path = null;
        }
        if (typeof form.profile_cover_path === 'string' && form.profile_cover_path.trim()) {
          p.profile_cover_path = form.profile_cover_path;
        } else if (form.profile_cover_path === null) {
          p.profile_cover_path = null;
        }
        return p;
      };

      if (mode === 'create') {
        const created = await creatorProfilesApi.create(buildPayload());
        navigate(`/creator/${created.slug}`, { replace: true });
      } else if (profile) {
        const updated = await creatorProfilesApi.update(profile.id, buildPayload());
        navigate(updated.slug ? `/creator/${updated.slug}` : '/discover', { replace: true });
      }
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Something went wrong');
    } finally {
      setSubmitting(false);
    }
  };

  if (authLoading || (mode === 'edit' && profileLoading)) {
    return (
      <div className="page-center">
        <p className="form-subtitle">Loading...</p>
      </div>
    );
  }

  if (!user) return null;

  if (mode === 'edit' && !profileLoading && !profile) {
    return (
      <div className="page-center">
        <div className="card" style={{ maxWidth: 420 }}>
          <h1 className="form-title">No creator profile</h1>
          <p className="form-subtitle">You don&apos;t have a creator profile yet</p>
          <Link to="/creator/new" className="btn btn-primary" style={{ display: 'inline-block', marginTop: '1rem' }}>
            Create profile
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="page-center">
      <div className="card creator-form-card">
        <h1 className="form-title">
          {mode === 'create' ? 'Create creator profile' : 'Edit creator profile'}
        </h1>
        <p className="form-subtitle">
          {mode === 'create'
            ? 'Set up your public Creator page'
            : 'Update your Display name, About, and Tags'}
        </p>

        {error && <div className="auth-error">{error}</div>}

        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="display_name">Display name</label>
            <input
              id="display_name"
              type="text"
              value={form.display_name}
              onChange={(e) => update('display_name', e.target.value)}
              placeholder="Avicii"
              required
              maxLength={50}
            />
          </div>
          <div className="form-group form-group-row">
            <div className="form-image-field">
              <label>Avatar</label>
              <div className="form-image-box form-image-box-avatar">
                {form.avatar_preview_url ? (
                  <img src={form.avatar_preview_url} alt="" className="form-image-preview" />
                ) : (
                  <span className="form-image-placeholder">No image</span>
                )}
              </div>
              <div className="form-image-actions">
                <input
                  type="file"
                  accept={ACCEPT_IMAGE}
                  onChange={handleAvatarFile}
                  className="form-image-input"
                  id="avatar-upload"
                  disabled={!!uploading}
                />
                <span className="form-image-btn-wrap">
                  <label htmlFor="avatar-upload" className="btn btn-secondary btn-sm form-image-btn">
                    {uploading === 'avatar' ? 'Uploading...' : form.avatar_preview_url ? 'Change' : 'Upload'}
                  </label>
                </span>
                {(form.avatar_preview_url || form.profile_avatar_path !== undefined) && (
                  <button type="button" className="btn btn-secondary btn-sm form-image-btn" onClick={clearAvatar}>
                    Remove
                  </button>
                )}
              </div>
              <span className="form-hint">JPEG, PNG or WebP (max {MAX_FILE_MB} MB)</span>
            </div>
            <div className="form-image-field">
              <label>Cover</label>
              <div className="form-image-box form-image-box-cover">
                {form.cover_preview_url ? (
                  <img src={form.cover_preview_url} alt="" className="form-image-preview" />
                ) : (
                  <span className="form-image-placeholder">No image</span>
                )}
              </div>
              <div className="form-image-actions">
                <input
                  type="file"
                  accept={ACCEPT_IMAGE}
                  onChange={handleCoverFile}
                  className="form-image-input"
                  id="cover-upload"
                  disabled={!!uploading}
                />
                <span className="form-image-btn-wrap">
                  <label htmlFor="cover-upload" className="btn btn-secondary btn-sm form-image-btn">
                    {uploading === 'cover' ? 'Uploading...' : form.cover_preview_url ? 'Change' : 'Upload'}
                  </label>
                </span>
                {(form.cover_preview_url || form.profile_cover_path !== undefined) && (
                  <button type="button" className="btn btn-secondary btn-sm form-image-btn" onClick={clearCover}>
                    Remove
                  </button>
                )}
              </div>
            </div>
          </div>
          <div className="form-group">
            <label htmlFor="slug">URL slug (optional)</label>
            <input
              id="slug"
              type="text"
              value={form.slug}
              onChange={(e) => update('slug', e.target.value)}
              placeholder="avicii"
              pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
              title="Lowercase letters, numbers, and hyphens only"
            />
            <span className="form-hint">
              {mode === 'create'
                ? 'Auto-generates from Display name (recommended)'
                : 'Your profile URL is /creator/[slug]. Changing it will break existing links'}
            </span>
          </div>
          <div className="form-group">
            <label htmlFor="about">About</label>
            <textarea
              id="about"
              value={form.about}
              onChange={(e) => update('about', e.target.value)}
              placeholder="A legendary Swedish DJ, remixer, and music producer"
              rows={5}
              maxLength={255}
              className="form-textarea form-textarea-about"
            />
            <span className="form-hint">Short Bio or Description (max 255 chars)</span>
          </div>
          <div className="form-group">
            <label>Tags</label>
            <div className="form-tag-chips" role="group" aria-label="Select tags">
              {tags.map((tag) => {
                const selected = form.tag_ids.includes(tag.id);
                return (
                  <button
                    key={tag.id}
                    type="button"
                    className={`tag-chip ${selected ? 'active' : ''}`}
                    onClick={() => toggleTag(tag.id)}
                    aria-pressed={selected}
                  >
                    {tag.name}
                  </button>
                );
              })}
              {tags.length === 0 && (
                <span className="form-hint">No tags available</span>
              )}
            </div>
          </div>
          <div className="form-actions">
            <button type="submit" className="btn btn-primary" disabled={submitting}>
              {submitting
                ? mode === 'create'
                  ? 'Creating...'
                  : 'Saving...'
                : mode === 'create'
                  ? 'Create profile'
                  : 'Save changes'}
            </button>
            <Link to={profile?.slug ? `/creator/${profile.slug}` : '/discover'} className="btn btn-secondary">
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
