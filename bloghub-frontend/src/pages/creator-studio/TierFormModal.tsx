import { useEffect, useState } from 'react';
import { tiersApi } from '../../api/client';
import type { Tier } from '../../api/client';
import { useToast } from '../../contexts/ToastContext';
import type { DraftTier } from './types';
import { ACCEPT_IMAGE, CURRENCIES } from './constants';

export function TierFormModal({
  tier,
  isNew,
  onSave,
  onClose,
}: {
  tier: DraftTier;
  isNew: boolean;
  onSave: (tier: DraftTier, isNew: boolean) => void;
  onClose: () => void;
}) {
  const [form, setForm] = useState({
    tier_name: tier.tier_name ?? '',
    tier_desc: tier.tier_desc ?? '',
    price: tier.price ?? 0,
    tier_currency: (tier.tier_currency ?? 'USD') as string,
    tier_cover_path: (tier as Tier & { tier_cover_path?: string })
      .tier_cover_path ?? '',
    tier_cover_preview_url:
      tier.tier_cover_url ?? (tier as DraftTier).tier_cover_preview_url ?? '',
  });
  const [uploading, setUploading] = useState(false);
  const { showToast } = useToast();

  useEffect(() => {
    setForm({
      tier_name: tier.tier_name ?? '',
      tier_desc: tier.tier_desc ?? '',
      price: tier.price ?? 0,
      tier_currency: (tier.tier_currency ?? 'USD') as string,
      tier_cover_path: (tier as Tier & { tier_cover_path?: string })
        .tier_cover_path ?? '',
      tier_cover_preview_url:
        tier.tier_cover_url ?? (tier as DraftTier).tier_cover_preview_url ?? '',
    });
  }, [tier]);

  const handleCoverFile = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file || !file.type.match(/^image\/(jpeg|png|webp)$/)) {
      showToast('Use JPEG, PNG or WebP', 'error');
      return;
    }
    setUploading(true);
    try {
      const { path, url } = await tiersApi.uploadCover(file);
      setForm((f) => ({
        ...f,
        tier_cover_path: path,
        tier_cover_preview_url: url,
      }));
    } catch {
      showToast('Cover upload failed', 'error');
    } finally {
      setUploading(false);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.tier_name.trim()) {
      showToast('Tier name is required', 'error');
      return;
    }
    const saved: DraftTier = {
      ...tier,
      tier_name: form.tier_name.trim(),
      tier_desc: form.tier_desc.trim() || null,
      price: form.price ?? 0,
      tier_currency: form.tier_currency,
      tier_cover_url: form.tier_cover_preview_url || null,
      _isNew: tier._isNew,
    };
    (saved as DraftTier & { tier_cover_path?: string }).tier_cover_path =
      form.tier_cover_path || undefined;
    onSave(saved, isNew);
  };

  return (
    <div
      className="tier-delete-overlay"
      role="dialog"
      aria-modal="true"
      aria-labelledby="tier-modal-title"
    >
      <div className="tier-delete-card card">
        <h2 id="tier-modal-title" className="form-title">
          {isNew ? 'Add tier' : 'Edit tier'}
        </h2>
        <form
          onSubmit={handleSubmit}
          style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}
        >
          <div className="form-group">
            <label className="form-label-required">Tier name</label>
            <input
              type="text"
              value={form.tier_name}
              onChange={(e) =>
                setForm((f) => ({ ...f, tier_name: e.target.value }))}
              maxLength={50}
              required
            />
          </div>
          <div className="form-group">
            <label>Description</label>
            <textarea
              value={form.tier_desc}
              onChange={(e) =>
                setForm((f) => ({ ...f, tier_desc: e.target.value }))}
              rows={4}
              maxLength={255}
              className="form-textarea"
            />
          </div>
          <div className="form-group">
            <label>Price</label>
            <input
              type="number"
              min={0}
              value={form.price || ''}
              onChange={(e) =>
                setForm((f) => ({
                  ...f,
                  price: parseInt(e.target.value, 10) || 0,
                }))}
            />
          </div>
          <div className="form-group">
            <label>Currency</label>
            <div className="form-tag-chips" role="group">
              {CURRENCIES.map((c) => (
                <button
                  key={c}
                  type="button"
                  className={`tag-chip ${form.tier_currency === c ? 'active' : ''}`}
                  onClick={() =>
                    setForm((f) => ({ ...f, tier_currency: c }))}
                >
                  {c}
                </button>
              ))}
            </div>
          </div>
          <div className="form-group">
            <label>Cover image</label>
            <div
              className="form-image-box form-image-box-cover"
              style={{ aspectRatio: '16/9', maxWidth: 280 }}
            >
              {form.tier_cover_preview_url ? (
                <img
                  src={form.tier_cover_preview_url}
                  alt=""
                  style={{ objectFit: 'cover' }}
                />
              ) : (
                <span className="form-image-placeholder">Optional</span>
              )}
            </div>
            <input
              type="file"
              accept={ACCEPT_IMAGE}
              onChange={handleCoverFile}
              disabled={uploading}
              className="form-image-input"
            />
            <label className="btn btn-secondary btn-sm" style={{ marginTop: 4 }}>
              {uploading ? 'Uploading…' : 'Upload'}
            </label>
          </div>
          <div style={{ display: 'flex', gap: '0.5rem' }}>
            <button type="submit" className="btn btn-primary">
              Save
            </button>
            <button
              type="button"
              className="btn btn-secondary"
              onClick={onClose}
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
