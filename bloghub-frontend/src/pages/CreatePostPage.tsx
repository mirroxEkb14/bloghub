import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  postsApi,
  tiersApi,
  ValidationError,
  type PostCreatePayload,
  type Tier,
} from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';

const ACCEPT_MEDIA = 'image/jpeg,image/png,image/webp,image/gif,video/mp4,audio/mpeg,audio/mp3,audio/mp4,audio/mp4';

const MEDIA_MAX_BYTES: Record<string, number> = {
  'image/gif': 15 * 1024 * 1024,
  'image/jpeg': 5 * 1024 * 1024,
  'image/png': 5 * 1024 * 1024,
  'image/webp': 5 * 1024 * 1024,
  'video/mp4': 64 * 1024 * 1024,
  'audio/mpeg': 2 * 1024 * 1024,
  'audio/mp3': 2 * 1024 * 1024,
  'audio/mp4': 2 * 1024 * 1024,
};
const DEFAULT_MAX_BYTES = 5 * 1024 * 1024;

const MEDIA_ERROR_HINT = 'Max size: Video 64 MB, GIF 15 MB, Image 5 MB, Audio 2 MB';

function slugify(text: string): string {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export default function CreatePostPage() {
  const { user, loading: authLoading } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const [tiers, setTiers] = useState<Tier[]>([]);
  const [loading, setLoading] = useState(true);
  const [form, setForm] = useState({
    title: '',
    slug: '',
    content_text: '',
    excerpt: '',
    required_tier_id: null as number | null,
    media_url: '',
    media_type: '' as string,
  });
  const [submitting, setSubmitting] = useState(false);
  const [uploadingMedia, setUploadingMedia] = useState(false);
  const [mediaPreviewUrl, setMediaPreviewUrl] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const loadTiers = useCallback(async () => {
    try {
      const list = await tiersApi.listMine();
      setTiers(Array.isArray(list) ? list : []);
    } catch {
      setTiers([]);
    }
  }, []);

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { replace: true });
      return;
    }
    if (user && !user.creator_profile) {
      setLoading(false);
      return;
    }
    if (user?.creator_profile) {
      loadTiers().finally(() => setLoading(false));
    }
  }, [user, authLoading, navigate, loadTiers]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    setError(null);
    try {
      const payload: PostCreatePayload = {
        slug: form.slug || slugify(form.title) || 'post',
        title: form.title.trim(),
        content_text: form.content_text.trim(),
      };
      if (form.excerpt.trim()) payload.excerpt = form.excerpt.trim();
      if (form.required_tier_id != null) payload.required_tier_id = form.required_tier_id;
      if (form.media_url.trim()) {
        payload.media_url = form.media_url.trim();
        if (form.media_type) payload.media_type = form.media_type;
      }
      const post = await postsApi.create(payload);
      showToast('Post created', 'success');
      const creatorSlug = user?.creator_profile?.slug;
      if (creatorSlug && post.slug) {
        navigate(`/creator/${creatorSlug}/post/${post.slug}`, { replace: true });
      } else {
        navigate(creatorSlug ? `/creator/${creatorSlug}` : '/', { replace: true });
      }
    } catch (e) {
      const rawMessage = e instanceof Error ? e.message : 'Failed to create post';
      const isSlugTaken =
        rawMessage.toLowerCase().includes('already been taken') ||
        (e instanceof ValidationError &&
          Array.isArray(e.errors?.slug) &&
          e.errors.slug.some((m) => m.toLowerCase().includes('already been taken')));
      const message = isSlugTaken
        ? 'This slug is already taken. Please choose a different slug'
        : rawMessage;
      showToast(message, 'error');
      setError(null);
    } finally {
      setSubmitting(false);
    }
  };

  const handleMediaFile = useCallback(
    async (e: React.ChangeEvent<HTMLInputElement>) => {
      const file = e.target.files?.[0];
      e.target.value = '';
      if (!file) return;
      const maxBytes = MEDIA_MAX_BYTES[file.type] ?? DEFAULT_MAX_BYTES;
      if (file.size > maxBytes) {
        showToast(`File is too large. ${MEDIA_ERROR_HINT}`, 'error');
        return;
      }
      setUploadingMedia(true);
      setError(null);
      try {
        const res = await postsApi.uploadMedia(file);
        setForm((prev) => ({
          ...prev,
          media_url: res.path,
          media_type: res.media_type,
        }));
        setMediaPreviewUrl(res.url);
      } catch (err) {
        const msg = err instanceof Error ? err.message : 'Media upload failed';
        const isNetworkError = /fetch|network|failed to load|connection/i.test(msg);
        const friendly = isNetworkError
          ? `Upload failed. The file may be too large (${MEDIA_ERROR_HINT}). Some servers also limit uploads to 5–8 MB—try a smaller file.`
          : msg;
        showToast(friendly, 'error');
      } finally {
        setUploadingMedia(false);
      }
    },
    [showToast]
  );

  const clearMedia = useCallback(() => {
    setForm((prev) => ({ ...prev, media_url: '', media_type: '' }));
    setMediaPreviewUrl(null);
    setError(null);
  }, []);

  const updateTitle = useCallback((title: string) => {
    setForm((prev) => ({
      ...prev,
      title,
      slug: slugify(title) || prev.slug,
    }));
    setError(null);
  }, []);

  if (authLoading || (user && !user.creator_profile && loading)) {
    return <LoadingPage />;
  }

  if (!user) return null;

  if (!user.creator_profile) {
    return (
      <div className="page-center">
        <div className="card" style={{ maxWidth: 420 }}>
          <h1 className="form-title">Creator profile required</h1>
          <p className="form-subtitle">Create a creator profile first to publish posts</p>
          <Link to="/creator/new" className="btn btn-primary" style={{ display: 'inline-block', marginTop: '1rem' }}>
            Create profile
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="page-center">
      <div className="card creator-form-card" style={{ maxWidth: 560 }}>
        <h1 className="form-title">New Post</h1>
        <p className="form-subtitle">Publish a post on your creator page</p>

        {error && <div className="auth-error">{error}</div>}

        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
          <div className="form-group">
            <label htmlFor="post_title" className="form-label-required">Title</label>
            <input
              id="post_title"
              type="text"
              value={form.title}
              onChange={(e) => updateTitle(e.target.value)}
              placeholder="e.g. Behind the scenes"
              required
              maxLength={50}
            />
            <span className="form-hint">Max 50 chars</span>
          </div>

          <div className="form-group">
            <label htmlFor="post_slug" className="form-label-required">URL slug</label>
            <input
              id="post_slug"
              type="text"
              value={form.slug}
              onChange={(e) => setForm((f) => ({ ...f, slug: e.target.value }))}
              placeholder="Auto-generated from title"
              pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
              title="Lowercase letters, numbers, and hyphens only"
            />
            <span className="form-hint">Used in the post URL. Auto-generated from title</span>
          </div>

          <div className="post-create-media-access-row">
            <div className="form-group post-create-media-cell">
              <label>Media</label>
              <div className="form-image-box form-image-box-cover post-create-media-box" style={{ aspectRatio: '16/9', maxWidth: 320 }}>
                {mediaPreviewUrl ? (
                  form.media_type === 'Video' ? (
                    <video src={mediaPreviewUrl} controls className="form-image-preview" style={{ objectFit: 'contain' }} />
                  ) : form.media_type === 'Audio' ? (
                    <audio src={mediaPreviewUrl} controls className="form-image-preview" style={{ width: '100%' }} />
                  ) : (
                    <img src={mediaPreviewUrl} alt="" className="form-image-preview" style={{ objectFit: 'contain' }} />
                  )
                ) : (
                  <span className="form-image-placeholder">Image, GIF, MP4 or MP3</span>
                )}
              </div>
              <div className="form-image-actions" style={{ marginTop: '0.35rem' }}>
                <input
                  type="file"
                  accept={ACCEPT_MEDIA}
                  onChange={handleMediaFile}
                  className="form-image-input"
                  id="post-media-upload"
                  disabled={!!uploadingMedia}
                />
                <span className="form-image-btn-wrap">
                  <label htmlFor="post-media-upload" className="btn btn-secondary btn-sm form-image-btn">
                    {uploadingMedia ? 'Uploading…' : mediaPreviewUrl ? 'Change' : 'Upload'}
                  </label>
                </span>
                {(mediaPreviewUrl || form.media_url) && (
                  <button type="button" className="btn btn-secondary btn-sm form-image-btn" onClick={clearMedia}>
                    Remove
                  </button>
                )}
              </div>
              <span className="form-hint">Image, GIF, MP4 or MP3</span>
            </div>
            <div className="form-group post-create-access-cell">
              <label className="form-label-required">Access</label>
              <div className="form-tag-chips" role="group" aria-label="Post access">
                <button
                  type="button"
                  className={`tag-chip ${form.required_tier_id === null ? 'active' : ''}`}
                  onClick={() => setForm((f) => ({ ...f, required_tier_id: null }))}
                  aria-pressed={form.required_tier_id === null}
                >
                  Public
                </button>
                {tiers.map((t) => {
                  const selected = form.required_tier_id === t.id;
                  return (
                    <button
                      key={t.id}
                      type="button"
                      className={`tag-chip ${selected ? 'active' : ''}`}
                      onClick={() => setForm((f) => ({ ...f, required_tier_id: t.id }))}
                      aria-pressed={selected}
                    >
                      {t.tier_name}
                    </button>
                  );
                })}
              </div>
              <span className="form-hint">Restrict to a subscription tier or public</span>
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="post_content" className="form-label-required">Content</label>
            <textarea
              id="post_content"
              value={form.content_text}
              onChange={(e) => setForm((f) => ({ ...f, content_text: e.target.value }))}
              placeholder="Write your post..."
              rows={10}
              required
              className="form-textarea"
            />
          </div>

          <div className="form-group">
            <label htmlFor="post_excerpt">Excerpt</label>
            <textarea
              id="post_excerpt"
              value={form.excerpt}
              onChange={(e) => setForm((f) => ({ ...f, excerpt: e.target.value }))}
              placeholder="Short summary for previews"
              rows={3}
              maxLength={255}
              className="form-textarea"
            />
            <span className="form-hint">Max 255 chars</span>
          </div>

          <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', marginTop: '0.5rem' }}>
            <button type="submit" className="btn btn-primary" disabled={submitting}>
              {submitting ? 'Publishing…' : 'Publish'}
            </button>
            <Link
              to={user.creator_profile?.slug ? `/creator/${user.creator_profile.slug}` : '/'}
              className="btn btn-secondary"
            >
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
