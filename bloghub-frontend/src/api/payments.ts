import { api } from './http';
import type { PaymentForUser } from './types';

export const paymentsApi = {
  list() {
    return api<{ data: PaymentForUser[] } | PaymentForUser[]>(
      '/api/me/payments'
    ).then((r) => (Array.isArray(r) ? r : (r as { data: PaymentForUser[] }).data ?? []));
  },
};
