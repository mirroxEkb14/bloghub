import { api } from './http';
import type { NotificationItem, PaginatedResponse } from './types';

export const notificationsApi = {
  list(params?: { page?: number; per_page?: number }) {
    const sp = new URLSearchParams();
    if (params?.page != null) sp.set('page', String(params.page));
    if (params?.per_page != null) sp.set('per_page', String(params.per_page));
    const q = sp.toString();
    return api<PaginatedResponse<NotificationItem>>(
      `/api/me/notifications${q ? `?${q}` : ''}`
    );
  },

  unreadCount() {
    return api<{ count: number }>('/api/me/notifications/unread-count');
  },

  markRead(id: number) {
    return api<NotificationItem>(`/api/me/notifications/${id}/read`, { method: 'PATCH' });
  },

  markAllRead() {
    return api<{ message: string }>('/api/me/notifications/read', { method: 'PATCH' });
  },
};
