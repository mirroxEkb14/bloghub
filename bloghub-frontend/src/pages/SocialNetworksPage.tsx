import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { creatorProfilesApi, type CreatorProfile } from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';

const FIELDS = [
  { key: 'telegram_url' as const, label: 'Telegram', placeholder: 'https://t.me/username' },
  { key: 'instagram_url' as const, label: 'Instagram', placeholder: 'https://instagram.com/username' },
  { key: 'facebook_url' as const, label: 'Facebook', placeholder: 'https://facebook.com/username' },
  { key: 'youtube_url' as const, label: 'YouTube', placeholder: 'https://youtube.com/@channel' },
  { key: 'twitch_url' as const, label: 'Twitch', placeholder: 'https://twitch.tv/username' },
  { key: 'website_url' as const, label: 'Website', placeholder: 'https://example.com' },
] as const;

type SocialKey = typeof FIELDS[number]['key'];

const URL_PREFIXES: Record<SocialKey, RegExp> = {
  telegram_url: /^https:\/\/t\.me\/.+/,
  instagram_url: /^https:\/\/(www\.)?instagram\.com\/.+/,
  facebook_url: /^https:\/\/(www\.)?facebook\.com\/.+/,
  youtube_url: /^https:\/\/(www\.)?youtube\.com\/.+/,
  twitch_url: /^https:\/\/(www\.)?twitch\.tv\/.+/,
  website_url: /^https:\/\/.+/,
};

function validateSocialUrl(key: SocialKey, value: string): string | null {
  const trimmed = value.trim();
  if (!trimmed) return null;
  if (!trimmed.startsWith('http://') && !trimmed.startsWith('https://')) {
    return `${FIELDS.find((f) => f.key === key)!.label} must be a valid URL starting with https://`;
  }
  if (!URL_PREFIXES[key].test(trimmed)) {
    switch (key) {
      case 'telegram_url':
        return 'Telegram link must start with https://t.me/';
      case 'instagram_url':
        return 'Instagram link must start with https://instagram.com/ or https://www.instagram.com/';
      case 'facebook_url':
        return 'Facebook link must start with https://facebook.com/ or https://www.facebook.com/';
      case 'youtube_url':
        return 'YouTube link must start with https://youtube.com/ or https://www.youtube.com/';
      case 'twitch_url':
        return 'Twitch link must start with https://twitch.tv/ or https://www.twitch.tv/';
      case 'website_url':
        return 'Website link must start with https://';
      default:
        return 'Invalid URL format';
    }
  }
  return null;
}

export default function SocialNetworksPage() {
  const { user, loading: authLoading } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const [profile, setProfile] = useState<CreatorProfile | null>(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [form, setForm] = useState<Record<SocialKey, string>>({
    telegram_url: '',
    instagram_url: '',
    facebook_url: '',
    youtube_url: '',
    twitch_url: '',
    website_url: '',
  });

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { replace: true });
      return;
    }
  }, [user, authLoading, navigate]);

  useEffect(() => {
    if (!user) return;
    let cancelled = false;
    setLoading(true);
    creatorProfilesApi
      .me()
      .then((data) => {
        if (!cancelled) {
          setProfile(data);
          setForm({
            telegram_url: data.telegram_url ?? '',
            instagram_url: data.instagram_url ?? '',
            facebook_url: data.facebook_url ?? '',
            youtube_url: data.youtube_url ?? '',
            twitch_url: data.twitch_url ?? '',
            website_url: data.website_url ?? '',
          });
        }
      })
      .catch(() => {
        if (!cancelled) setProfile(null);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => { cancelled = true; };
  }, [user]);

  const update = useCallback((key: SocialKey, value: string) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  }, []);

  const handleSubmit = useCallback(
    async (e: React.FormEvent) => {
      e.preventDefault();
      if (!profile || submitting) return;
      for (const { key } of FIELDS) {
        const err = validateSocialUrl(key, form[key]);
        if (err) {
          showToast(err, 'error');
          return;
        }
      }
      setSubmitting(true);
      try {
        const payload: Partial<Record<SocialKey, string | null>> = {};
        FIELDS.forEach(({ key }) => {
          const v = form[key].trim();
          payload[key] = v === '' ? null : v;
        });
        const updated = await creatorProfilesApi.update(profile.id, payload);
        setProfile(updated);
        setForm({
          telegram_url: updated.telegram_url ?? '',
          instagram_url: updated.instagram_url ?? '',
          facebook_url: updated.facebook_url ?? '',
          youtube_url: updated.youtube_url ?? '',
          twitch_url: updated.twitch_url ?? '',
          website_url: updated.website_url ?? '',
        });
        showToast('Social links saved', 'success');
      } catch (err) {
        showToast(err instanceof Error ? err.message : 'Failed to save', 'error');
      } finally {
        setSubmitting(false);
      }
    },
    [profile, form, submitting, showToast]
  );

  if (authLoading || !user) {
    return <LoadingPage />;
  }

  if (!loading && !profile) {
    return (
      <div className="page-center">
        <div className="card" style={{ maxWidth: 420 }}>
          <h1 className="form-title">Social networks</h1>
          <p className="form-subtitle">
            You need a creator page to add social links. They will appear on your public creator profile
          </p>
          <Link to="/creator/new" className="btn btn-primary" style={{ marginTop: '1rem' }}>
            Create creator page
          </Link>
        </div>
      </div>
    );
  }

  if (loading) {
    return <LoadingPage message="Loading…" />;
  }

  return (
    <div className="page-center">
      <div className="card" style={{ maxWidth: 520 }}>
        <h1 className="form-title">Social networks</h1>
        <p className="form-subtitle">
          Add links to your social profiles. They will appear on your public Creator page
        </p>
        <form onSubmit={handleSubmit}>
          {FIELDS.map(({ key, label, placeholder }) => (
            <div key={key} className="form-group">
              <label htmlFor={key}>{label}</label>
              <input
                id={key}
                type="url"
                value={form[key]}
                onChange={(e) => update(key, e.target.value)}
                placeholder={placeholder}
                autoComplete="off"
              />
            </div>
          ))}
          <div className="form-actions">
            <button type="submit" className="btn btn-primary" disabled={submitting}>
              {submitting ? 'Saving…' : 'Save'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
