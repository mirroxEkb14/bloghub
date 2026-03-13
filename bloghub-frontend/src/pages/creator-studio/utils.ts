import type { DraftPost, PlaceholderTier, SocialKey } from './types';
import { PLACEHOLDER_BY_LEVEL, SOCIAL_URL_VALIDATION } from './constants';

export function slugify(text: string): string {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '') || 'post';
}

export function getDefaultPlaceholderForLevel(
  level: 1 | 2 | 3
): PlaceholderTier {
  return { ...PLACEHOLDER_BY_LEVEL[level] };
}

export function createEmptyPlaceholderPost(): DraftPost {
  return {
    id: -Date.now(),
    slug: 'the-iniciation',
    title: 'The Iniciation',
    content_text: "Burial as a symbol of rebirth and a new beginning.",
    excerpt: "Become a brother upon the ritual's completion...",
    media_url: null,
    media_type: null,
    required_tier: null,
    _isNew: true,
  } as unknown as DraftPost;
}

export function validateSocialUrl(
  key: SocialKey,
  url: string
): string | null {
  const trimmed = url.trim();
  if (!trimmed) return null;
  try {
    new URL(trimmed);
  } catch {
    return 'Please enter a valid URL';
  }
  const { pattern, message } = SOCIAL_URL_VALIDATION[key];
  return pattern.test(trimmed) ? null : message;
}
