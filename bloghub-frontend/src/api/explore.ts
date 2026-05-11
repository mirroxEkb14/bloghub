import { api } from './http';
import type { CreatorProfile, Post } from './types';

export const exploreApi = {
  getPopularCreators() {
    return api<{ data: CreatorProfile[] }>('/api/explore/popular-creators').then((r) => ((r && typeof r === 'object' && 'data' in r ? (r as { data: CreatorProfile[] }).data : undefined) ?? []) as CreatorProfile[]);
  },
  getTrendingPosts() {
    return api<{ data: Post[] } | Post[]>('/api/explore/trending-posts').then((r) => (Array.isArray(r) ? r : (r && typeof r === 'object' && 'data' in r ? (r as { data: Post[] }).data : [])) ?? []);
  },
};
