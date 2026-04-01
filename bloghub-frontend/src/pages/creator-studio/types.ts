import type { Post, Tier } from '../../api/client';

export type SocialKey =
  | 'telegram_url'
  | 'instagram_url'
  | 'facebook_url'
  | 'youtube_url'
  | 'twitch_url'
  | 'website_url';

export type ProfileDraft = {
  display_name: string;
  slug: string;
  about: string;
  tag_ids: number[];
  profile_avatar_path: string | null | undefined;
  profile_cover_path: string | null | undefined;
  avatar_preview_url: string | null;
  cover_preview_url: string | null;
  telegram_url: string;
  instagram_url: string;
  facebook_url: string;
  youtube_url: string;
  twitch_url: string;
  website_url: string;
};

export type DraftTier = Tier & {
  tier_cover_preview_url?: string | null;
  _isNew?: boolean;
};

export type DraftPost = Post & {
  _isNew?: boolean;
  media_preview_url?: string | null;
};

export type PlaceholderTier = {
  tier_name: string;
  tier_desc: string;
  price: number;
  tier_currency: 'USD' | 'EUR' | 'CZK';
  tier_cover_path?: string;
  tier_cover_preview_url?: string;
};
