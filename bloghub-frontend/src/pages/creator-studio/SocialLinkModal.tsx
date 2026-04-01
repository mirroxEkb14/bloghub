import { useEffect, useState } from 'react';
import { useToast } from '../../contexts/ToastContext';
import { TelegramIcon, InstagramIcon, FacebookIcon, YouTubeIcon, TwitchIcon, WebsiteIcon } from '../../components/icons';
import type { SocialKey } from './types';
import { validateSocialUrl } from './utils';

const socialIconMap: Record<SocialKey, React.ComponentType<{ size?: number; className?: string; ariaHidden?: boolean }>> = {
  telegram_url: TelegramIcon,
  instagram_url: InstagramIcon,
  facebook_url: FacebookIcon,
  youtube_url: YouTubeIcon,
  twitch_url: TwitchIcon,
  website_url: WebsiteIcon,
};

function SocialIcon({ keyName }: { keyName: SocialKey }) {
  const Icon = socialIconMap[keyName];
  return <Icon size={24} />;
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
