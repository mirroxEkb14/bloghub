import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import InputWithIcon from '../components/InputWithIcon';
import PasswordField from '../components/PasswordField';
import { useAuth } from '../contexts/AuthContext';

export default function Login() {
  const { login, error, clearError } = useAuth();
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    clearError();
  }, [clearError]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    clearError();
    setSubmitting(true);
    try {
      await login(email, password);
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
            onChange={(e) => setEmail(e.target.value)}
            placeholder="you@example.com"
            required
            autoComplete="email"
          />
          <PasswordField
            id="password"
            label="Password"
            value={password}
            onChange={setPassword}
            placeholder="qWerty123456!"
            required
            autoComplete="current-password"
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
