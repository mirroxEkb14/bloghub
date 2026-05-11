import { api } from './http';
import type { HomeFeedParams, PaginatedResponse, Post, PublicFeedParams, TierFeedParams } from './types';

function buildFeedQuery(params: { per_page?: number; page?: number; q?: string }): string {
  const sp = new URLSearchParams();
  if (params.per_page != null) sp.set('per_page', String(params.per_page));
  if (params.page != null) sp.set('page', String(params.page));
  if (params.q != null && params.q.trim() !== '') sp.set('q', params.q.trim());
  const queryString = sp.toString();
  return queryString ? `?${queryString}` : '';
}

export const feedApi = {
  getHomeFeed(params: HomeFeedParams = {}) {
    return api<PaginatedResponse<Post>>(`/api/me/feed${buildFeedQuery(params)}`);
  },

  getPublicFeed(params: PublicFeedParams = {}) {
    return api<PaginatedResponse<Post>>(`/api/me/feed/public${buildFeedQuery(params)}`);
  },

  getTierFeed(params: TierFeedParams = {}) {
    return api<PaginatedResponse<Post>>(`/api/me/feed/tier${buildFeedQuery(params)}`);
  },
};
