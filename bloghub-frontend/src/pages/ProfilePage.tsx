import { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ValidationError } from '../api/client';
import { authApi } from '../api/client';
import InputWithIcon from '../components/InputWithIcon';
import LoadingPage from '../components/LoadingPage';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';

type FormKey = 'name' | 'username' | 'email' | 'phone';

export default function ProfilePage() {
  const { user, loading: authLoading, refreshUser } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const [form, setForm] = useState({
    name: '',
    username: '',
    email: '',
    phone: '',
  });
  const [avatarPreviewUrl, setAvatarPreviewUrl] = useState<string | null>(null);
  const [pendingAvatarFile, setPendingAvatarFile] = useState<File | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [avatarError, setAvatarError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Partial<Record<FormKey, string>>>({});

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { replace: true });
      return;
    }
    if (user) {
      const phoneRaw = user.phone ?? '';
      const phoneDisplay = phoneRaw.startsWith('+') ? phoneRaw.slice(1) : phoneRaw;
      setForm({
        name: user.name ?? '',
        username: user.username ?? '',
        email: user.email ?? '',
        phone: phoneDisplay,
      });
      setAvatarPreviewUrl(user.avatar_url ?? null);
      setPendingAvatarFile(null);
    }
  }, [user, authLoading, navigate]);

  useEffect(() => {
    return () => {
      if (avatarPreviewUrl?.startsWith('blob:')) {
        URL.revokeObjectURL(avatarPreviewUrl);
      }
    };
  }, [avatarPreviewUrl]);

  const update = useCallback((field: FormKey, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
    setFieldErrors((prev) => {
      const next = { ...prev };
      delete next[field];
      return next;
    });
  }, []);

  const handleAvatarChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file) return;
    if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
      setAvatarError('Please choose a JPEG, PNG or WebP image');
      return;
    }
    const maxBytes = 5 * 1024 * 1024;
    if (file.size > maxBytes) {
      showToast('Image must be under 5 MB', 'error');
      return;
    }
    setAvatarError(null);
    if (avatarPreviewUrl?.startsWith('blob:')) {
      URL.revokeObjectURL(avatarPreviewUrl);
    }
    setAvatarPreviewUrl(URL.createObjectURL(file));
    setPendingAvatarFile(file);
  }, [avatarPreviewUrl, showToast]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setFieldErrors({});
    setAvatarError(null);

    const userPhoneDisplay = user?.phone != null
      ? (user.phone.startsWith('+') ? user.phone.slice(1) : user.phone)
      : '';
    const formPhone = form.phone?.trim() ?? '';
    const nothingChanged =
      !pendingAvatarFile &&
      (form.name ?? '') === (user?.name ?? '') &&
      (form.username ?? '') === (user?.username ?? '') &&
      (form.email ?? '') === (user?.email ?? '') &&
      formPhone === userPhoneDisplay;

    if (nothingChanged) {
      showToast('No changes were made to your profile', 'warning');
      return;
    }

    setSubmitting(true);
    try {
      const { user: updated } = await authApi.updateProfile({
        name: form.name,
        username: form.username,
        email: form.email,
        phone: form.phone || undefined,
      });
      if (pendingAvatarFile) {
        await authApi.uploadUserAvatar(pendingAvatarFile);
        setPendingAvatarFile(null);
      }
      await refreshUser();
      if (avatarPreviewUrl?.startsWith('blob:')) {
        URL.revokeObjectURL(avatarPreviewUrl);
      }
      setAvatarPreviewUrl(pendingAvatarFile ? null : (updated.avatar_url ?? null));
      showToast('Profile updated successfully', 'success');
      const phoneRaw = updated.phone ?? '';
      const phoneDisplay = phoneRaw.startsWith('+') ? phoneRaw.slice(1) : phoneRaw;
      setForm({
        name: updated.name ?? '',
        username: updated.username ?? '',
        email: updated.email ?? '',
        phone: phoneDisplay,
      });
    } catch (err) {
      if (err instanceof ValidationError) {
        const next: Partial<Record<FormKey, string>> = {};
        const keys: FormKey[] = ['name', 'username', 'email', 'phone'];
        for (const [key, messages] of Object.entries(err.errors)) {
          const k = key as FormKey;
          if (messages?.length && keys.includes(k)) {
            next[k] = messages[0];
          }
        }
        setFieldErrors(next);
      } else if (err instanceof Error) {
        setAvatarError(err.message);
      } else {
        setAvatarError('Avatar upload failed');
      }
    } finally {
      setSubmitting(false);
    }
  }

  if (authLoading || !user) {
    return <LoadingPage message="Loading profile..." />;
  }

  const initial = user.name?.charAt(0)?.toUpperCase() ?? user.username?.charAt(0)?.toUpperCase() ?? '?';

  return (
    <div className="page-center">
      <div className="card" style={{ maxWidth: 480 }}>
        <h1 className="form-title">Edit profile</h1>
        <p className="form-subtitle">Update your account details</p>

        <div className="profile-avatar-row">
          <label className="profile-avatar-label" htmlFor="profile-avatar-input">
            {avatarPreviewUrl ? (
              <img src={avatarPreviewUrl} alt="" className="profile-avatar-img" />
            ) : (
              <span className="profile-avatar-initial">{initial}</span>
            )}
            <span className="profile-avatar-hint">
              Change photo
            </span>
          </label>
          <input
            id="profile-avatar-input"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            className="profile-avatar-input"
            disabled={submitting}
            onChange={handleAvatarChange}
            aria-label="Upload avatar"
          />
        </div>
        {avatarError && (
          <p className="field-error" style={{ marginTop: '-0.5rem', marginBottom: '0.5rem' }}>
            {avatarError}
          </p>
        )}

        <form onSubmit={handleSubmit}>
          <InputWithIcon
            id="profile-name"
            label="Name"
            icon="user"
            type="text"
            value={form.name}
            onChange={(e) => update('name', e.target.value)}
            placeholder="Your name"
            required
            autoComplete="name"
            error={fieldErrors.name}
          />
          <InputWithIcon
            id="profile-username"
            label="Username"
            icon="user"
            type="text"
            value={form.username}
            onChange={(e) => update('username', e.target.value)}
            placeholder="username"
            required
            autoComplete="username"
            error={fieldErrors.username}
          />
          <InputWithIcon
            id="profile-email"
            label="Email"
            icon="email"
            type="email"
            value={form.email}
            onChange={(e) => update('email', e.target.value)}
            placeholder="you@example.com"
            required
            autoComplete="email"
            error={fieldErrors.email}
          />
          <div className="form-group">
            <label htmlFor="profile-phone">
              Phone
            </label>
            <div className={`input-with-prefix ${fieldErrors.phone ? 'has-error' : ''}`}>
              <span className="input-with-prefix-addon" aria-hidden>+</span>
              <input
                id="profile-phone"
                type="tel"
                value={form.phone}
                onChange={(e) => update('phone', e.target.value)}
                placeholder="1 234 567 890"
                autoComplete="tel"
                className="input-with-prefix-input"
                aria-invalid={!!fieldErrors.phone}
              />
            </div>
            {fieldErrors.phone && <p className="field-error">{fieldErrors.phone}</p>}
          </div>

          <button type="submit" className="btn btn-primary" disabled={submitting} style={{ marginTop: '1rem' }}>
            {submitting ? 'Saving…' : 'Save changes'}
          </button>
        </form>
      </div>
    </div>
  );
}
