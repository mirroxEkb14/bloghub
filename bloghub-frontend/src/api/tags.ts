import { api } from './http';
import type { Tag } from './types';

export const tagsApi = {
  list() {
    return api<{ data: Tag[] }>('/api/tags').then((r) =>
      Array.isArray(r) ? r : (r as { data: Tag[] }).data ?? []
    );
  },
};
