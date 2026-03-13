import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { notificationsApi, type NotificationItem } from '../api/client';
import type { PaginatedMeta, PaginatedResponse } from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import LoadingPage from '../components/LoadingPage';
import { CircleCheckIcon, EnvelopeIcon } from '../components/icons';
import '../styles/notifications.css';

function formatDate(iso: string | null): string {
  if (!iso) return '—';
  try {
    const d = new Date(iso);
    const datePart = d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    const timePart = d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
    return `${datePart}, ${timePart}`;
  } catch {
    return iso;
  }
}

type NotificationMessage = {
  creatorName: string;
  creatorSlug: string | undefined;
  creatorAvatarUrl: string | null;
  actionText: string;
  link?: string;
  linkLabel?: string;
};

function notificationMessage(n: NotificationItem): NotificationMessage {
  const d = (n.data ?? {}) as Record<string, unknown>;
  const creatorName = (d.creator_display_name as string) ?? (d.creator_slug as string) ?? 'A creator';
  const creatorSlug = d.creator_slug as string | undefined;
  const creatorAvatarUrl = typeof d.creator_avatar_url === 'string' && d.creator_avatar_url ? d.creator_avatar_url : null;

  switch (n.type) {
    case 'subscription_canceled': {
      const endNow = d.end_now as boolean | undefined;
      const tierName = (d.tier_name as string) ?? 'tier';
      const endDate = d.end_date as string | undefined;
      const actionText = endNow
        ? `You ended your subscription to (${tierName}). Access was removed immediately.`
        : `You ended your subscription to (${tierName}). You have access until ${endDate ? formatDate(endDate) : 'period end'}.`;
      return { creatorName, creatorSlug, creatorAvatarUrl, actionText, link: creatorSlug ? `/creator/${creatorSlug}` : undefined, linkLabel: 'View creator' };
    }
    case 'subscription_expired': {
      const tierName = (d.tier_name as string) ?? 'tier';
      return { creatorName, creatorSlug, creatorAvatarUrl, actionText: `Your subscription to (${tierName}) has expired.`, link: creatorSlug ? `/creator/${creatorSlug}` : undefined, linkLabel: 'View creator' };
    }
    case 'new_post': {
      const postTitle = (d.post_title as string) ?? 'New post';
      const postSlug = d.post_slug as string | undefined;
      const link = creatorSlug && postSlug ? `/creator/${creatorSlug}/post/${postSlug}` : creatorSlug ? `/creator/${creatorSlug}` : undefined;
      return { creatorName, creatorSlug, creatorAvatarUrl, actionText: `published: ${postTitle}`, link, linkLabel: 'View post' };
    }
    case 'tier_created': {
      const tierName = (d.tier_name as string) ?? 'a tier';
      return { creatorName, creatorSlug, creatorAvatarUrl, actionText: `added a new tier: ${tierName}`, link: creatorSlug ? `/creator/${creatorSlug}#profile-tiers` : undefined, linkLabel: 'View tiers' };
    }
    case 'tier_edited': {
      const tierName = (d.tier_name as string) ?? 'a tier';
      return { creatorName, creatorSlug, creatorAvatarUrl, actionText: `updated tier: ${tierName}`, link: creatorSlug ? `/creator/${creatorSlug}#profile-tiers` : undefined, linkLabel: 'View tiers' };
    }
    case 'tier_removed': {
      const tierName = (d.tier_name as string) ?? 'a tier';
      return { creatorName, creatorSlug, creatorAvatarUrl, actionText: `removed tier: ${tierName}`, link: creatorSlug ? `/creator/${creatorSlug}` : undefined, linkLabel: 'View creator' };
    }
    default:
      return { creatorName, creatorSlug: undefined, creatorAvatarUrl: null, actionText: 'Notification' };
  }
}

export default function NotificationsPage() {
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [notifications, setNotifications] = useState<NotificationItem[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [page, setPage] = useState(1);
  const [markingId, setMarkingId] = useState<number | null>(null);

  useEffect(() => {
    if (!authLoading && !user) {
      navigate('/login', { state: { from: '/notifications' }, replace: true });
      return;
    }
    if (!user) return;

    setLoading(true);
    setError(null);
    notificationsApi
      .list({ page, per_page: 20 })
      .then((res) => {
        const typed = res as PaginatedResponse<NotificationItem>;
        const data = typed?.data;
        const list = Array.isArray(data) ? data : [];
        setNotifications(list.filter((n): n is NotificationItem => n != null && typeof n.id === 'number'));
        setMeta(typed?.meta ?? null);
      })
      .catch((e) => {
        setError(e instanceof Error ? e.message : 'Failed to load notifications');
        setNotifications([]);
      })
      .finally(() => setLoading(false));
  }, [user, authLoading, navigate, page]);

  async function handleMarkRead(n: NotificationItem) {
    if (n.read_at) return;
    setMarkingId(n.id);
    try {
      await notificationsApi.markRead(n.id);
      setNotifications((prev) =>
        prev.map((item) => (item.id === n.id ? { ...item, read_at: new Date().toISOString() } : item))
      );
    } finally {
      setMarkingId(null);
    }
  }

  async function handleMarkAllRead() {
    try {
      await notificationsApi.markAllRead();
      setNotifications((prev) =>
        prev.map((item) => ({ ...item, read_at: item.read_at ?? new Date().toISOString() }))
      );
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to mark all as read');
    }
  }

  if (authLoading || !user) {
    return <LoadingPage />;
  }

  const list = Array.isArray(notifications) ? notifications : [];
  const hasUnread = list.some((n) => !n.read_at);

  return (
    <div className="page-center">
      <div className="card notifications-page-card" style={{ maxWidth: 640 }}>
        <div className="notifications-header">
          <h1 className="form-title">Notifications</h1>
          {hasUnread && (
            <button
              type="button"
              className="btn btn-secondary btn-sm"
              onClick={handleMarkAllRead}
            >
              Mark all as read
            </button>
          )}
        </div>
        {error && (
          <p className="form-error" role="alert" style={{ color: 'var(--color-error, #f87171)' }}>
            {error}
          </p>
        )}
        {loading ? (
          <p className="notifications-loading">Loading…</p>
        ) : list.length === 0 ? (
          <p className="notifications-empty">No notifications yet</p>
        ) : (
          <ul className="notifications-list">
            {list.map((n) => {
              let msg: NotificationMessage = { creatorName: 'Creator', creatorSlug: undefined, creatorAvatarUrl: null, actionText: 'Notification' };
              try {
                msg = notificationMessage(n);
              } catch {
                // ignore
              }
              const isRead = !!n.read_at;
              const profileUrl = msg.creatorSlug ? `/creator/${msg.creatorSlug}` : undefined;
              const initials = msg.creatorName.slice(0, 2).toUpperCase() || '?';
              return (
                <li
                  key={n.id}
                  className={`notifications-item ${isRead ? 'notifications-item-read' : ''}`}
                >
                  <div className="notifications-item-main">
                    <div className="notifications-item-top">
                      <div className="notifications-item-avatar" aria-hidden>
                        {msg.creatorAvatarUrl ? (
                          <img src={msg.creatorAvatarUrl} alt="" className="notifications-item-avatar-img" />
                        ) : (
                          initials
                        )}
                      </div>
                      <div className="notifications-item-head">
                        <div className="notifications-item-name-row">
                          {profileUrl ? (
                            <Link to={profileUrl} className="notifications-item-name">
                              {msg.creatorName}
                            </Link>
                          ) : (
                            <span className="notifications-item-name-plain">{msg.creatorName}</span>
                          )}
                        </div>
                        <p className="notifications-item-action">{msg.actionText}</p>
                        <div className="notifications-item-meta">
                          <span className="notifications-item-date">{formatDate(n.created_at)}</span>
                          {msg.link && msg.linkLabel && (
                            <Link to={msg.link} className="notifications-item-link">
                              {msg.linkLabel}
                            </Link>
                          )}
                        </div>
                      </div>
                    </div>
                  </div>
                  <button
                    type="button"
                    className="notifications-item-icon-btn"
                    disabled={isRead || markingId === n.id}
                    onClick={() => !isRead && handleMarkRead(n)}
                    aria-label={isRead ? 'Read' : 'Mark as read'}
                    title={isRead ? 'Read' : 'Mark as read'}
                  >
                    {isRead ? (
                      <span className="notifications-item-icon-read">
                        <EnvelopeIcon size={18} />
                        <CircleCheckIcon size={12} className="notifications-item-icon-check" />
                      </span>
                    ) : markingId === n.id ? (
                      <span className="notifications-item-icon-loading">…</span>
                    ) : (
                      <EnvelopeIcon size={18} />
                    )}
                  </button>
                </li>
              );
            })}
          </ul>
        )}
        {meta && meta.last_page > 1 && (
          <div className="notifications-pagination">
            <button
              type="button"
              className="btn btn-secondary btn-sm"
              disabled={page <= 1}
              onClick={() => setPage((p) => p - 1)}
            >
              Previous
            </button>
            <span className="notifications-page-info">
              Page {meta.current_page} of {meta.last_page}
            </span>
            <button
              type="button"
              className="btn btn-secondary btn-sm"
              disabled={page >= meta.last_page}
              onClick={() => setPage((p) => p + 1)}
            >
              Next
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
