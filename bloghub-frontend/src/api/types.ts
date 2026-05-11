export type User = {
  id: number;
  name: string;
  username: string;
  email: string;
  email_verified_at: string | null;
  avatar_url?: string | null;
  phone?: string | null;
  terms_accepted_at: string | null;
  privacy_accepted_at: string | null;
  created_at: string;
  updated_at: string;
  creator_profile?: { id: number; user_id: number; slug: string } | null;
};

export type AuthResponse = {
  user: User;
  token: string;
  token_type: string;
};

export type Tag = {
  id: number;
  slug: string;
  name: string;
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
  telegram_url?: string | null;
  instagram_url?: string | null;
  facebook_url?: string | null;
  youtube_url?: string | null;
  twitch_url?: string | null;
  website_url?: string | null;
  user?: CreatorProfileUser;
  tags?: Tag[];
  posts_count?: number;
  followers_count?: number;
  subscribers_count?: number;
  subscriptions_count?: number;
  last_post_at?: string | null;
  is_following?: boolean;
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
  excerpt: string | null;
  media_url: string | null;
  media_type: 'Image' | 'Gif' | 'Audio' | 'Video' | null;
  required_tier?: PostRequiredTier | null;
  user_has_access?: boolean;
  views_count?: number;
  user_has_viewed?: boolean;
  comments_count?: number;
  likes_count?: number;
  user_has_liked?: boolean;
  bookmarks_count?: number;
  created_at?: string;
  updated_at?: string;
  creator_profile?: { slug: string; display_name: string; profile_avatar_url?: string | null } | null;
};

export type CommentUser = {
  id: number;
  name: string;
  username: string;
  avatar_url?: string | null;
  creator_profile?: { slug: string; profile_avatar_url?: string | null } | null;
};

export type Comment = {
  id: number;
  content_text: string;
  created_at: string;
  updated_at: string;
  user: CommentUser;
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

export type PostsByCreatorParams = {
  per_page?: number;
  page?: number;
};

export type HomeFeedParams = {
  per_page?: number;
  page?: number;
  q?: string;
};

export type PublicFeedParams = {
  per_page?: number;
  page?: number;
  q?: string;
};

export type TierFeedParams = {
  per_page?: number;
  page?: number;
  q?: string;
};

export type PostCreatePayload = {
  slug: string;
  title: string;
  content_text: string;
  excerpt?: string | null;
  media_url?: string | null;
  media_type?: string | null;
  required_tier_id?: number | null;
};

export type PostUpdatePayload = Partial<{
  slug: string;
  title: string;
  content_text: string;
  excerpt: string | null;
  media_url: string | null;
  media_type: string | null;
  required_tier_id: number | null;
}>;

export type PostMediaUploadResponse = {
  path: string;
  url: string;
  media_type: string;
};

export type TierCreatePayload = {
  tier_name: string;
  tier_desc: string;
  price: number;
  tier_currency: string;
  tier_cover_path?: string | null;
};

export type TierUpdatePayload = Partial<TierCreatePayload>;

export type SubscriptionCreator = {
  id: number;
  slug: string;
  display_name: string;
  profile_avatar_url: string | null;
  followers_count?: number | null;
  last_post_at?: string | null;
};

export type SubscriptionWithTier = {
  id: number;
  user_id: number;
  tier_id: number;
  start_date: string | null;
  end_date: string | null;
  sub_status: string;
  created_at: string | null;
  updated_at: string | null;
  tier: Tier;
  creator: SubscriptionCreator;
  card_last4?: string | null;
};

export type SubscriptionStatusResponse = {
  subscribed: boolean;
  active_subscription: SubscriptionWithTier | null;
};

export type CheckoutSessionResponse =
  | { type: 'free'; subscription: SubscriptionWithTier }
  | { type: 'checkout'; checkout_url: string }
  | { type: 'already_subscribed'; message?: string }
  | {
      type: 'upgrade_confirm';
      message?: string;
      current_subscription: { tier_name: string | null; end_date: string | null };
      new_tier_name: string;
    };

export type PaymentSubscriptionContext = {
  id: number;
  tier_name: string | null;
  creator: { slug: string; display_name: string } | null;
};

export type PaymentForUser = {
  id: number;
  amount: number;
  currency: string | null;
  checkout_date: string | null;
  card_last4: string | null;
  payment_status: string;
  subscription: PaymentSubscriptionContext;
};

export type InsightsPeriodKey = 'overall' | 'year' | '6m' | '3m' | '1m';

export type InsightsResponse = {
  members: { total: number; paid: number; free: number };
  earnings: Record<InsightsPeriodKey, { amount: number }>;
  engagement: Record<
    InsightsPeriodKey,
    { post_views: number; likes: number; comments: number }
  >;
  growth_30d: { new_paid: number; cancellations: number };
};

export type NotificationItem = {
  id: number;
  type: string;
  data: Record<string, unknown>;
  read_at: string | null;
  created_at: string | null;
};
