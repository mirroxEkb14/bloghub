export const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8080';

function getToken(): string | null {
  return localStorage.getItem('auth_token');
}

export async function api<T>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const token = getToken();
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(options.headers as Record<string, string>),
  };
  if (token) {
    (headers as Record<string, string>)['Authorization'] = `Bearer ${token}`;
  }

  const res = await fetch(`${API_BASE}${path}`, { ...options, headers });
  const data = await res.json().catch(() => ({}));

  if (!res.ok) {
    const msg = data.message ?? (typeof data.errors === 'object'
      ? Object.values(data.errors as Record<string, string[]>).flat().join(' ')
      : data.errors) ?? `Request failed: ${res.status}`;
    throw new Error(typeof msg === 'string' ? msg : JSON.stringify(msg));
  }
  return data as T;
}

export async function uploadApi<T>(
  path: string,
  formKey: string,
  file: File,
  method: 'POST' | 'PUT' = 'POST'
): Promise<T> {
  const token = getToken();
  const form = new FormData();
  form.append(formKey, file);
  const headers: HeadersInit = {
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  };

  const res = await fetch(`${API_BASE}${path}`, {
    method,
    headers,
    body: form,
  });
  const data = await res.json().catch(() => ({}));

  if (!res.ok) {
    const msg = data.message ?? (typeof data.errors === 'object'
      ? Object.values(data.errors as Record<string, string[]>).flat().join(' ')
      : data.errors) ?? `Upload failed: ${res.status}`;
    throw new Error(typeof msg === 'string' ? msg : JSON.stringify(msg));
  }
  return data as T;
}

export type User = {
  id: number;
  name: string;
  username: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
};

export type AuthResponse = {
  user: User;
  token: string;
  token_type: string;
};

export const authApi = {
  register(body: {
    name: string;
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
    phone?: string;
    terms_accepted?: boolean;
    privacy_accepted?: boolean;
  }) {
    return api<AuthResponse>('/api/register', {
      method: 'POST',
      body: JSON.stringify(body),
    });
  },

  login(body: { email: string; password: string }) {
    return api<AuthResponse>('/api/login', {
      method: 'POST',
      body: JSON.stringify(body),
    });
  },

  logout() {
    return api<unknown>('/api/logout', { method: 'POST' });
  },

  user() {
    return api<{ user: User }>('/api/user');
  },
};

export type Tag = {
  id: number;
  slug: string;
  name: string;
};

export const tagsApi = {
  list() {
    return api<{ data: Tag[] }>('/api/tags').then((r) =>
      Array.isArray(r) ? r : (r as { data: Tag[] }).data ?? []
    );
  },
};

export type CreatorProfileUser = {
  id: number;
  name: string;
  username: string;
};

export type CreatorProfile = {
  id: number;
  slug: string;
  display_name: string;
  about: string | null;
  profile_avatar_url: string | null;
  profile_cover_url: string | null;
  user?: CreatorProfileUser;
  tags?: Tag[];
  posts_count?: number;
  created_at?: string;
  updated_at?: string;
};

export type PostRequiredTier = {
  id: number;
  level: number;
  tier_name: string;
};

export type Post = {
  id: number;
  slug: string;
  title: string;
  content_text: string | null;
  media_url: string | null;
  media_type: 'Image' | 'Audio' | 'Video' | null;
  required_tier?: PostRequiredTier | null;
  created_at?: string;
  updated_at?: string;
};

export type Tier = {
  id: number;
  level: number;
  tier_name: string;
  tier_desc: string | null;
  price: number;
  tier_currency: string | null;
  tier_cover_url: string | null;
  created_at?: string;
  updated_at?: string;
};

export type CreatorProfilesParams = {
  tag?: string | number;
  search?: string;
  per_page?: number;
  page?: number;
};

export type PaginatedMeta = {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
};

export type PaginatedResponse<T> = {
  data: T[];
  meta: PaginatedMeta;
  links: { first: string; last: string; prev: string | null; next: string | null };
};

function unwrapData<T>(r: T | { data: T }): T {
  return r != null && typeof r === 'object' && 'data' in r && (r as { data: T }).data != null
    ? (r as { data: T }).data
    : (r as T);
}

function normalizeUploadResponse(r: unknown): { path: string; url: string } {
  const raw = unwrapData(r as { path?: string; url?: string; data?: { path?: string; url?: string } });
  const obj = raw != null && typeof raw === 'object' ? (raw as Record<string, unknown>) : {};
  const path = (typeof obj.path === 'string' ? obj.path : typeof obj.file_path === 'string' ? obj.file_path : '') || '';
  if (!path.trim()) {
    throw new Error('Upload failed: server did not return a file path. Check Network → upload response body');
  }
  const url = typeof obj.url === 'string' && obj.url.startsWith('http') ? obj.url : null;
  const previewUrl = url ?? `${API_BASE.replace(/\/$/, '')}/storage/${path.replace(/^\//, '')}`;
  return { path, url: previewUrl };
}

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

  me() {
    return api<CreatorProfile | { data: CreatorProfile }>('/api/me/creator-profile').then(unwrapData);
  },

  create(body: {
    display_name: string;
    slug?: string;
    about?: string | null;
    profile_avatar_path?: string | null;
    profile_cover_path?: string | null;
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

export type PostsByCreatorParams = {
  per_page?: number;
  page?: number;
};

export const postsApi = {
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
};

export const tiersApi = {
  listByCreator(creatorSlug: string) {
    return api<{ data: Tier[] } | Tier[]>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/tiers`
    ).then((r) => (Array.isArray(r) ? r : (r as { data: Tier[] }).data ?? []));
  },
};
