import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ValidationError } from '../api/client';
import InputWithIcon from '../components/InputWithIcon';
import PasswordField from '../components/PasswordField';
import { useAuth } from '../contexts/AuthContext';

type FormKey = 'name' | 'username' | 'email' | 'password' | 'password_confirmation';

export default function Register() {
  const { register, error, clearError } = useAuth();
  const navigate = useNavigate();
  const [form, setForm] = useState({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [submitting, setSubmitting] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Partial<Record<FormKey, string>>>({});

  useEffect(() => {
    clearError();
  }, [clearError]);

  function update(f: FormKey, value: string) {
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

    setSubmitting(true);
    try {
      await register({
        name: form.name,
        username: form.username,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
      });
      navigate('/', { replace: true });
    } catch (err) {
      if (err instanceof ValidationError) {
        const next: Partial<Record<FormKey, string>> = {};
        for (const [key, messages] of Object.entries(err.errors)) {
          const k = key as FormKey;
          if (messages?.length && (k === 'name' || k === 'username' || k === 'email' || k === 'password' || k === 'password_confirmation')) {
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
