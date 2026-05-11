import { api } from './http';
import type { Comment } from './types';

export const commentsApi = {
  list(creatorSlug: string, postSlug: string) {
    return api<{ data: Comment[] } | Comment[]>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/posts/${encodeURIComponent(postSlug)}/comments`
    ).then((r) => (Array.isArray(r) ? r : (r && typeof r === 'object' && 'data' in r ? (r as { data: Comment[] }).data : [])) ?? []);
  },

  create(creatorSlug: string, postSlug: string, body: { content_text: string }) {
    return api<{ data: Comment } | Comment>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/posts/${encodeURIComponent(postSlug)}/comments`,
      { method: 'POST', body: JSON.stringify(body) }
    ).then((r) => (r && typeof r === 'object' && 'data' in r ? (r as { data: Comment }).data : r) as Comment);
  },
};
