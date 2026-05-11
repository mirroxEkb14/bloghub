import { api, normalizeUploadResponse, unwrapData, uploadApi } from './http';
import type { CreatorProfile, CreatorProfilesParams, PaginatedResponse } from './types';

export const creatorProfilesApi = {
  list(params: CreatorProfilesParams = {}) {
    const sp = new URLSearchParams();
    if (params.tag != null) sp.set('tag', String(params.tag));
    if (params.search) sp.set('search', params.search);
    if (params.per_page != null) sp.set('per_page', String(params.per_page));
    if (params.page != null) sp.set('page', String(params.page));
    const q = sp.toString();
    return api<PaginatedResponse<CreatorProfile>>(
      `/api/creator-profiles${q ? `?${q}` : ''}`
    );
  },

  getBySlug(slug: string) {
    return api<CreatorProfile | { data: CreatorProfile }>(
      `/api/creator-profiles/${encodeURIComponent(slug)}`
    ).then(unwrapData);
  },

  follow(slug: string) {
    return api<{ message: string }>(`/api/creator-profiles/${encodeURIComponent(slug)}/follow`, {
      method: 'POST',
    });
  },

  unfollow(slug: string) {
    return api<{ message: string }>(`/api/creator-profiles/${encodeURIComponent(slug)}/follow`, {
      method: 'DELETE',
    });
  },

  me() {
    return api<CreatorProfile | { data: CreatorProfile }>('/api/me/creator-profile').then(unwrapData);
  },

  getFollowing() {
    return api<{ data: { creator_profile: CreatorProfile; followed_at: string | null }[] }>(
      '/api/me/following'
    ).then((r) => (r && typeof r === 'object' && 'data' in r ? (r as { data: { creator_profile: CreatorProfile; followed_at: string | null }[] }).data : []) ?? []);
  },

  deleteMe() {
    return api<{ message: string }>('/api/me/creator-profile', { method: 'DELETE' });
  },

  updateMe(body: {
    slug?: string;
    display_name?: string;
    about?: string | null;
    profile_avatar_path?: string | null;
    profile_cover_path?: string | null;
    telegram_url?: string | null;
    instagram_url?: string | null;
    facebook_url?: string | null;
    youtube_url?: string | null;
    twitch_url?: string | null;
    website_url?: string | null;
    tag_ids?: number[];
  }) {
    return api<CreatorProfile | { data: CreatorProfile }>('/api/me/creator-profile', {
      method: 'PUT',
      body: JSON.stringify(body),
    }).then(unwrapData);
  },

  create(body: {
    display_name: string;
    slug?: string;
    about?: string | null;
    profile_avatar_path?: string | null;
    profile_cover_path?: string | null;
    telegram_url?: string | null;
    instagram_url?: string | null;
    facebook_url?: string | null;
    youtube_url?: string | null;
    twitch_url?: string | null;
    website_url?: string | null;
    tag_ids?: number[];
  }) {
    return api<CreatorProfile | { data: CreatorProfile }>('/api/creator-profiles', {
      method: 'POST',
      body: JSON.stringify(body),
    }).then(unwrapData);
  },

  update(id: number, body: {
    slug?: string;
    display_name?: string;
    about?: string | null;
    profile_avatar_path?: string | null;
    profile_cover_path?: string | null;
    telegram_url?: string | null;
    instagram_url?: string | null;
    facebook_url?: string | null;
    youtube_url?: string | null;
    twitch_url?: string | null;
    website_url?: string | null;
    tag_ids?: number[];
  }) {
    return api<CreatorProfile | { data: CreatorProfile }>(`/api/creator-profiles/${id}`, {
      method: 'PUT',
      body: JSON.stringify(body),
    }).then(unwrapData);
  },

  uploadAvatar(file: File) {
    return uploadApi<{ path: string; url: string | null } | { data: { path: string; url: string | null } }>(
      '/api/creator-profiles/upload-avatar',
      'avatar',
      file
    ).then(normalizeUploadResponse);
  },

  uploadCover(file: File) {
    return uploadApi<{ path: string; url: string | null } | { data: { path: string; url: string | null } }>(
      '/api/creator-profiles/upload-cover',
      'cover',
      file
    ).then(normalizeUploadResponse);
  },
};
