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

const DEFAULT_PLACEHOLDER_POST = {
  title: 'The Initiation',
  content_text: "Burial as a symbol of rebirth and a new beginning.",
  excerpt: "Become a brother upon the ritual's completion...",
};

export function createEmptyPlaceholderPost(): DraftPost {
  return {
    id: -Date.now(),
    slug: 'the-initiation',
    title: DEFAULT_PLACEHOLDER_POST.title,
    content_text: DEFAULT_PLACEHOLDER_POST.content_text,
    excerpt: DEFAULT_PLACEHOLDER_POST.excerpt,
    media_url: null,
    media_type: null,
    required_tier: null,
    _isNew: true,
  } as unknown as DraftPost;
}

export function isDefaultPlaceholderPost(p: DraftPost): boolean {
  const t = (p.title ?? '').trim();
  const c = (p.content_text ?? '').trim();
  const e = (p.excerpt ?? '').trim();
  return (
    t === DEFAULT_PLACEHOLDER_POST.title &&
    c === DEFAULT_PLACEHOLDER_POST.content_text &&
    e === DEFAULT_PLACEHOLDER_POST.excerpt
  );
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
