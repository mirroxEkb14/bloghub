const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8080';

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
