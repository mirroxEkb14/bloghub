export const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8080';

export class ValidationError extends Error {
  readonly errors: Record<string, string[]>;

  constructor(message: string, errors: Record<string, string[]>) {
    super(message);
    this.name = 'ValidationError';
    this.errors = errors;
  }
}

export class ApiError extends Error {
  readonly status: number;
  readonly body: unknown;

  constructor(message: string, status: number, body: unknown) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.body = body;
  }
}

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
    const errorsObj = res.status === 422 && data.errors && typeof data.errors === 'object'
      ? (data.errors as Record<string, string[]>)
      : null;
    const msg = data.message ?? (errorsObj
      ? Object.values(errorsObj).flat().join(' ')
      : data.errors) ?? `Request failed: ${res.status}`;
    if (errorsObj) {
      throw new ValidationError(typeof msg === 'string' ? msg : 'Validation failed', errorsObj);
    }
    throw new ApiError(typeof msg === 'string' ? msg : JSON.stringify(msg), res.status, data);
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

export function unwrapData<T>(r: T | { data: T }): T {
  return r != null && typeof r === 'object' && 'data' in r && (r as { data: T }).data != null
    ? (r as { data: T }).data
    : (r as T);
}

export function normalizeUploadResponse(r: unknown): { path: string; url: string } {
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
