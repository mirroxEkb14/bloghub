import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function AcceptLegalModal() {
  const { user, acceptTermsAndPrivacy } = useAuth();
  const [termsChecked, setTermsChecked] = useState(false);
  const [privacyChecked, setPrivacyChecked] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const mustAccept =
    user &&
    (!user.terms_accepted_at || !user.privacy_accepted_at);

  if (!mustAccept) return null;

  const canSubmit = termsChecked && privacyChecked;

  async function handleAccept() {
    if (!canSubmit) return;
    setError(null);
    setSubmitting(true);
    try {
      await acceptTermsAndPrivacy();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Something went wrong');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="accept-legal-overlay" role="dialog" aria-modal="true" aria-labelledby="accept-legal-title">
      <div className="accept-legal-card card">
        <h1 id="accept-legal-title" className="form-title">Terms & Privacy</h1>
        <p className="form-subtitle">
          Please accept our Terms of Service and Privacy Policy to continue using BlogHub.
        </p>
        {error && <div className="auth-error">{error}</div>}
        <div className="form-group accept-legal-checkboxes">
          <label className="checkbox-label">
            <input
              type="checkbox"
              checked={termsChecked}
              onChange={(e) => setTermsChecked(e.target.checked)}
            />
            <span>
              I accept the <Link to="/terms" target="_blank" rel="noopener noreferrer">Terms of Service</Link>
            </span>
          </label>
          <label className="checkbox-label">
            <input
              type="checkbox"
              checked={privacyChecked}
              onChange={(e) => setPrivacyChecked(e.target.checked)}
            />
            <span>
              I accept the <Link to="/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</Link>
            </span>
          </label>
        </div>
        <div className="form-actions">
          <button
            type="button"
            className="btn btn-primary"
            disabled={!canSubmit || submitting}
            onClick={handleAccept}
          >
            {submitting ? 'Accepting...' : 'Accept and continue'}
          </button>
        </div>
      </div>
    </div>
  );
}
