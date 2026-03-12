import { useEffect, useState } from 'react';
import { useToast } from '../../contexts/ToastContext';
import type { SocialKey } from './types';
import { validateSocialUrl } from './utils';

function SocialIcon({ keyName }: { keyName: SocialKey }) {
  const icons: Record<SocialKey, React.ReactNode> = {
    telegram_url: (
      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden>
        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
      </svg>
    ),
    instagram_url: (
      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden>
        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
      </svg>
    ),
    facebook_url: (
      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden>
        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
      </svg>
    ),
    youtube_url: (
      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden>
        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
      </svg>
    ),
    twitch_url: (
      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden>
        <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z" />
      </svg>
    ),
    website_url: (
      <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      >
        <circle cx="12" cy="12" r="10" />
        <line x1="2" y1="12" x2="22" y2="12" />
        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
      </svg>
    ),
  };
  return <>{icons[keyName]}</>;
}

export function SocialLinkModal({
  keyName,
  label,
  value,
  onSave,
  onRemove,
  onClose,
}: {
  keyName: SocialKey;
  label: string;
  value: string;
  onSave: (value: string) => void;
  onRemove: () => void;
  onClose: () => void;
}) {
  const [input, setInput] = useState(value);
  const [inputHighlightError, setInputHighlightError] = useState(false);
  const hasExisting = (value || '').trim().length > 0;
  const { showToast } = useToast();
  useEffect(() => setInput(value), [value, keyName]);

  useEffect(() => {
    if (!inputHighlightError) return;
    const t = setTimeout(() => setInputHighlightError(false), 1500);
    return () => clearTimeout(t);
  }, [inputHighlightError]);

  const handleSaveClick = () => {
    const trimmed = input.trim();
    setInputHighlightError(false);
    if (!trimmed) {
      onSave('');
      return;
    }
    const error = validateSocialUrl(keyName, trimmed);
    if (error) {
      setInputHighlightError(true);
      showToast(error, 'error');
      return;
    }
    onSave(trimmed);
  };

  return (
    <div
      className="tier-delete-overlay"
      role="dialog"
      aria-modal="true"
      aria-labelledby="social-modal-title"
      onClick={onClose}
    >
      <div
        className="tier-delete-card card"
        onClick={(e) => e.stopPropagation()}
      >
        <h2 id="social-modal-title" className="form-title">
          {hasExisting ? `Edit ${label} link` : `Add ${label} link`}
        </h2>
        <div className="form-group">
          <label htmlFor="social-link-url">URL</label>
          <input
            id="social-link-url"
            type="url"
            value={input}
            onChange={(e) => {
              setInput(e.target.value);
              setInputHighlightError(false);
            }}
            placeholder={
              keyName === 'website_url'
                ? 'https://example.com'
                : `https://${keyName.replace('_url', '')}.com/...`
            }
            style={{ width: '100%', maxWidth: '360px' }}
            className={inputHighlightError ? 'social-link-input-error' : ''}
            aria-invalid={inputHighlightError}
          />
        </div>
        <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
          <button
            type="button"
            className="btn btn-primary"
            onClick={handleSaveClick}
          >
            Save
          </button>
          {hasExisting && (
            <button
              type="button"
              className="btn btn-secondary"
              onClick={onRemove}
            >
              Remove link
            </button>
          )}
          <button
            type="button"
            className="btn btn-secondary"
            onClick={onClose}
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  );
}

export { SocialIcon };
