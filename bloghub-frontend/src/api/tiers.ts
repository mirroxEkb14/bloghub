import { api, normalizeUploadResponse, uploadApi } from './http';
import type { Tier, TierCreatePayload, TierUpdatePayload } from './types';

export const tiersApi = {
  listByCreator(creatorSlug: string) {
    return api<{ data: Tier[] } | Tier[]>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/tiers`
    ).then((r) => (Array.isArray(r) ? r : (r as { data: Tier[] }).data ?? []));
  },

  listMine() {
    return api<{ data: Tier[] } | Tier[]>(
      '/api/me/creator-profile/tiers'
    ).then((r) => (Array.isArray(r) ? r : (r as { data: Tier[] }).data ?? []));
  },

  uploadCover(file: File) {
    return uploadApi<{ path: string; url: string | null } | { data: { path: string; url: string | null } }>(
      '/api/me/creator-profile/tiers/upload-cover',
      'cover',
      file
    ).then(normalizeUploadResponse);
  },

  create(payload: TierCreatePayload) {
    return api<Tier | { data: Tier }>(
      '/api/me/creator-profile/tiers',
      { method: 'POST', body: JSON.stringify(payload) }
    ).then((r) => (r != null && typeof r === 'object' && 'data' in r ? (r as { data: Tier }).data : r) as Tier);
  },

  update(tierId: number, payload: TierUpdatePayload) {
    return api<Tier | { data: Tier }>(
      `/api/me/creator-profile/tiers/${tierId}`,
      { method: 'PUT', body: JSON.stringify(payload) }
    ).then((r) => (r != null && typeof r === 'object' && 'data' in r ? (r as { data: Tier }).data : r) as Tier);
  },

  delete(tierId: number) {
    return api<void>(`/api/me/creator-profile/tiers/${tierId}`, { method: 'DELETE' });
  },
};
