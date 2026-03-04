import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import InputWithIcon from '../components/InputWithIcon';
import PasswordField from '../components/PasswordField';
import { useAuth } from '../contexts/AuthContext';

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
  const [passwordMismatch, setPasswordMismatch] = useState(false);

  useEffect(() => {
    clearError();
  }, [clearError]);

  function update(f: keyof typeof form, value: string) {
    setForm((prev) => ({ ...prev, [f]: value }));
    if (f === 'password' || f === 'password_confirmation') {
      setPasswordMismatch(false);
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    clearError();
    setPasswordMismatch(false);
    if (form.password !== form.password_confirmation) {
      setPasswordMismatch(true);
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
    } catch {
      // error set in context
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
        {passwordMismatch && (
          <div className="auth-error">Passwords do not match</div>
        )}

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
