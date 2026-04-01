import { useEffect, useMemo, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { subscriptionsApi, type SubscriptionWithTier } from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { CheckIcon, CircleCheckIcon, ClockIcon, LayersIcon, XCircleIcon } from '../components/icons';
import { useAuth } from '../contexts/AuthContext';
import '../styles/memberships.css';

const STATUS_TABS: { id: StatusTab; label: string; Icon: React.ComponentType<{ size?: number; className?: string }> }[] = [
  { id: 'All', label: 'All', Icon: LayersIcon },
  { id: 'Active', label: 'Active', Icon: CheckIcon },
  { id: 'Canceled', label: 'Canceled', Icon: XCircleIcon },
  { id: 'Expired', label: 'Expired', Icon: ClockIcon },
];
type StatusTab = 'All' | 'Active' | 'Canceled' | 'Expired';

function formatDate(iso: string | null): string {
  if (!iso) return '—';
  return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
}

function getStatusLabel(sub: SubscriptionWithTier): 'Active' | 'Canceled' | 'Expired' {
  const endDate = sub.end_date ? new Date(sub.end_date) : null;
  if (sub.sub_status === 'Canceled') return 'Canceled';
  if (endDate !== null && endDate < new Date()) return 'Expired';
  return 'Active';
}

export default function SubscriptionsPage() {
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const statusParam = (searchParams.get('status') ?? 'all').toLowerCase();
  const activeTab: StatusTab =
    statusParam === 'all' ? 'All'
    : statusParam === 'active' ? 'Active'
    : statusParam === 'canceled' ? 'Canceled'
    : statusParam === 'expired' ? 'Expired'
    : 'All';

  const [subscriptions, setSubscriptions] = useState<SubscriptionWithTier[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [cancelingId, setCancelingId] = useState<number | null>(null);
  const [cancelToastMessage, setCancelToastMessage] = useState<string | null>(null);
  const [confirmCancelSub, setConfirmCancelSub] = useState<SubscriptionWithTier | null>(null);
  const [cursorLabel, setCursorLabel] = useState<{ x: number; y: number; text: string } | null>(null);

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/login', { state: { from: '/subscriptions' }, replace: true });
    }
  }, [user, authLoading, navigate]);

  useEffect(() => {
    if (!user) return;
    let cancelled = false;
    setLoading(true);
    setError(null);
    subscriptionsApi
      .list()
      .then((list) => {
        if (!cancelled) setSubscriptions(Array.isArray(list) ? list : []);
      })
      .catch((e) => {
        if (!cancelled) setError(e instanceof Error ? e.message : 'Failed to load subscriptions');
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => { cancelled = true; };
  }, [user]);

  const TOAST_BAR_S = 4;
  const TOAST_VISIBLE_MS = 4100;

  useEffect(() => {
    if (!cancelToastMessage) return;
    const t = setTimeout(() => setCancelToastMessage(null), TOAST_VISIBLE_MS);
    return () => clearTimeout(t);
  }, [cancelToastMessage]);

  function requestCancel(sub: SubscriptionWithTier) {
    setConfirmCancelSub(sub);
  }

  async function handleCancel(sub: SubscriptionWithTier, endNow: boolean) {
    setConfirmCancelSub(null);
    setError(null);
    setCancelingId(sub.id);
    try {
      const res = await subscriptionsApi.cancel(sub.id, { endNow });
      setSubscriptions((prev) =>
        prev.map((s) => (s.id === sub.id ? res.subscription : s))
      );
      const label = res.subscription?.creator?.display_name && res.subscription?.tier?.tier_name
        ? `${res.subscription.creator.display_name} – ${res.subscription.tier.tier_name}`
        : 'Subscription';
      setCancelToastMessage(`${label} canceled`);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to cancel subscription');
    } finally {
      setCancelingId(null);
    }
  }

  const filteredSubscriptions = useMemo(() => {
    if (activeTab === 'All') return subscriptions;
    return subscriptions.filter((sub) => getStatusLabel(sub) === activeTab);
  }, [subscriptions, activeTab]);

  function setStatusTab(tab: StatusTab) {
    if (tab === 'All') {
      setSearchParams((prev) => {
        const next = new URLSearchParams(prev);
        next.delete('status');
        return next;
      });
    } else {
      setSearchParams({ status: tab.toLowerCase() });
    }
  }

  if (authLoading || !user) {
    return <LoadingPage />;
  }

  return (
    <div className="page-center memberships-page">
      {cursorLabel && (
        <div
          className="membership-cursor-label"
          style={{ left: cursorLabel.x, top: cursorLabel.y }}
          aria-hidden
        >
          {cursorLabel.text}
        </div>
      )}
      {cancelToastMessage && (
        <div
          key={cancelToastMessage}
          className="subscription-toast subscription-toast-success"
          role="status"
          aria-live="polite"
          aria-label="Canceled"
          style={{ ['--toast-duration' as string]: `${TOAST_BAR_S}s` }}
        >
          <span className="subscription-toast-icon subscription-toast-icon-success" aria-hidden>
            <CircleCheckIcon size={20} />
          </span>
          <p className="subscription-toast-msg">{cancelToastMessage}</p>
          <button
            type="button"
            className="subscription-toast-close"
            onClick={() => setCancelToastMessage(null)}
            aria-label="Dismiss"
          >
            ×
          </button>
          <div className="subscription-toast-timer" aria-hidden />
        </div>
      )}

      <div className="memberships-content">
        <div className="memberships-header">
          <h1 className="profile-name">Subscriptions</h1>
          <p className="memberships-subtitle">
            Your tier subscriptions and billing. You can cancel anytime
          </p>
        </div>

        {error && (
          <p className="memberships-error" role="alert">
            {error}
          </p>
        )}

        {loading ? (
          <LoadingPage message="Loading subscriptions…" />
        ) : subscriptions.length === 0 ? (
          <div className="card memberships-empty">
            <p className="memberships-empty-text">You don’t have any subscriptions yet</p>
            <p className="memberships-empty-hint">
              Subscribe to a creator’s tier from their page to see it here
            </p>
            <Link to="/explore" className="btn btn-primary" style={{ marginTop: '1rem' }}>
              Explore creators
            </Link>
          </div>
        ) : (
          <>
            <div className="membership-tabs" role="tablist" aria-label="Filter by status">
              {STATUS_TABS.map(({ id, label, Icon }) => (
                <button
                  key={id}
                  type="button"
                  role="tab"
                  aria-selected={activeTab === id}
                  className={`membership-tab ${activeTab === id ? 'membership-tab-active' : ''}`}
                  onClick={() => setStatusTab(id)}
                >
                  <Icon size={18} className="membership-tab-icon" />
                  <span>{label}</span>
                </button>
              ))}
            </div>
          <ul className="memberships-list">
            {filteredSubscriptions.map((sub) => {
              const statusLabel = getStatusLabel(sub);
              const endDate = sub.end_date ? new Date(sub.end_date) : null;
              const isExpired = endDate !== null && endDate < new Date();
              const isActive = sub.sub_status === 'Active' && !isExpired;
              const creatorSlug = sub.creator?.slug;
              const creatorName = sub.creator?.display_name ?? sub.creator?.slug ?? 'Creator';
              const tierName = sub.tier?.tier_name ?? 'Tier';
              const price = sub.tier?.price;
              const currency = sub.tier?.tier_currency ?? 'USD';

              return (
                <li
                  key={sub.id}
                  className={`membership-list-item ${isActive ? 'membership-card-cancelable' : ''}`}
                >
                  {isActive && (
                    <button
                      type="button"
                      className="membership-card-cancel-icon"
                      onClick={() => requestCancel(sub)}
                      disabled={cancelingId === sub.id}
                      aria-label="Cancel subscription"
                      onMouseEnter={(e) => setCursorLabel({ x: e.clientX + 12, y: e.clientY + 12, text: 'End subscription' })}
                      onMouseMove={(e) => setCursorLabel((l) => l ? { ...l, x: e.clientX + 12, y: e.clientY + 12 } : null)}
                      onMouseLeave={() => setCursorLabel(null)}
                    >
                      <XCircleIcon size={18} />
                    </button>
                  )}
                  <div className={`membership-card ${!isActive ? 'membership-card-inactive' : ''}`}>
                  <div className="membership-card-main">
                    <div className="membership-card-avatar-wrap">
                      {sub.creator?.profile_avatar_url ? (
                        <img
                          src={sub.creator.profile_avatar_url}
                          alt=""
                          className="membership-card-avatar"
                        />
                      ) : (
                        <span className="membership-card-avatar-placeholder">
                          {creatorName.charAt(0).toUpperCase()}
                        </span>
                      )}
                    </div>
                    <div className="membership-card-info">
                      {creatorSlug ? (
                        <Link to={`/creator/${creatorSlug}`} className="membership-card-creator">
                          {creatorName}
                        </Link>
                      ) : (
                        <span className="membership-card-creator">{creatorName}</span>
                      )}
                      <span className="membership-card-tier">{tierName}</span>
                      {price != null && (
                        <span className="membership-card-price">
                          {new Intl.NumberFormat(undefined, {
                            style: 'currency',
                            currency: currency || 'USD',
                          }).format(price)}
                          {isActive && ' / period'}
                        </span>
                      )}
                      <div className="membership-card-dates">
                        {formatDate(sub.start_date)} – {formatDate(sub.end_date)}
                      </div>
                      {statusLabel === 'Canceled' && sub.end_date && new Date(sub.end_date) > new Date() && (
                        <p className="membership-card-access-until" aria-label="Access until end date">
                          Access until {formatDate(sub.end_date)}
                        </p>
                      )}
                      <div className="membership-card-meta">
                        {sub.card_last4 && (
                          <span className="membership-card-last4">•••• {sub.card_last4}</span>
                        )}
                        <span className={`membership-card-status membership-card-status-${statusLabel.toLowerCase()}`}>
                          {statusLabel}
                        </span>
                      </div>
                    </div>
                  </div>
                  </div>
                </li>
              );
            })}
          </ul>
          {filteredSubscriptions.length === 0 && (
            <p className="membership-tabs-empty">No {activeTab.toLowerCase()} subscriptions</p>
          )}
          </>
        )}
      </div>

      {confirmCancelSub && (
        <div className="membership-confirm-overlay" role="dialog" aria-modal="true" aria-labelledby="membership-confirm-title">
          <div className="membership-confirm-wrap">
            <button
              type="button"
              className="membership-confirm-close"
              aria-label="Keep subscription"
              onClick={() => { setConfirmCancelSub(null); setCursorLabel(null); }}
              onMouseEnter={(e) => setCursorLabel({ x: e.clientX + 12, y: e.clientY + 12, text: 'Keep subscription' })}
              onMouseMove={(e) => setCursorLabel((l) => l ? { ...l, x: e.clientX + 12, y: e.clientY + 12 } : null)}
              onMouseLeave={() => setCursorLabel(null)}
            >
              <XCircleIcon size={18} />
            </button>
            <div className="membership-confirm-dialog">
            <h2 id="membership-confirm-title" className="membership-confirm-title">End subscription?</h2>
            <p className="membership-confirm-text">
              End your subscription to{' '}
              <strong>{confirmCancelSub.creator?.display_name ?? confirmCancelSub.creator?.slug ?? 'Creator'}</strong>
              {' – '}
              <strong>{confirmCancelSub.tier?.tier_name ?? 'Tier'}</strong>.
              You can end access right away or keep it until the current period ends ({formatDate(confirmCancelSub.end_date)}). No refunds.
            </p>
            <div className="membership-confirm-actions">
              <button
                type="button"
                className="btn membership-confirm-cancel-btn"
                disabled={cancelingId === confirmCancelSub.id}
                onClick={() => handleCancel(confirmCancelSub, true)}
              >
                {cancelingId === confirmCancelSub.id ? 'Canceling…' : 'End now (lose access)'}
              </button>
              <button
                type="button"
                className="btn membership-confirm-cancel-btn membership-confirm-end-period"
                disabled={cancelingId === confirmCancelSub.id}
                onClick={() => handleCancel(confirmCancelSub, false)}
              >
                End at period end
              </button>
            </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
