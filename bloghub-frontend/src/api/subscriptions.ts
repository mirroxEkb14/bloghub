import { api, unwrapData } from './http';
import type {
  CheckoutSessionResponse,
  SubscriptionStatusResponse,
  SubscriptionWithTier,
} from './types';

export const subscriptionsApi = {
  list() {
    return api<{ data: SubscriptionWithTier[] } | SubscriptionWithTier[]>(
      '/api/me/subscriptions'
    ).then((r) => (Array.isArray(r) ? r : (r as { data: SubscriptionWithTier[] }).data ?? []));
  },

  subscribe(tierId: number) {
    return api<SubscriptionWithTier | { data: SubscriptionWithTier }>(
      '/api/subscriptions',
      { method: 'POST', body: JSON.stringify({ tier_id: tierId }) }
    ).then(unwrapData);
  },

  createCheckoutSession(tierId: number, options?: { confirmUpgrade?: boolean }) {
    const body: { tier_id: number; confirm_upgrade?: boolean } = { tier_id: tierId };
    if (options?.confirmUpgrade === true) body.confirm_upgrade = true;
    return api<CheckoutSessionResponse>(
      '/api/subscriptions/create-checkout-session',
      { method: 'POST', body: JSON.stringify(body) }
    );
  },

  confirmCheckout(sessionId: string) {
    return api<{ status: 'active'; subscription: SubscriptionWithTier } | { status: string; message: string }>(
      '/api/subscriptions/confirm-checkout',
      { method: 'POST', body: JSON.stringify({ session_id: sessionId }) }
    );
  },

  getStatusByCreator(creatorSlug: string) {
    return api<SubscriptionStatusResponse>(
      `/api/creator-profiles/${encodeURIComponent(creatorSlug)}/subscription-status`
    );
  },

  cancel(subscriptionId: number, options?: { endNow?: boolean }) {
    const body = options?.endNow !== undefined ? { end_now: options.endNow } : {};
    return api<{ message: string; subscription: SubscriptionWithTier }>(
      `/api/subscriptions/${subscriptionId}/cancel`,
      { method: 'PATCH', body: Object.keys(body).length ? JSON.stringify(body) : undefined }
    );
  },
};
