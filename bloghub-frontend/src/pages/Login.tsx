import { useEffect, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { ValidationError } from '../api/client';
import InputWithIcon from '../components/InputWithIcon';
import PasswordField from '../components/PasswordField';
import { useAuth } from '../contexts/AuthContext';

type LoginFormKey = 'email' | 'password';

export default function Login() {
  const { login, error, clearError } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const from = (location.state as { from?: string } | null)?.from ?? '/';
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Partial<Record<LoginFormKey, string>>>({});

  useEffect(() => {
    clearError();
  }, [clearError]);

  function update(field: LoginFormKey, value: string) {
    if (field === 'email') setEmail(value);
    else setPassword(value);
    setFieldErrors((prev) => {
      const next = { ...prev };
      delete next[field];
      return next;
    });
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    clearError();
    setFieldErrors({});
    setSubmitting(true);
    try {
      await login(email, password);
      navigate(from, { replace: true });
    } catch (err) {
      clearError();
      if (err instanceof ValidationError) {
        const next: Partial<Record<LoginFormKey, string>> = {};
        for (const [key, messages] of Object.entries(err.errors)) {
          const k = key as LoginFormKey;
          if (messages?.length && (k === 'email' || k === 'password')) {
            next[k] = messages[0];
          }
        }
        setFieldErrors(next);
      } else {
        const message = err instanceof Error ? err.message : 'Login failed';
        setFieldErrors({ email: message, password: message });
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="page-center">
      <div className="card">
        <h1 className="form-title">Log in</h1>
        <p className="form-subtitle">Welcome back to BlogHub</p>

        {error && <div className="auth-error">{error}</div>}

        <form onSubmit={handleSubmit}>
          <InputWithIcon
            id="email"
            label="Email"
            icon="email"
            type="email"
            value={email}
            onChange={(e) => update('email', e.target.value)}
            placeholder="you@example.com"
            required
            autoComplete="email"
            error={fieldErrors.email}
          />
          <PasswordField
            id="password"
            label="Password"
            value={password}
            onChange={(v) => update('password', v)}
            placeholder="qWerty123456!"
            required
            autoComplete="current-password"
            error={fieldErrors.password}
          />
          <div className="form-actions">
            <button type="submit" className="btn btn-primary" disabled={submitting}>
              {submitting ? 'Signing in...' : 'Log in'}
            </button>
          </div>
        </form>

        <p className="form-footer">
          Don't have an account? <Link to="/register">Register</Link>
        </p>
      </div>
    </div>
  );
}
