import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  tiersApi,
  type Tier,
  type TierCreatePayload,
  type TierUpdatePayload,
} from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';

const MAX_TIERS = 3;
const CURRENCIES = ['USD', 'EUR', 'CZK'] as const;
const ACCEPT_IMAGE = 'image/jpeg,image/png,image/webp';

type CreateFormState = TierCreatePayload & {
  tier_cover_path?: string | null;
  tier_cover_preview_url?: string | null;
  priceDisplay: string;
};

const emptyForm: CreateFormState = {
  tier_name: '',
  tier_desc: '',
  price: 0,
  tier_currency: 'USD',
  tier_cover_path: null,
  tier_cover_preview_url: null,
  priceDisplay: '',
};

export default function CreatorTiersPage() {
  const { user, loading: authLoading } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const [tiers, setTiers] = useState<Tier[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showAddForm, setShowAddForm] = useState(false);
  const [form, setForm] = useState<CreateFormState>(emptyForm);
  const [submitting, setSubmitting] = useState(false);
  const [uploadingCover, setUploadingCover] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [editForm, setEditForm] = useState<TierUpdatePayload & { tier_cover_path?: string | null; tier_cover_preview_url?: string | null; priceDisplay?: string }>({});
  const [deletingId, setDeletingId] = useState<number | null>(null);
  const [deleteConfirmTierId, setDeleteConfirmTierId] = useState<number | null>(null);

  const loadTiers = useCallback(async () => {
    try {
      const list = await tiersApi.listMine();
      setTiers(Array.isArray(list) ? list : []);
      setError(null);
    } catch (e) {
      setTiers([]);
      setError(e instanceof Error ? e.message : 'Failed to load tiers');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { replace: true });
      return;
    }
    if (user && !user.creator_profile) {
      setLoading(false);
      return;
    }
    if (user?.creator_profile) {
      loadTiers();
    }
  }, [user, authLoading, navigate, loadTiers]);

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    setError(null);
    try {
      const payload: TierCreatePayload = {
        tier_name: form.tier_name,
        tier_desc: form.tier_desc,
        price: form.price,
        tier_currency: form.tier_currency,
      };
      if (form.tier_cover_path) payload.tier_cover_path = form.tier_cover_path;
      await tiersApi.create(payload);
      setForm(emptyForm);
      setShowAddForm(false);
      await loadTiers();
      showToast('Tier created!', 'success');
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to create tier');
    } finally {
      setSubmitting(false);
    }
  };

  const handleCoverFile = useCallback(
    async (e: React.ChangeEvent<HTMLInputElement>) => {
      const file = e.target.files?.[0];
      e.target.value = '';
      if (!file) return;
      if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
        setError('Please choose a JPEG, PNG or WebP image.');
        return;
      }
      setUploadingCover(true);
      setError(null);
      try {
        const { path, url } = await tiersApi.uploadCover(file);
        setForm((prev) => ({
          ...prev,
          tier_cover_path: path,
          tier_cover_preview_url: url,
        }));
      } catch (err) {
        const msg = err instanceof Error ? err.message : 'Cover upload failed';
        const friendly = /fetch|network/i.test(msg) ? 'Incorrect format or size for Cover image' : msg;
        showToast(friendly, 'error');
      } finally {
        setUploadingCover(false);
      }
    },
    [showToast]
  );

  const clearCover = useCallback(() => {
    setForm((prev) => ({ ...prev, tier_cover_path: null, tier_cover_preview_url: null }));
    setError(null);
  }, []);

  const handleEditCoverFile = useCallback(async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file) return;
    if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
      setError('Please choose a JPEG, PNG or WebP image.');
      return;
    }
    setUploadingCover(true);
    setError(null);
    try {
      const { path, url } = await tiersApi.uploadCover(file);
      setEditForm((prev) => ({ ...prev, tier_cover_path: path, tier_cover_preview_url: url }));
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Cover upload failed';
      const friendly = /fetch|network/i.test(msg) ? 'Incorrect format or size for Cover image' : msg;
      showToast(friendly, 'error');
    } finally {
      setUploadingCover(false);
    }
  }, [showToast]);

  const clearEditCover = useCallback(() => {
    setEditForm((prev) => ({ ...prev, tier_cover_path: null, tier_cover_preview_url: null }));
    setError(null);
  }, []);

  const handleUpdate = async (e: React.FormEvent, tierId: number) => {
    e.preventDefault();
    const payload: TierUpdatePayload = { ...editForm };
    delete (payload as Record<string, unknown>).priceDisplay;
    delete (payload as Record<string, unknown>).tier_cover_preview_url;
    if (Object.keys(payload).length === 0) {
      showToast('No changes to save', 'warning');
      setEditingId(null);
      setEditForm({});
      return;
    }
    setSubmitting(true);
    setError(null);
    try {
      await tiersApi.update(tierId, payload);
      setEditingId(null);
      setEditForm({});
      await loadTiers();
      showToast('Tier updated!', 'success');
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to update tier');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (tierId: number) => {
    setDeletingId(tierId);
    setError(null);
    setDeleteConfirmTierId(null);
    try {
      await tiersApi.delete(tierId);
      await loadTiers();
      showToast('Tier deleted!', 'success');
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to delete tier');
    } finally {
      setDeletingId(null);
    }
  };

  if (authLoading || (user && !user.creator_profile && loading)) {
    return <LoadingPage />;
  }

  if (!user) {
    return null;
  }

  if (!user.creator_profile) {
    return (
      <div className="page-center">
        <div className="card" style={{ maxWidth: 420 }}>
          <h1 className="form-title">Creator profile required</h1>
          <p className="form-subtitle">Create a creator profile first to manage subscription tiers.</p>
          <Link to="/creator/new" className="btn btn-primary" style={{ display: 'inline-block', marginTop: '1rem' }}>
            Create profile
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="page-center">
      <div className="card creator-form-card creator-tiers-card" style={{ maxWidth: 560 }}>
        <h1 className="form-title">Subscription Tiers</h1>

        {error && <div className="auth-error">{error}</div>}

        {loading ? (
          <p className="form-subtitle">Loading tiers...</p>
        ) : (
          <>
            <ul className="tier-list tier-list-sidebar tier-list-with-add">
              {tiers.map((tier) => (
                <li key={tier.id} className="tier-card tier-card-stacked" style={{ marginBottom: '1rem' }}>
                  <div className="tier-card-body">
                    {editingId === tier.id ? (
                      <form
                        onSubmit={(e) => handleUpdate(e, tier.id)}
                        style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}
                      >
                        <div className="tier-edit-top-row">
                          <div className="form-group tier-cover-cell">
                            <label>Cover image</label>
                            <div className="form-image-box form-image-box-cover" style={{ maxWidth: 280, aspectRatio: '16/9' }}>
                              {(editForm.tier_cover_preview_url ?? tier.tier_cover_url) ? (
                                <img src={editForm.tier_cover_preview_url ?? tier.tier_cover_url ?? ''} alt="" className="form-image-preview" style={{ objectFit: 'cover' }} />
                              ) : (
                                <span className="form-image-placeholder">No image</span>
                              )}
                            </div>
                            <div className="form-image-actions" style={{ marginTop: '0.35rem' }}>
                              <input
                                id={`tier-edit-cover-${tier.id}`}
                                type="file"
                                accept={ACCEPT_IMAGE}
                                onChange={handleEditCoverFile}
                                className="form-image-input"
                                disabled={!!uploadingCover}
                              />
                              <span className="form-image-btn-wrap">
                                <label htmlFor={`tier-edit-cover-${tier.id}`} className="btn btn-secondary btn-sm form-image-btn">
                                  {uploadingCover ? 'Uploading…' : (editForm.tier_cover_preview_url ?? tier.tier_cover_url) ? 'Change' : 'Upload'}
                                </label>
                              </span>
                              {(editForm.tier_cover_path !== undefined || editForm.tier_cover_preview_url || tier.tier_cover_url) && (
                                <button type="button" className="btn btn-secondary btn-sm form-image-btn" onClick={clearEditCover}>
                                  Remove
                                </button>
                              )}
                            </div>
                          </div>
                          <div className="form-group tier-price-currency-cell">
                            <div className="tier-price-currency-row" style={{ marginBottom: 0 }}>
                              <div className="tier-price-wrap">
                                <label className="tier-price-currency-label">Price</label>
                                <input
                                  type="number"
                                  min={0}
                                  value={editForm.priceDisplay !== undefined ? editForm.priceDisplay : (tier.price === 0 ? '' : String(tier.price))}
                                  onChange={(e) => {
                                    const v = e.target.value;
                                    setEditForm((f) => ({ ...f, priceDisplay: v, price: v === '' ? 0 : parseInt(v, 10) || 0 }));
                                  }}
                                  className="tier-price-input"
                                />
                              </div>
                              <div className="tier-currency-wrap">
                                <span className="tier-price-currency-label">Currency</span>
                                <div className="form-tag-chips tier-currency-chips" role="group" aria-label="Currency">
                                  {CURRENCIES.map((c) => {
                                    const selected = (editForm.tier_currency ?? tier.tier_currency ?? 'USD') === c;
                                    return (
                                      <button
                                        key={c}
                                        type="button"
                                        className={`tag-chip ${selected ? 'active' : ''}`}
                                        onClick={() => setEditForm((f) => ({ ...f, tier_currency: c }))}
                                        aria-pressed={selected}
                                      >
                                        {c}
                                      </button>
                                    );
                                  })}
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div className="form-group">
                          <label>Tier name</label>
                          <input
                            type="text"
                            value={editForm.tier_name ?? tier.tier_name}
                            onChange={(e) => setEditForm((f) => ({ ...f, tier_name: e.target.value }))}
                            placeholder="Tier name"
                            required
                            maxLength={50}
                          />
                        </div>
                        <div className="form-group">
                          <label>Description</label>
                          <textarea
                            value={editForm.tier_desc !== undefined ? editForm.tier_desc : (tier.tier_desc ?? '')}
                            onChange={(e) => setEditForm((f) => ({ ...f, tier_desc: e.target.value }))}
                            placeholder="Description"
                            rows={6}
                            maxLength={255}
                            className="form-textarea tier-desc-textarea"
                            required
                          />
                        </div>
                        <div style={{ display: 'flex', gap: '0.5rem' }}>
                          <button type="submit" className="btn btn-primary btn-sm" disabled={submitting}>
                            Save
                          </button>
                          <button
                            type="button"
                            className="btn btn-secondary btn-sm"
                            onClick={() => { setEditingId(null); setEditForm({}); }}
                          >
                            Cancel
                          </button>
                        </div>
                      </form>
                    ) : (
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '0.5rem' }}>
                        <div className="tier-card-content">
                          <h3 className="tier-card-name">Level {tier.level} – {tier.tier_name}</h3>
                          {tier.tier_desc && (
                            <p className="tier-card-desc" style={{ whiteSpace: 'pre-line', margin: '0.25rem 0 0 0' }}>
                              {tier.tier_desc}
                            </p>
                          )}
                          <p className="tier-card-price" style={{ marginTop: '0.35rem' }}>
                            {tier.price === 0 ? 'Free' : `${tier.tier_currency ?? ''} ${tier.price}`}
                          </p>
                        </div>
                        <div className="tier-card-actions-col">
                          <button
                            type="button"
                            className="btn btn-secondary btn-sm"
                            onClick={() => { setEditingId(tier.id); setEditForm({}); }}
                          >
                            Edit
                          </button>
                          <button
                            type="button"
                            className="btn btn-secondary btn-sm"
                            onClick={() => setDeleteConfirmTierId(tier.id)}
                            disabled={deletingId === tier.id}
                          >
                            {deletingId === tier.id ? 'Deleting…' : 'Delete'}
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </li>
              ))}
            </ul>

            {tiers.length < MAX_TIERS && !editingId && (
              <div className="tier-add-wrap">
                {!showAddForm ? (
                  <button
                    type="button"
                    className="btn btn-primary"
                    onClick={() => setShowAddForm(true)}
                  >
                    Add tier
                  </button>
                ) : (
                  <form onSubmit={handleCreate} style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                    <div className="tier-create-top-row">
                      <div className="form-group tier-cover-cell">
                        <label>Cover image</label>
                        <div className="form-image-box form-image-box-cover" style={{ maxWidth: 320, aspectRatio: '16/9' }}>
                          {form.tier_cover_preview_url ? (
                            <img src={form.tier_cover_preview_url} alt="" className="form-image-preview" style={{ objectFit: 'cover' }} />
                          ) : (
                            <span className="form-image-placeholder">No image</span>
                          )}
                        </div>
                        <div className="form-image-actions" style={{ marginTop: '0.35rem' }}>
                          <input
                            type="file"
                            accept={ACCEPT_IMAGE}
                            onChange={handleCoverFile}
                            className="form-image-input"
                            id="tier-cover-upload"
                            disabled={!!uploadingCover}
                          />
                          <span className="form-image-btn-wrap">
                            <label htmlFor="tier-cover-upload" className="btn btn-secondary btn-sm form-image-btn">
                              {uploadingCover ? 'Uploading…' : form.tier_cover_preview_url ? 'Change' : 'Upload'}
                            </label>
                          </span>
                          {(form.tier_cover_preview_url || form.tier_cover_path) && (
                            <button type="button" className="btn btn-secondary btn-sm form-image-btn" onClick={clearCover}>
                              Remove
                            </button>
                          )}
                        </div>
                        <span className="form-hint">JPEG, PNG or WebP (max 5 MB)</span>
                      </div>
                      <div className="form-group tier-price-currency-cell">
                        <div className="tier-price-currency-row" style={{ marginBottom: 0 }}>
                          <div className="tier-price-wrap">
                            <label htmlFor="tier_price" className="tier-price-currency-label">Price</label>
                            <input
                              id="tier_price"
                              type="number"
                              min={0}
                              value={form.priceDisplay}
                              onChange={(e) => {
                                const v = e.target.value;
                                setForm((f) => ({ ...f, priceDisplay: v, price: v === '' ? 0 : parseInt(v, 10) || 0 }));
                              }}
                              className="tier-price-input"
                            />
                          </div>
                          <div className="tier-currency-wrap">
                            <span className="tier-price-currency-label" id="tier_currency_label">Currency</span>
                            <div className="form-tag-chips tier-currency-chips" role="group" aria-labelledby="tier_currency_label">
                              {CURRENCIES.map((c) => {
                                const selected = form.tier_currency === c;
                                return (
                                  <button
                                    key={c}
                                    type="button"
                                    className={`tag-chip ${selected ? 'active' : ''}`}
                                    onClick={() => setForm((f) => ({ ...f, tier_currency: c }))}
                                    aria-pressed={selected}
                                  >
                                    {c}
                                  </button>
                                );
                              })}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className="form-group">
                      <label htmlFor="tier_name">Tier name</label>
                      <input
                        id="tier_name"
                        type="text"
                        value={form.tier_name}
                        onChange={(e) => setForm((f) => ({ ...f, tier_name: e.target.value }))}
                        placeholder="e.g. Supporter"
                        required
                        maxLength={50}
                      />
                    </div>
                    <div className="form-group">
                      <label htmlFor="tier_desc">Description</label>
                      <textarea
                        id="tier_desc"
                        value={form.tier_desc}
                        onChange={(e) => setForm((f) => ({ ...f, tier_desc: e.target.value }))}
                        placeholder="What do subscribers get?"
                        rows={6}
                        maxLength={255}
                        className="form-textarea tier-desc-textarea"
                        required
                      />
                      <span className="form-hint">Max 255 characters</span>
                    </div>
                    <div style={{ display: 'flex', gap: '0.5rem' }}>
                      <button type="submit" className="btn btn-primary" disabled={submitting}>
                        {submitting ? 'Creating…' : 'Create tier'}
                      </button>
                      <button
                        type="button"
                        className="btn btn-secondary"
                        onClick={() => { setShowAddForm(false); setForm(emptyForm); }}
                      >
                        Cancel
                      </button>
                    </div>
                  </form>
                )}
              </div>
            )}

            {tiers.length === 0 && !showAddForm && (
              <p className="form-subtitle creator-tiers-empty-hint">No tiers yet. Add one above to get started</p>
            )}
          </>
        )}

        <div style={{ marginTop: '1.5rem', paddingTop: '1rem', borderTop: '1px solid var(--border)' }}>
          <Link to={user.creator_profile?.slug ? `/creator/${user.creator_profile.slug}` : '/explore'} className="btn btn-secondary">
            Back to my page
          </Link>
        </div>
      </div>

      {deleteConfirmTierId !== null && (
        <div
          className="tier-delete-overlay"
          role="dialog"
          aria-modal="true"
          aria-labelledby="tier-delete-title"
        >
          <div className="tier-delete-card card">
            <h2 id="tier-delete-title" className="form-title">Delete tier?</h2>
            <p className="form-subtitle" style={{ marginBottom: '1rem' }}>
              Existing subscribers may be affected. This cannot be undone
            </p>
            <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
              <button
                type="button"
                className="btn btn-primary"
                disabled={deletingId !== null}
                onClick={() => deleteConfirmTierId !== null && handleDelete(deleteConfirmTierId)}
              >
                {deletingId !== null ? 'Deleting…' : 'Delete'}
              </button>
              <button
                type="button"
                className="btn btn-secondary"
                onClick={() => setDeleteConfirmTierId(null)}
                disabled={deletingId !== null}
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
