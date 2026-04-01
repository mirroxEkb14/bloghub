import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ValidationError } from '../api/client';
import InputWithIcon from '../components/InputWithIcon';
import PasswordField from '../components/PasswordField';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';

type FormKey = 'name' | 'username' | 'email' | 'password' | 'password_confirmation' | 'terms_accepted' | 'privacy_accepted';

export default function Register() {
  const { user, register, error, clearError } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const [form, setForm] = useState({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms_accepted: false,
    privacy_accepted: false,
  });
  const [submitting, setSubmitting] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Partial<Record<FormKey, string>>>({});

  useEffect(() => {
    if (user) navigate('/', { replace: true });
  }, [user, navigate]);

  useEffect(() => {
    clearError();
  }, [clearError]);

  function update(f: FormKey, value: string | boolean) {
    setForm((prev) => ({ ...prev, [f]: value }));
    setFieldErrors((prev) => {
      const next = { ...prev };
      delete next[f];
      return next;
    });
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    clearError();
    setFieldErrors({});

    if (form.password !== form.password_confirmation) {
      setFieldErrors({
        password: 'Passwords do not match',
        password_confirmation: 'Passwords do not match',
      });
      return;
    }

    if (!form.terms_accepted || !form.privacy_accepted) {
      setFieldErrors({
        terms_accepted: !form.terms_accepted ? 'You must accept the Terms of Service' : undefined,
        privacy_accepted: !form.privacy_accepted ? 'You must accept the Privacy Policy' : undefined,
      });
      return;
    }

    setSubmitting(true);
    try {
      await register({
        name: form.name,
        username: form.username,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
        terms_accepted: true,
        privacy_accepted: true,
      });
      showToast('Account created. Please check your email to verify your account', 'success');
      navigate('/', { replace: true });
    } catch (err) {
      if (err instanceof ValidationError) {
        const next: Partial<Record<FormKey, string>> = {};
        const keys: FormKey[] = ['name', 'username', 'email', 'password', 'password_confirmation', 'terms_accepted', 'privacy_accepted'];
        for (const [key, messages] of Object.entries(err.errors)) {
          const k = key as FormKey;
          if (messages?.length && keys.includes(k)) {
            next[k] = messages[0];
          }
        }
        setFieldErrors(next);
        clearError();
      }
      throw err;
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="page-center">
      <div className="card">
        <h1 className="form-title">Create account</h1>
        <p className="form-subtitle">Join BlogHub</p>

        {error && <div className="auth-error">{error}</div>}

        <form onSubmit={handleSubmit}>
          <InputWithIcon
            id="name"
            label="Name"
            icon="user"
            type="text"
            value={form.name}
            onChange={(e) => update('name', e.target.value)}
            placeholder="Fox Mulder"
            required
            maxLength={100}
            autoComplete="name"
            error={fieldErrors.name}
          />
          <InputWithIcon
            id="username"
            label="Username"
            icon="user"
            type="text"
            value={form.username}
            onChange={(e) => update('username', e.target.value)}
            placeholder="trust_no1"
            required
            maxLength={50}
            autoComplete="username"
            error={fieldErrors.username}
          />
          <InputWithIcon
            id="email"
            label="Email"
            icon="email"
            type="email"
            value={form.email}
            onChange={(e) => update('email', e.target.value)}
            placeholder="trust_no1@gmail.com"
            required
            autoComplete="email"
            error={fieldErrors.email}
          />
          <PasswordField
            id="password"
            label="Password"
            value={form.password}
            onChange={(v) => update('password', v)}
            placeholder="qWerty123456!"
            required
            minLength={8}
            autoComplete="new-password"
            error={fieldErrors.password}
          />
          <PasswordField
            id="password_confirmation"
            label="Confirm password"
            value={form.password_confirmation}
            onChange={(v) => update('password_confirmation', v)}
            placeholder="qWerty123456!"
            required
            minLength={8}
            autoComplete="new-password"
            error={fieldErrors.password_confirmation}
          />
          <div className="form-group accept-legal-checkboxes">
            <label className="checkbox-label">
              <input
                type="checkbox"
                checked={form.terms_accepted}
                onChange={(e) => update('terms_accepted', e.target.checked)}
              />
              <span>
                I accept the <Link to="/terms" target="_blank" rel="noopener noreferrer">Terms of Service</Link>
              </span>
            </label>
            {fieldErrors.terms_accepted && <p className="field-error">{fieldErrors.terms_accepted}</p>}
            <label className="checkbox-label">
              <input
                type="checkbox"
                checked={form.privacy_accepted}
                onChange={(e) => update('privacy_accepted', e.target.checked)}
              />
              <span>
                I accept the <Link to="/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</Link>
              </span>
            </label>
            {fieldErrors.privacy_accepted && <p className="field-error">{fieldErrors.privacy_accepted}</p>}
          </div>
          <div className="form-actions">
            <button type="submit" className="btn btn-primary" disabled={submitting}>
              {submitting ? 'Creating account...' : 'Register'}
            </button>
          </div>
        </form>

        <p className="form-footer">
          Already have an account? <Link to="/login">Log in</Link>
        </p>
      </div>
    </div>
  );
}
