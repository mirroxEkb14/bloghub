import { api, uploadApi } from './http';
import type { AuthResponse, User } from './types';

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

  resendVerificationEmail() {
    return api<{ message: string }>('/api/email/resend', { method: 'POST' });
  },

  user() {
    return api<{ user: User }>('/api/user');
  },

  updateProfile(body: { name: string; username: string; email: string; phone?: string | null }) {
    return api<{ user: User }>('/api/user', {
      method: 'PATCH',
      body: JSON.stringify(body),
    });
  },

  uploadUserAvatar(file: File) {
    return uploadApi<{ path: string; url: string }>('/api/user/upload-avatar', 'avatar', file);
  },

  acceptTermsAndPrivacy(body: { terms_accepted: true; privacy_accepted: true }) {
    return api<{ user: User }>('/api/user/accept-terms-privacy', {
      method: 'PATCH',
      body: JSON.stringify(body),
    });
  },
};
