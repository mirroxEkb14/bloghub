import { api, unwrapData, uploadApi } from './http';
import type {
  PaginatedResponse,
  Post,
  PostCreatePayload,
  PostMediaUploadResponse,
  PostUpdatePayload,
  PostsByCreatorParams,
} from './types';

export const postsApi = {
  uploadMedia(file: File) {
    return uploadApi<PostMediaUploadResponse | { data: PostMediaUploadResponse }>(
      '/api/me/creator-profile/posts/upload-media',
      'media',
      file
    ).then((r) => {
      const raw = r != null && typeof r === 'object' && 'data' in r ? (r as { data: PostMediaUploadResponse }).data : r;
      return raw as PostMediaUploadResponse;
    });
  },

  create(payload: PostCreatePayload) {
    return api<Post | { data: Post }>(
      '/api/me/creator-profile/posts',
      { method: 'POST', body: JSON.stringify(payload) }
    ).then((r) => (r != null && typeof r === 'object' && 'data' in r ? (r as { data: Post }).data : r) as Post);
  },

  update(postSlug: string, payload: PostUpdatePayload) {
    return api<Post | { data: Post }>(
      `/api/me/creator-profile/posts/${encodeURIComponent(postSlug)}`,
      { method: 'PUT', body: JSON.stringify(payload) }
    ).then((r) => (r != null && typeof r === 'object' && 'data' in r ? (r as { data: Post }).data : r) as Post);
  },

  listByCreator(creatorSlug: string, params: PostsByCreatorParams = {}) {
    const sp = new URLSearchParams();
    if (params.per_page != null) sp.set('per_page', String(params.per_page));
    if (params.page != null) sp.set('page', String(params.page));
    const q = sp.toString();
    return api<PaginatedResponse<Post>>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/posts${q ? `?${q}` : ''}`
    );
  },

  getBySlug(creatorSlug: string, postSlug: string) {
    return api<Post | { data: Post }>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/posts/${encodeURIComponent(postSlug)}`
    ).then(unwrapData);
  },

  recordView(creatorSlug: string, postSlug: string) {
    return api<unknown>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/posts/${encodeURIComponent(postSlug)}/view`,
      { method: 'POST' }
    );
  },

  like(creatorSlug: string, postSlug: string) {
    return api<unknown>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/posts/${encodeURIComponent(postSlug)}/like`,
      { method: 'POST' }
    );
  },

  unlike(creatorSlug: string, postSlug: string) {
    return api<unknown>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/posts/${encodeURIComponent(postSlug)}/like`,
      { method: 'DELETE' }
    );
  },

  deleteMine(postSlug: string) {
    return api<unknown>(
      `/api/me/creator-profile/posts/${encodeURIComponent(postSlug)}`,
      { method: 'DELETE' }
    );
  },
};
