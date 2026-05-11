import { api } from './http';
import type { InsightsResponse } from './types';

export const insightsApi = {
  get() {
    return api<InsightsResponse>('/api/me/insights');
  },
};
