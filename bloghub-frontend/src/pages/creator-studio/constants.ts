import type { SocialKey } from './types';

export const POSTS_LOAD_PER_PAGE = 100;
export const ACCEPT_IMAGE = 'image/jpeg,image/png,image/webp';
export const COVER_IMAGE_RECOMMENDED = '1920x480 px';
export const AVATAR_IMAGE_RECOMMENDED = '256x256 px. Max 5 MB';
export const TIER_COVER_IMAGE_RECOMMENDED = '400x200 px';
export const POST_MEDIA_HINT =
  'Recommended: 1200x720 (16:9). Image/Gif: max 5 MB. Video: max 50 MB. Audio: max 5 MB';
export const ACCEPT_MEDIA =
  'image/jpeg,image/png,image/webp,image/gif,video/mp4,audio/mpeg,audio/mp3,audio/mp4,audio/mp4';
export const MAX_FILE_MB = 5;
export const MAX_FILE_BYTES = MAX_FILE_MB * 1024 * 1024;
export const CURRENCIES = ['USD', 'EUR', 'CZK'] as const;

export const PLACEHOLDER_BY_LEVEL: Record<
  1 | 2 | 3,
  { tier_name: string; tier_desc: string; price: number; tier_currency: 'USD' }
> = {
  1: {
    tier_name: 'Entered Apprentice',
    tier_desc: '• 1st Degree: The initiation stage',
    price: 1,
    tier_currency: 'USD',
  },
  2: {
    tier_name: 'Fellow Craft',
    tier_desc: '• 2nd Degree: Education, science, and the application of knowledge',
    price: 2,
    tier_currency: 'USD',
  },
  3: {
    tier_name: 'Master',
    tier_desc: '• 3rd Degree: Completion of the foundational journey',
    price: 3,
    tier_currency: 'USD',
  },
};

export const SOCIAL_LINKS_CONFIG: { key: SocialKey; label: string }[] = [
  { key: 'telegram_url', label: 'Telegram' },
  { key: 'instagram_url', label: 'Instagram' },
  { key: 'facebook_url', label: 'Facebook' },
  { key: 'youtube_url', label: 'YouTube' },
  { key: 'twitch_url', label: 'Twitch' },
  { key: 'website_url', label: 'Website' },
];

export const SOCIAL_URL_VALIDATION: Record<
  SocialKey,
  { pattern: RegExp; message: string }
> = {
  telegram_url: {
    pattern: /^https:\/\/t\.me\/.+/,
    message: 'Social link must start with https://t.me/',
  },
  instagram_url: {
    pattern: /^https:\/\/(www\.)?instagram\.com\/.+/,
    message:
      'Social link must start with https://instagram.com/ or https://www.instagram.com/',
  },
  facebook_url: {
    pattern: /^https:\/\/(www\.)?facebook\.com\/.+/,
    message:
      'Social link must start with https://facebook.com/ or https://www.facebook.com/',
  },
  youtube_url: {
    pattern: /^https:\/\/(www\.)?youtube\.com\/.+/,
    message:
      'Social link must start with https://youtube.com/ or https://www.youtube.com/',
  },
  twitch_url: {
    pattern: /^https:\/\/(www\.)?twitch\.tv\/.+/,
    message:
      'Social link must start with https://twitch.tv/ or https://www.twitch.tv/',
  },
  website_url: {
    pattern: /^https:\/\/.+/,
    message: 'Website link must start with https://',
  },
};
