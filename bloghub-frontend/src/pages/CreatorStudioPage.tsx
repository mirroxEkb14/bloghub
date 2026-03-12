import { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import {
  creatorProfilesApi,
  postsApi,
  tagsApi,
  tiersApi,
  ValidationError,
  type Tag,
  type Tier,
} from '../api/client';
import type { PostCreatePayload, PostUpdatePayload } from '../api/client';
import type { TierUpdatePayload } from '../api/client';
import LoadingPage from '../components/LoadingPage';
import { CameraIcon, EditIcon, PlusIcon, Trash2Icon } from '../components/icons';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';
import {
  ACCEPT_IMAGE,
  ACCEPT_MEDIA,
  AVATAR_IMAGE_RECOMMENDED,
  COVER_IMAGE_RECOMMENDED,
  CURRENCIES,
  MAX_FILE_BYTES,
  POST_MEDIA_HINT,
  POSTS_LOAD_PER_PAGE,
  SOCIAL_LINKS_CONFIG,
  TIER_COVER_IMAGE_RECOMMENDED,
} from './creator-studio/constants';
import { PostCardStudio } from './creator-studio/PostCardStudio';
import { SocialIcon, SocialLinkModal } from './creator-studio/SocialLinkModal';
import { TagsModal } from './creator-studio/TagsModal';
import { TierFormModal } from './creator-studio/TierFormModal';
import type { DraftPost, DraftTier, PlaceholderTier, ProfileDraft, SocialKey } from './creator-studio/types';
import {
  createEmptyPlaceholderPost,
  getDefaultPlaceholderForLevel,
  slugify,
} from './creator-studio/utils';

export default function CreatorStudioPage() {
  const { user, loading: authLoading, refreshUser } = useAuth();
  const { showToast } = useToast();
  const navigate = useNavigate();
  const location = useLocation();
  const routeIsNew = location.pathname === '/creator/new';
  const [mode, setMode] = useState<'create' | 'edit' | null>(null);
  const [profileId, setProfileId] = useState<number | null>(null);
  const [profileSlug, setProfileSlug] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [profileDraft, setProfileDraft] = useState<ProfileDraft | null>(null);
  const [tiersDraft, setTiersDraft] = useState<DraftTier[]>([]);
  const [tierIdsToDelete, setTierIdsToDelete] = useState<Set<number>>(new Set());
  const [postsDraft, setPostsDraft] = useState<DraftPost[]>([]);
  const [postSlugsToDelete, setPostSlugsToDelete] = useState<Set<string>>(new Set());
  const [tags, setTags] = useState<Tag[]>([]);
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState<string | null>(null);
  const [slugHighlightError, setSlugHighlightError] = useState(false);
  const [showProfilePanel, _setShowProfilePanel] = useState(false);
  const [editingDisplayName, setEditingDisplayName] = useState(false);
  const [editingAbout, setEditingAbout] = useState(false);
  const [tierModal, setTierModal] = useState<{ tier: DraftTier | null; isNew: boolean } | null>(null);
  const [socialModalKey, setSocialModalKey] = useState<SocialKey | null>(null);
  const [serverFollowersCount, setServerFollowersCount] = useState<number | null>(null);
  const [serverSubscribersCount, setServerSubscribersCount] = useState<number | null>(null);
  const [showTagsModal, setShowTagsModal] = useState(false);
  const [uploadingTierCoverId, setUploadingTierCoverId] = useState<number | null>(null);
  const [tierIdForCoverUpload, setTierIdForCoverUpload] = useState<number | null>(null);
  const [tierIdConfirmRemove, setTierIdConfirmRemove] = useState<number | null>(null);
  const [placeholderConfirmRemoveLevel, setPlaceholderConfirmRemoveLevel] = useState<number | null>(null);
  const [postToConfirmRemove, setPostToConfirmRemove] = useState<{ post: DraftPost; isPlaceholder: boolean; placeholderIndex?: number } | null>(null);
  const [placeholderPosts, setPlaceholderPosts] = useState<DraftPost[]>(() => [createEmptyPlaceholderPost()]);
  const [includedPlaceholderPostIndices, setIncludedPlaceholderPostIndices] = useState<number[]>([]);
  const [postMediaUploadTarget, setPostMediaUploadTarget] = useState<{ slug: string; id: number } | { placeholderIndex: number } | null>(null);
  const [uploadingPostMedia, setUploadingPostMedia] = useState(false);
  const postMediaInputRef = useRef<HTMLInputElement>(null);
  const [includedPlaceholderLevels, setIncludedPlaceholderLevels] = useState<number[]>([]);
  const [placeholderDataByLevel, setPlaceholderDataByLevel] = useState<Record<number, PlaceholderTier>>({});
  const [placeholderCoverLevel, setPlaceholderCoverLevel] = useState<number | null>(null);
  const [uploadingPlaceholderCoverLevel, setUploadingPlaceholderCoverLevel] = useState<number | null>(null);
  const [avatarTooltip, setAvatarTooltip] = useState<{ x: number; y: number } | null>(null);
  const fileInputAvatar = useRef<HTMLInputElement>(null);
  const fileInputCover = useRef<HTMLInputElement>(null);
  const tierCoverInputRef = useRef<HTMLInputElement>(null);
  const displayNameInputRef = useRef<HTMLInputElement>(null);
  const aboutTextareaRef = useRef<HTMLTextAreaElement>(null);

  const emptyProfileDraft = useCallback((): ProfileDraft => ({
    display_name: '',
    slug: '',
    about: '',
    tag_ids: [],
    profile_avatar_path: undefined,
    profile_cover_path: undefined,
    avatar_preview_url: null,
    cover_preview_url: null,
    telegram_url: '',
    instagram_url: '',
    facebook_url: '',
    youtube_url: '',
    twitch_url: '',
    website_url: '',
  }), []);

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { replace: true });
      return;
    }
  }, [user, authLoading, navigate]);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      const list = await tagsApi.list().catch(() => []);
      if (!cancelled) setTags(Array.isArray(list) ? list : []);
    })();
    return () => { cancelled = true; };
  }, []);

  useEffect(() => {
    if (editingDisplayName) displayNameInputRef.current?.focus();
  }, [editingDisplayName]);

  useEffect(() => {
    if (editingAbout) aboutTextareaRef.current?.focus();
  }, [editingAbout]);

  useEffect(() => {
    if (!slugHighlightError) return;
    const t = setTimeout(() => setSlugHighlightError(false), 1500);
    return () => clearTimeout(t);
  }, [slugHighlightError]);

  useEffect(() => {
    if (!user) return;
    let cancelled = false;
    setLoading(true);
    (async () => {
      try {
        const me = await creatorProfilesApi.me().catch(() => null);
        if (cancelled) return;
        if (me && !routeIsNew) {
          setMode('edit');
          setProfileId(me.id);
          setProfileSlug(me.slug ?? null);
          setServerFollowersCount(me.followers_count ?? null);
          setServerSubscribersCount(me.subscribers_count ?? null);
          setProfileDraft({
            display_name: me.display_name ?? '',
            slug: me.slug ?? '',
            about: me.about ?? '',
            tag_ids: me.tags?.map((t: Tag) => t.id) ?? [],
            profile_avatar_path: undefined,
            profile_cover_path: undefined,
            avatar_preview_url: me.profile_avatar_url ?? null,
            cover_preview_url: me.profile_cover_url ?? null,
            telegram_url: me.telegram_url ?? '',
            instagram_url: me.instagram_url ?? '',
            facebook_url: me.facebook_url ?? '',
            youtube_url: me.youtube_url ?? '',
            twitch_url: me.twitch_url ?? '',
            website_url: me.website_url ?? '',
          });
          const [tiersRes, postsRes] = await Promise.all([
            tiersApi.listMine().then((t) => (Array.isArray(t) ? t : [])),
            postsApi.listByCreator(me.slug!, { per_page: POSTS_LOAD_PER_PAGE }).then((r) => r.data ?? []),
          ]);
          if (!cancelled) {
            setTiersDraft(tiersRes as DraftTier[]);
            setPostsDraft(postsRes as DraftPost[]);
            setPlaceholderPosts([createEmptyPlaceholderPost()]);
            setIncludedPlaceholderPostIndices([]);
          }
        } else if (me && routeIsNew) {
          navigate('/creator/edit', { replace: true });
          return;
        } else {
          setMode('create');
          setProfileId(null);
          setProfileSlug(null);
          setServerFollowersCount(null);
          setServerSubscribersCount(null);
          setProfileDraft(emptyProfileDraft());
          setTiersDraft([]);
          setPostsDraft([]);
          setPlaceholderPosts([createEmptyPlaceholderPost()]);
          setIncludedPlaceholderPostIndices([]);
        }
      } catch {
        if (!cancelled) setMode(null);
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [user, emptyProfileDraft, routeIsNew, navigate]);

  const updateProfileDraft = useCallback((updates: Partial<ProfileDraft>) => {
    setProfileDraft((p) => {
      if (!p) return p;
      const next = { ...p, ...updates };
      if (updates.display_name !== undefined && typeof updates.display_name === 'string') {
        next.slug = slugify(updates.display_name);
      }
      return next;
    });
  }, [mode]);

  const toggleTag = useCallback((id: number) => {
    setProfileDraft((p) => {
      if (!p) return p;
      const has = p.tag_ids.includes(id);
      return {
        ...p,
        tag_ids: has ? p.tag_ids.filter((x) => x !== id) : [...p.tag_ids, id],
      };
    });
  }, []);

  const handleAvatarFile = useCallback(async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file || !file.type.match(/^image\/(jpeg|png|webp)$/) || file.size > MAX_FILE_BYTES) {
      showToast('Use JPEG, PNG or WebP under 5 MB', 'error');
      return;
    }
    setUploading('avatar');
    try {
      const { path, url } = await creatorProfilesApi.uploadAvatar(file);
      updateProfileDraft({ profile_avatar_path: path, avatar_preview_url: url });
    } catch {
      showToast('Avatar upload failed', 'error');
    } finally {
      setUploading(null);
    }
  }, [showToast, updateProfileDraft]);

  const handleCoverFile = useCallback(async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file || !file.type.match(/^image\/(jpeg|png|webp)$/) || file.size > MAX_FILE_BYTES) {
      showToast('Use JPEG, PNG or WebP under 5 MB', 'error');
      return;
    }
    setUploading('cover');
    try {
      const { path, url } = await creatorProfilesApi.uploadCover(file);
      updateProfileDraft({ profile_cover_path: path, cover_preview_url: url });
    } catch {
      showToast('Cover upload failed', 'error');
    } finally {
      setUploading(null);
    }
  }, [showToast, updateProfileDraft]);

  const visibleTiers = tiersDraft.filter((t) => !tierIdsToDelete.has(t.id));
  const visiblePosts = postsDraft.filter((p) => !postSlugsToDelete.has(p.slug));

  const missingLevels = [1, 2, 3].filter((l) => !visibleTiers.some((t) => t.level === l));

  const updateTierInDraft = useCallback((tierId: number, updates: Partial<DraftTier>) => {
    setTiersDraft((prev) =>
      prev.map((t) => (t.id === tierId ? { ...t, ...updates } : t))
    );
  }, []);

  const handleTierCoverFile = useCallback(
    async (e: React.ChangeEvent<HTMLInputElement>) => {
      const tierId = tierIdForCoverUpload;
      const level = placeholderCoverLevel;
      setTierIdForCoverUpload(null);
      setPlaceholderCoverLevel(null);
      const file = e.target.files?.[0];
      e.target.value = '';
      if ((!tierId && level == null) || !file || !file.type.match(/^image\/(jpeg|png|webp)$/) || file.size > MAX_FILE_BYTES) {
        if (file) showToast('Use JPEG, PNG or WebP under 5 MB', 'error');
        return;
      }
      if (tierId != null) {
        setUploadingTierCoverId(tierId);
        try {
          const { path, url } = await tiersApi.uploadCover(file);
          setTiersDraft((prev) =>
            prev.map((t) =>
              t.id === tierId
                ? { ...t, tier_cover_url: url, tier_cover_preview_url: url, tier_cover_path: path } as DraftTier & { tier_cover_path?: string }
                : t
            )
          );
        } catch {
          showToast('Cover upload failed', 'error');
        } finally {
          setUploadingTierCoverId(null);
        }
      } else if (level != null) {
        setUploadingPlaceholderCoverLevel(level);
        try {
          const { path, url } = await tiersApi.uploadCover(file);
          setPlaceholderDataByLevel((prev) => ({
            ...prev,
            [level]: { ...(prev[level] ?? getDefaultPlaceholderForLevel(level as 1 | 2 | 3)), tier_cover_path: path, tier_cover_preview_url: url ?? undefined },
          }));
          setIncludedPlaceholderLevels((prev) => (prev.includes(level) ? prev : [...prev, level]));
        } catch {
          showToast('Cover upload failed', 'error');
        } finally {
          setUploadingPlaceholderCoverLevel(null);
        }
      }
    },
    [showToast, tierIdForCoverUpload, placeholderCoverLevel]
  );

  const handleSave = useCallback(async () => {
    if (!profileDraft || !user) return;
    const displayName = profileDraft.display_name.trim();
    const slug = profileDraft.slug.trim() || slugify(displayName) || 'creator';
    if (!displayName) {
      showToast('Display name is required', 'error');
      return;
    }
    setSaving(true);
    try {
      if (mode === 'create') {
        type CreatePayload = Parameters<typeof creatorProfilesApi.create>[0];
        const profilePayload: CreatePayload = {
          display_name: displayName,
          slug,
          about: profileDraft.about.trim() || null,
          tag_ids: profileDraft.tag_ids,
          telegram_url: profileDraft.telegram_url?.trim() || null,
          instagram_url: profileDraft.instagram_url?.trim() || null,
          facebook_url: profileDraft.facebook_url?.trim() || null,
          youtube_url: profileDraft.youtube_url?.trim() || null,
          twitch_url: profileDraft.twitch_url?.trim() || null,
          website_url: profileDraft.website_url?.trim() || null,
          profile_avatar_path: profileDraft.profile_avatar_path ?? undefined,
          profile_cover_path: profileDraft.profile_cover_path ?? undefined,
        };
        const created = await creatorProfilesApi.create(profilePayload);
        await refreshUser();
        const newSlug = created.slug ?? slug;
        const tierIdMap = new Map<number | string, number>();
        for (const t of tiersDraft) {
          if (t._isNew) {
            const createdTier = await tiersApi.create({
              tier_name: t.tier_name,
              tier_desc: t.tier_desc ?? '',
              price: t.price ?? 0,
              tier_currency: t.tier_currency ?? 'USD',
              tier_cover_path: (t as Tier & { tier_cover_path?: string }).tier_cover_path ?? undefined,
            });
            tierIdMap.set(t.id, createdTier.id);
          }
        }
        for (const level of [1, 2, 3]) {
          if (includedPlaceholderLevels.includes(level)) {
            const p = placeholderDataByLevel[level];
            if (p) {
              const createdTier = await tiersApi.create({
                tier_name: p.tier_name,
                tier_desc: p.tier_desc ?? '',
                price: p.price ?? 0,
                tier_currency: p.tier_currency ?? 'USD',
                tier_cover_path: p.tier_cover_path ?? undefined,
              });
              tierIdMap.set(`placeholder-${level}`, createdTier.id);
            }
          }
        }
        for (const p of postsDraft) {
          if (p._isNew) {
            const requiredTierId = p.required_tier?.id ? tierIdMap.get(p.required_tier.id) ?? p.required_tier.id : null;
            await postsApi.create({
              slug: (p.slug || slugify(p.title || '')).trim() || 'post',
              title: p.title ?? '',
              content_text: p.content_text ?? '',
              excerpt: p.excerpt ?? null,
              media_url: p.media_url ?? null,
              media_type: p.media_type ?? null,
              required_tier_id: requiredTierId ?? undefined,
            } as PostCreatePayload);
          }
        }
        for (const p of placeholderPosts) {
          if ((p.content_text ?? '').trim().length === 0) continue;
          const postSlug = (p.slug || slugify(p.title || '')).trim() || 'post';
          const requiredTierId = p.required_tier?.id ? tierIdMap.get(p.required_tier.id) ?? p.required_tier.id : null;
          await postsApi.create({
            slug: postSlug,
            title: p.title ?? '',
            content_text: p.content_text ?? '',
            excerpt: p.excerpt ?? null,
            media_url: p.media_url ?? null,
            media_type: p.media_type ?? null,
            required_tier_id: requiredTierId ?? undefined,
          } as PostCreatePayload);
        }
        showToast('Creator profile created!', 'success');
        navigate(`/creator/${newSlug}`, { replace: true });
        return;
      }

      if (mode === 'edit' && profileId != null) {
        type UpdatePayload = Parameters<typeof creatorProfilesApi.updateMe>[0];
        const profilePayload: UpdatePayload = {
          display_name: displayName,
          slug,
          about: profileDraft.about.trim() || null,
          tag_ids: profileDraft.tag_ids,
          telegram_url: profileDraft.telegram_url.trim() || null,
          instagram_url: profileDraft.instagram_url.trim() || null,
          facebook_url: profileDraft.facebook_url.trim() || null,
          youtube_url: profileDraft.youtube_url.trim() || null,
          twitch_url: profileDraft.twitch_url.trim() || null,
          website_url: profileDraft.website_url.trim() || null,
          profile_avatar_path: profileDraft.profile_avatar_path,
          profile_cover_path: profileDraft.profile_cover_path,
        };
        const updatedProfile = await creatorProfilesApi.updateMe(profilePayload);
        setServerFollowersCount(updatedProfile?.followers_count ?? serverFollowersCount);
        setServerSubscribersCount(updatedProfile?.subscribers_count ?? serverSubscribersCount);
        for (const id of tierIdsToDelete) {
          await tiersApi.delete(id);
        }
        const newTiers: DraftTier[] = [];
        const tierIdByOldId = new Map<number, number>();
        for (const t of tiersDraft) {
          if (tierIdsToDelete.has(t.id)) continue;
          if (t._isNew) {
            const createdTier = await tiersApi.create({
              tier_name: t.tier_name,
              tier_desc: t.tier_desc ?? '',
              price: t.price ?? 0,
              tier_currency: t.tier_currency ?? 'USD',
              tier_cover_path: (t as Tier & { tier_cover_path?: string }).tier_cover_path ?? undefined,
            });
            tierIdByOldId.set(t.id, createdTier.id);
            newTiers.push({ ...createdTier, _isNew: false } as DraftTier);
          } else {
            const payload: TierUpdatePayload = {
              tier_name: t.tier_name,
              tier_desc: t.tier_desc ?? '',
              price: t.price ?? 0,
              tier_currency: t.tier_currency ?? 'USD',
            };
            const tierWithPath = t as DraftTier & { tier_cover_path?: string };
            if (tierWithPath.tier_cover_path !== undefined) payload.tier_cover_path = tierWithPath.tier_cover_path;
            const updated = await tiersApi.update(t.id, payload);
            tierIdByOldId.set(t.id, updated.id);
            newTiers.push(updated as DraftTier);
          }
        }
        for (const level of includedPlaceholderLevels) {
          const p = placeholderDataByLevel[level];
          if (!p) continue;
          const createdTier = await tiersApi.create({
            tier_name: p.tier_name,
            tier_desc: p.tier_desc ?? '',
            price: p.price ?? 0,
            tier_currency: p.tier_currency ?? 'USD',
            tier_cover_path: p.tier_cover_path ?? undefined,
          });
          newTiers.push({ ...createdTier, _isNew: false } as DraftTier);
        }
        setTiersDraft(newTiers);
        setPlaceholderDataByLevel((prev) => {
          const next = { ...prev };
          for (const l of includedPlaceholderLevels) delete next[l];
          return next;
        });
        setIncludedPlaceholderLevels([]);
        setTierIdsToDelete(new Set());

        for (const slug of postSlugsToDelete) {
          await postsApi.deleteMine(slug);
        }
        setPostSlugsToDelete(new Set());
        const updatedPosts: DraftPost[] = [];
        for (const p of postsDraft) {
          if (postSlugsToDelete.has(p.slug)) continue;
          if (p._isNew) {
            const requiredTierId = p.required_tier?.id != null ? (tierIdByOldId.get(p.required_tier.id) ?? p.required_tier.id) : null;
            const created = await postsApi.create({
              slug: p.slug,
              title: p.title,
              content_text: p.content_text ?? '',
              excerpt: p.excerpt ?? null,
              media_url: p.media_url ?? null,
              media_type: p.media_type ?? null,
              required_tier_id: requiredTierId ?? undefined,
            } as PostCreatePayload);
            updatedPosts.push({ ...created, _isNew: false } as DraftPost);
          } else {
            const payload: PostUpdatePayload = {};
            if (p.title) payload.title = p.title;
            if (p.content_text !== undefined) payload.content_text = p.content_text ?? undefined;
            if (p.excerpt !== undefined) payload.excerpt = p.excerpt;
            if (p.media_url !== undefined) payload.media_url = p.media_url;
            if (p.media_type !== undefined) payload.media_type = p.media_type;
            if (p.required_tier?.id !== undefined) payload.required_tier_id = p.required_tier.id ?? null;
            if (Object.keys(payload).length > 0) {
              const updated = await postsApi.update(p.slug, payload);
              updatedPosts.push({ ...updated, _isNew: false } as DraftPost);
            } else {
              updatedPosts.push(p);
            }
          }
        }
        for (const p of placeholderPosts) {
          if ((p.content_text ?? '').trim().length === 0) continue;
          const postSlug = (p.slug || slugify(p.title || '')).trim() || 'post';
          const requiredTierId = p.required_tier?.id != null ? (tierIdByOldId.get(p.required_tier.id) ?? p.required_tier.id) : null;
          const created = await postsApi.create({
            slug: postSlug,
            title: p.title ?? '',
            content_text: p.content_text ?? '',
            excerpt: p.excerpt ?? null,
            media_url: p.media_url ?? null,
            media_type: p.media_type ?? null,
            required_tier_id: requiredTierId ?? undefined,
          } as PostCreatePayload);
          updatedPosts.push({ ...created, _isNew: false } as DraftPost);
        }
        setPostsDraft(updatedPosts);
        const keptPlaceholders = placeholderPosts.filter((p) => (p.content_text ?? '').trim().length === 0);
        setPlaceholderPosts(keptPlaceholders.length > 0 ? keptPlaceholders : [createEmptyPlaceholderPost()]);
        setIncludedPlaceholderPostIndices([]);
        await refreshUser();
        showToast('Changes saved', 'success');
        const newSlug = profileDraft.slug.trim() || slug;
        setProfileSlug(newSlug);
        navigate(`/creator/${newSlug}`, { replace: true });
      }
    } catch (e) {
      const raw = e instanceof Error ? e.message : 'Save failed';
      const valErr = e instanceof ValidationError ? e as ValidationError : null;
      const isSlug = raw.toLowerCase().includes('already been taken') ||
        (valErr && Array.isArray(valErr.errors?.slug));
      const socialKeys: SocialKey[] = ['telegram_url', 'instagram_url', 'facebook_url', 'youtube_url', 'twitch_url', 'website_url'];
      const firstSocialError = valErr && socialKeys.map((k) => valErr.errors?.[k]?.[0]).find(Boolean);
      const displayMessage = isSlug
        ? 'This slug is already taken. Choose another'
        : typeof firstSocialError === 'string'
          ? firstSocialError
          : raw;
      showToast(displayMessage, 'error');
      if (isSlug) setSlugHighlightError(true);
    } finally {
      setSaving(false);
    }
  }, [mode, profileDraft, profileId, user, tiersDraft, postsDraft, tierIdsToDelete, postSlugsToDelete, includedPlaceholderLevels, placeholderDataByLevel, placeholderPosts, showToast, refreshUser, navigate]);

  const saveTierFromModal = useCallback((tier: DraftTier, isNew: boolean) => {
    if (isNew) {
      setTiersDraft((prev) => [...prev, { ...tier, _isNew: true }]);
    } else {
      setTiersDraft((prev) => prev.map((t) => (t.id === tier.id ? { ...tier } : t)));
    }
    setTierModal(null);
  }, []);

  const removeTier = useCallback((id: number) => {
    setTierIdConfirmRemove(null);
    if (id > 0) {
      setTierIdsToDelete((s) => new Set(s).add(id));
    } else {
      setTiersDraft((prev) => prev.filter((t) => t.id !== id));
    }
  }, []);

  const updatePlaceholderData = useCallback((level: number, updates: Partial<PlaceholderTier>) => {
    setPlaceholderDataByLevel((prev) => {
      const current = prev[level] ?? getDefaultPlaceholderForLevel(level as 1 | 2 | 3);
      return { ...prev, [level]: { ...current, ...updates } };
    });
    setIncludedPlaceholderLevels((prev) => (prev.includes(level) ? prev : [...prev, level]));
  }, []);

  const resetPlaceholder = useCallback((level: number) => {
    setPlaceholderConfirmRemoveLevel(null);
    setIncludedPlaceholderLevels((prev) => prev.filter((l) => l !== level));
    setPlaceholderDataByLevel((prev) => ({ ...prev, [level]: getDefaultPlaceholderForLevel(level as 1 | 2 | 3) }));
  }, []);

  const addPlaceholderPost = useCallback(() => {
    setPlaceholderPosts((prev) => [...prev, createEmptyPlaceholderPost()]);
  }, []);

  const updateDraftPost = useCallback((slugOrId: string | number, updates: Partial<DraftPost>) => {
    setPostsDraft((prev) =>
      prev.map((p) => {
        if (typeof slugOrId === 'number' ? p.id === slugOrId : p.slug === slugOrId) return { ...p, ...updates };
        return p;
      })
    );
  }, []);

  const updatePlaceholderPostAt = useCallback((index: number, updates: Partial<DraftPost>) => {
    setPlaceholderPosts((prev) => prev.map((p, i) => (i === index ? { ...p, ...updates } : p)));
    setIncludedPlaceholderPostIndices((prev) => (prev.includes(index) ? prev : [...prev, index]));
  }, []);

  const handlePostMediaFile = useCallback(
    async (e: React.ChangeEvent<HTMLInputElement>) => {
      const file = e.target.files?.[0];
      e.target.value = '';
      if (!file || !postMediaUploadTarget) return;
      setUploadingPostMedia(true);
      try {
        const res = await postsApi.uploadMedia(file);
        const updates: Partial<DraftPost> = {
          media_url: res.path,
          media_preview_url: res.url,
          media_type: res.media_type as DraftPost['media_type'],
        };
        if ('placeholderIndex' in postMediaUploadTarget) {
          updatePlaceholderPostAt(postMediaUploadTarget.placeholderIndex, updates);
        } else {
          updateDraftPost(postMediaUploadTarget.id, updates);
        }
      } catch {
        showToast('Media upload failed', 'error');
      } finally {
        setUploadingPostMedia(false);
        setPostMediaUploadTarget(null);
      }
    },
    [postMediaUploadTarget, updatePlaceholderPostAt, updateDraftPost, showToast]
  );

  const removePost = useCallback((payload: { post: DraftPost; isPlaceholder: boolean; placeholderIndex?: number }) => {
    setPostToConfirmRemove(null);
    const { post, isPlaceholder, placeholderIndex } = payload;
    if (isPlaceholder && placeholderIndex !== undefined) {
      setPlaceholderPosts((prev) => prev.filter((_, i) => i !== placeholderIndex));
      setIncludedPlaceholderPostIndices((prev) =>
        prev.filter((i) => i !== placeholderIndex).map((i) => (i > placeholderIndex ? i - 1 : i))
      );
    } else if (post._isNew) {
      setPostsDraft((prev) => prev.filter((p) => !(p as DraftPost)._isNew || p.id !== post.id));
    } else {
      setPostSlugsToDelete((s) => new Set(s).add(post.slug));
    }
  }, []);

  if (authLoading || loading || !profileDraft) {
    return <LoadingPage message={mode === null ? 'Loading...' : undefined} />;
  }
  if (!user) return null;

  if (mode === 'create' && !user.email_verified_at) {
    return (
      <div className="page-center">
        <div className="card creator-studio-verify-card" style={{ maxWidth: 420 }}>
          <h1 className="form-title">Verify your email</h1>
          <p className="form-subtitle">Verify your email before creating a creator page</p>
          <Link to="/creator/new" className="btn btn-primary creator-studio-verify-btn">
            Back
          </Link>
        </div>
      </div>
    );
  }

  const displayName = profileDraft.display_name.trim() || user.name || profileDraft.slug || 'Creator';
  const avatarUrl = profileDraft.avatar_preview_url ?? null;
  const coverUrl = profileDraft.cover_preview_url ?? null;

  return (
    <div className="profile-page creator-studio">
      <div className="creator-studio-toolbar">
        <span className="creator-studio-toolbar-label">
          {mode === 'create'
            ? 'Preview: your creator page. You can\'t add posts, tiers, or social links until the profile is created'
            : 'Edit your page - changes are preview only until you Save'}
        </span>
        <div className="creator-studio-toolbar-actions">
          {mode === 'edit' && profileSlug && (
            <button
              type="button"
              className="btn btn-secondary"
              onClick={() => navigate(`/creator/${profileSlug}`)}
            >
              Discard
            </button>
          )}
          <button
            type="button"
            className="btn btn-primary"
            disabled={saving}
            onClick={handleSave}
          >
            {saving ? 'Saving...' : mode === 'create' ? 'Create profile' : 'Save'}
          </button>
        </div>
      </div>


      {showProfilePanel && (
        <div className="creator-studio-panel card">
          <h3 className="profile-section-title">Profile</h3>
          <div className="form-group">
            <label>Display name</label>
            <input
              type="text"
              value={profileDraft.display_name}
              onChange={(e) => updateProfileDraft({ display_name: e.target.value })}
              maxLength={50}
            />
          </div>
          <div className="form-group">
            <input
              type="text"
              value={profileDraft.slug}
              onChange={(e) => {
                updateProfileDraft({ slug: e.target.value });
                setSlugHighlightError(false);
              }}
              placeholder="URL slug"
              aria-label="URL slug"
              className={slugHighlightError ? 'social-link-input-error' : ''}
              aria-invalid={slugHighlightError}
            />
          </div>
          <div className="form-group">
            <label>About</label>
            <textarea
              value={profileDraft.about}
              onChange={(e) => updateProfileDraft({ about: e.target.value })}
              rows={3}
              maxLength={255}
              className="form-textarea"
            />
          </div>
          <div className="form-group">
            <label>Social links</label>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
              {(['telegram_url', 'instagram_url', 'facebook_url', 'youtube_url', 'twitch_url', 'website_url'] as const).map((key) => (
                <input
                  key={key}
                  type="url"
                  placeholder={key.replace('_url', '')}
                  value={profileDraft[key]}
                  onChange={(e) => updateProfileDraft({ [key]: e.target.value })}
                />
              ))}
            </div>
          </div>
          <div className="form-group">
            <label>Tags</label>
            <div className="form-tag-chips" role="group">
              {tags.map((tag) => {
                const selected = profileDraft.tag_ids.includes(tag.id);
                return (
                  <button
                    key={tag.id}
                    type="button"
                    className={`tag-chip ${selected ? 'active' : ''}`}
                    onClick={() => toggleTag(tag.id)}
                    aria-pressed={selected}
                  >
                    {tag.name}
                  </button>
                );
              })}
            </div>
          </div>
        </div>
      )}

      <div
        className="profile-cover creator-studio-cover"
        style={coverUrl ? { backgroundImage: `url(${coverUrl})` } : undefined}
        onClick={() => fileInputCover.current?.click()}
        role="button"
        tabIndex={0}
        onKeyDown={(e) => e.key === 'Enter' && fileInputCover.current?.click()}
        aria-label="Change cover"
      >
        <input
          ref={fileInputCover}
          type="file"
          accept={ACCEPT_IMAGE}
          onChange={handleCoverFile}
          className="form-image-input"
          style={{ display: 'none' }}
        />
        <span className="creator-studio-cover-label">
          {uploading === 'cover' ? 'Uploading...' : 'Change cover'}
        </span>
        <span className="creator-studio-cover-hint">Recommended: {COVER_IMAGE_RECOMMENDED}</span>
      </div>

      <div className="profile-container">
        <div className="profile-main">
          <div className="profile-header">
            <div
              className="creator-studio-avatar-wrap"
              onClick={() => fileInputAvatar.current?.click()}
              role="button"
              tabIndex={0}
              onKeyDown={(e) => e.key === 'Enter' && fileInputAvatar.current?.click()}
              aria-label="Change avatar"
              onMouseEnter={(e) => setAvatarTooltip({ x: e.clientX, y: e.clientY })}
              onMouseMove={(e) => setAvatarTooltip((prev) => (prev ? { x: e.clientX, y: e.clientY } : null))}
              onMouseLeave={() => setAvatarTooltip(null)}
            >
              <input
                ref={fileInputAvatar}
                type="file"
                accept={ACCEPT_IMAGE}
                onChange={handleAvatarFile}
                className="form-image-input"
                style={{ display: 'none' }}
              />
              {avatarUrl ? (
                <img src={avatarUrl} alt="" className="profile-avatar" />
              ) : (
                <div className="profile-avatar profile-avatar-placeholder">
                  {displayName.charAt(0).toUpperCase()}
                </div>
              )}
              <span className="creator-studio-avatar-label">{uploading === 'avatar' ? '...' : 'Change'}</span>
            </div>
            {avatarTooltip && (
              <div
                className="creator-studio-avatar-hint-tooltip"
                style={{
                  position: 'fixed',
                  left: avatarTooltip.x,
                  top: avatarTooltip.y,
                  transform: 'translate(12px, 12px)',
                }}
              >
                Recommended: {AVATAR_IMAGE_RECOMMENDED}
              </div>
            )}
            <div className="profile-heading">
              <div className="profile-heading-top-row">
                <div className="profile-heading-left">
                  {editingDisplayName ? (
                    <input
                      ref={displayNameInputRef}
                      type="text"
                      className="profile-name profile-name-inline-edit"
                      value={profileDraft.display_name}
                      onChange={(e) => updateProfileDraft({ display_name: e.target.value })}
                      onBlur={() => {
                        updateProfileDraft({ display_name: profileDraft.display_name.trim() });
                        setEditingDisplayName(false);
                      }}
                      onKeyDown={(e) => {
                        if (e.key === 'Enter') {
                          e.currentTarget.blur();
                        }
                        if (e.key === 'Escape') {
                          updateProfileDraft({ display_name: profileDraft.display_name.trim() });
                          setEditingDisplayName(false);
                          e.currentTarget.blur();
                        }
                      }}
                      maxLength={50}
                      aria-label="Display name"
                    />
                  ) : (
                    <h1
                      className="profile-name profile-name-clickable"
                      onClick={() => setEditingDisplayName(true)}
                      onFocus={() => setEditingDisplayName(true)}
                      tabIndex={0}
                      role="button"
                      aria-label="Display name (click to edit)"
                    >
                      {displayName}
                    </h1>
                  )}
                  {user.username && <span className="profile-username">@{user.username}</span>}
                </div>
                <div className="profile-slug-row-studio profile-slug-row-studio-inline">
                  <input
                    type="text"
                    className={`profile-slug-input-studio ${slugHighlightError ? 'social-link-input-error' : ''}`}
                    value={profileDraft.slug}
                    onChange={(e) => {
                      updateProfileDraft({ slug: e.target.value });
                      setSlugHighlightError(false);
                    }}
                    onBlur={() => {
                      const slug = profileDraft.slug.trim() || slugify(profileDraft.display_name || '');
                      updateProfileDraft({ slug: slug === 'post' ? '' : slug });
                    }}
                    placeholder="URL slug"
                    aria-label="URL slug"
                    aria-invalid={slugHighlightError}
                  />
                </div>
              </div>
              <div className="profile-stats-row">
                <div className="profile-stats">
                  <span className="profile-stat">
                    <span className="profile-stat-value">{serverFollowersCount ?? 0}</span>
                    <span className="profile-stat-label">Followers</span>
                  </span>
                  <span className="profile-stat">
                    <span className="profile-stat-value">{serverSubscribersCount ?? 0}</span>
                    <span className="profile-stat-label">Subscribers</span>
                  </span>
                  <span className="profile-stat">
                    <span className="profile-stat-value">{visiblePosts.length}</span>
                    <span className="profile-stat-label">Posts</span>
                  </span>
                </div>
                <div className="profile-social-links profile-social-links-studio">
                  {SOCIAL_LINKS_CONFIG.map(({ key, label }) => {
                    const url = profileDraft[key]?.trim() || '';
                    const hasLink = url.length > 0;
                    return (
                      <span key={key} className="profile-social-link-wrap-studio">
                        {hasLink ? (
                          <a href={url} target="_blank" rel="noopener noreferrer" className="profile-social-link" title={label} aria-label={label}>
                            <SocialIcon keyName={key} />
                          </a>
                        ) : (
                          <span className="profile-social-link profile-social-link-empty" title={`Add ${label}`} aria-label={`Add ${label}`}>
                            <SocialIcon keyName={key} />
                          </span>
                        )}
                        <button
                          type="button"
                          className="profile-social-edit-btn"
                          disabled={mode === 'create'}
                          onClick={() => setSocialModalKey(key)}
                          aria-label={hasLink ? `Edit ${label} link` : `Add ${label} link`}
                          title={hasLink ? `Edit ${label}` : `Add ${label}`}
                        >
                          {hasLink ? (
                            <EditIcon size={14} aria-hidden />
                          ) : (
                            <PlusIcon size={14} aria-hidden />
                          )}
                        </button>
                      </span>
                    );
                  })}
                </div>
              </div>
            </div>
          </div>

          <section className="profile-posts">
            <div className="profile-section-title-row">
              <h2 className="profile-section-title">Posts</h2>
            </div>
            <div className={`creator-studio-section-wrap ${mode === 'create' ? 'creator-studio-section-wrap-disabled' : ''}`}>
              {mode === 'create' && <div className="creator-studio-block-overlay" aria-hidden />}
              <input
              ref={postMediaInputRef}
              type="file"
              accept={ACCEPT_MEDIA}
              onChange={handlePostMediaFile}
              className="form-image-input"
              style={{ display: 'none' }}
            />
            <ul className="post-card-list post-card-list-studio">
              {visiblePosts.map((post) => (
                <li key={post.id} className="post-card-wrapper post-card-wrapper-studio post-card-list-item-studio">
                  <button
                    type="button"
                    className="tier-card-remove-btn"
                    disabled={mode === 'create'}
                    onClick={() => setPostToConfirmRemove({ post, isPlaceholder: false })}
                    aria-label="Remove post"
                    title="Remove post"
                  >
                    <Trash2Icon size={18} aria-hidden />
                  </button>
                  <PostCardStudio
                    post={post}
                    isPlaceholder={false}
                    willSave
                    visibleTiers={visibleTiers}
                    postMediaHint={POST_MEDIA_HINT}
                    uploading={uploadingPostMedia && postMediaUploadTarget !== null && !('placeholderIndex' in postMediaUploadTarget) && postMediaUploadTarget.id === post.id}
                    onUpdate={(updates) => updateDraftPost(post.id, updates)}
                    onMediaClick={() => { setPostMediaUploadTarget({ slug: post.slug, id: post.id }); postMediaInputRef.current?.click(); }}
                  />
                </li>
              ))}
              {placeholderPosts.map((post, idx) => (
                <li key={`placeholder-${idx}`} className="post-card-wrapper post-card-wrapper-studio post-card-list-item-studio">
                  <button
                    type="button"
                    className="tier-card-remove-btn"
                    disabled={mode === 'create'}
                    onClick={() => setPostToConfirmRemove({ post, isPlaceholder: true, placeholderIndex: idx })}
                    aria-label="Discard this draft"
                    title="Discard draft"
                  >
                    <Trash2Icon size={18} aria-hidden />
                  </button>
                  <PostCardStudio
                    post={post}
                    isPlaceholder
                    placeholderIndex={idx}
                    willSave={includedPlaceholderPostIndices.includes(idx)}
                    visibleTiers={visibleTiers}
                    postMediaHint={POST_MEDIA_HINT}
                    uploading={uploadingPostMedia && postMediaUploadTarget !== null && 'placeholderIndex' in postMediaUploadTarget && postMediaUploadTarget.placeholderIndex === idx}
                    onUpdate={(updates) => updatePlaceholderPostAt(idx, updates)}
                    onMediaClick={() => { setPostMediaUploadTarget({ placeholderIndex: idx }); postMediaInputRef.current?.click(); }}
                  />
                </li>
              ))}
            </ul>
            <button type="button" className="post-card-add-btn" disabled={mode === 'create'} onClick={addPlaceholderPost}>
              Add post
            </button>
            </div>
          </section>
        </div>

        <aside className="profile-sidebar">
          <section className="profile-about">
            <h2 className="profile-section-title">About {displayName.replace(/\s+.*$/, '')}</h2>
            {editingAbout ? (
              <textarea
                ref={aboutTextareaRef}
                className="profile-about-text profile-about-inline-edit"
                value={profileDraft.about}
                onChange={(e) => updateProfileDraft({ about: e.target.value })}
                onBlur={() => {
                  updateProfileDraft({ about: profileDraft.about.trim() });
                  setEditingAbout(false);
                }}
                onKeyDown={(e) => {
                  if (e.key === 'Escape') {
                    updateProfileDraft({ about: profileDraft.about.trim() });
                    setEditingAbout(false);
                    e.currentTarget.blur();
                  }
                }}
                maxLength={255}
                rows={4}
                placeholder="No description yet"
                aria-label="About / description"
              />
            ) : (
              <p
                className={`profile-about-text ${!profileDraft.about ? 'profile-about-empty' : ''} profile-about-clickable`}
                onClick={() => setEditingAbout(true)}
                onFocus={() => setEditingAbout(true)}
                tabIndex={0}
                role="button"
                aria-label="About (click to edit)"
              >
                {profileDraft.about || 'No description yet'}
              </p>
            )}
            <div className="profile-tag-list profile-tag-list-studio">
              {tags.filter((t) => profileDraft.tag_ids.includes(t.id)).map((t) => (
                <span key={t.id} className="creator-tag creator-tag-pill">{t.name}</span>
              ))}
              <button
                type="button"
                className="creator-tag creator-tag-pill creator-tag-add-btn"
                onClick={() => setShowTagsModal(true)}
                aria-label="Add or edit tags"
              >
                + Add tags
              </button>
            </div>
          </section>

          {showTagsModal && (
            <TagsModal
              tags={tags}
              selectedIds={profileDraft.tag_ids}
              onToggle={(id) => {
                const next = profileDraft.tag_ids.includes(id)
                  ? profileDraft.tag_ids.filter((x) => x !== id)
                  : [...profileDraft.tag_ids, id];
                updateProfileDraft({ tag_ids: next });
              }}
              onClose={() => setShowTagsModal(false)}
            />
          )}

          <section id="profile-tiers" className="profile-tiers">
            <div className="profile-section-title-row">
              <h2 className="profile-section-title">Subscription Tiers</h2>
            </div>
            <div className={`creator-studio-section-wrap ${mode === 'create' ? 'creator-studio-section-wrap-disabled' : ''}`}>
              {mode === 'create' && <div className="creator-studio-block-overlay" aria-hidden />}
            <input
              ref={tierCoverInputRef}
              type="file"
              accept={ACCEPT_IMAGE}
              onChange={handleTierCoverFile}
              className="form-image-input"
              style={{ display: 'none' }}
            />
            <ul className="tier-list tier-list-sidebar">
              {([1, 2, 3] as const).map((level) => {
                const tier = visibleTiers.find((t) => t.level === level);
                const isMissingLevel = missingLevels.includes(level);
                const pending = isMissingLevel ? (placeholderDataByLevel[level] ?? getDefaultPlaceholderForLevel(level as 1 | 2 | 3)) : null;
                const placeholderWillSave = isMissingLevel && includedPlaceholderLevels.includes(level);

                if (tier) {
                  return (
                    <li key={`tier-${tier.id}`} className="tier-list-item tier-list-item-studio">
                      <button
                        type="button"
                        className="tier-card-remove-btn"
                        disabled={mode === 'create'}
                        onClick={() => setTierIdConfirmRemove(tier.id)}
                        aria-label="Remove tier"
                        title="Remove tier"
                      >
                        <Trash2Icon size={18} aria-hidden />
                      </button>
                      <div className="tier-card tier-card-stacked tier-card-studio tier-card-will-save">
                        <div
                          className="tier-card-cover-wrap-studio"
                          onClick={() => { setTierIdForCoverUpload(tier.id); tierCoverInputRef.current?.click(); }}
                          role="button"
                          tabIndex={0}
                          onKeyDown={(e) => e.key === 'Enter' && (setTierIdForCoverUpload(tier.id), tierCoverInputRef.current?.click())}
                          aria-label="Change cover"
                        >
                          <span className="tier-card-cover-hint">Recommended: {TIER_COVER_IMAGE_RECOMMENDED}</span>
                          {tier.tier_cover_url || (tier as DraftTier).tier_cover_preview_url ? (
                            <div className="tier-card-cover tier-card-cover-img">
                              <img src={(tier as DraftTier).tier_cover_preview_url ?? tier.tier_cover_url ?? ''} alt="" />
                            </div>
                          ) : (
                            <div className="tier-card-cover tier-card-cover-placeholder" />
                          )}
                          <span className="tier-card-cover-hover-overlay" aria-hidden />
                          <span className="tier-card-cover-camera">
                            {uploadingTierCoverId === tier.id ? '...' : (
                              <CameraIcon size={20} aria-hidden />
                            )}
                          </span>
                        </div>
                        <div className="tier-card-body">
                          <input
                            type="text"
                            className="tier-card-name tier-card-name-input"
                            value={tier.tier_name ?? ''}
                            onChange={(e) => updateTierInDraft(tier.id, { tier_name: e.target.value })}
                            placeholder="Tier name"
                            maxLength={50}
                            aria-label="Tier name"
                          />
                          <textarea
                            className="tier-card-desc tier-card-desc-input"
                            value={tier.tier_desc ?? ''}
                            onChange={(e) => updateTierInDraft(tier.id, { tier_desc: e.target.value })}
                            onKeyDown={(e) => {
                              if (e.key === 'Enter') {
                                const ta = e.currentTarget;
                                const start = ta.selectionStart ?? 0;
                                const end = ta.selectionEnd ?? start;
                                const v = ta.value;
                                const before = v.slice(0, start);
                                const after = v.slice(end);
                                const newLine = '\nвЂў ';
                                const newVal = before + newLine + after;
                                if (newVal.length <= 255) {
                                  updateTierInDraft(tier.id, { tier_desc: newVal });
                                  e.preventDefault();
                                  const pos = start + newLine.length;
                                  setTimeout(() => { ta.focus(); ta.setSelectionRange(pos, pos); }, 0);
                                }
                              }
                            }}
                            placeholder={'вЂў Description point\nвЂў Another point'}
                            rows={3}
                            maxLength={255}
                            aria-label="Tier description"
                          />
                          <div className="tier-card-price-row">
                            <input
                              type="number"
                              min={0}
                              className="tier-card-price-input"
                              value={tier.price === 0 ? '' : tier.price}
                              onChange={(e) => updateTierInDraft(tier.id, { price: parseInt(e.target.value, 10) || 0 })}
                              aria-label="Price"
                            />
                            <div className="form-tag-chips tier-card-currency-chips" role="group" aria-label="Currency">
                              {CURRENCIES.map((c) => (
                                <button
                                  key={c}
                                  type="button"
                                  className={`tag-chip ${(tier.tier_currency ?? 'USD') === c ? 'active' : ''}`}
                                  onClick={() => updateTierInDraft(tier.id, { tier_currency: c })}
                                  aria-pressed={(tier.tier_currency ?? 'USD') === c}
                                >
                                  {c}
                                </button>
                              ))}
                            </div>
                          </div>
                          <span className="tier-card-level-badge" aria-label={`Level ${tier.level}`}>Level {tier.level}</span>
                        </div>
                      </div>
                    </li>
                  );
                }

                if (isMissingLevel && pending) {
                  return (
                    <li key={`placeholder-${level}`} className="tier-list-item tier-list-item-studio">
                      <button
                        type="button"
                        className="tier-card-remove-btn"
                        disabled={mode === 'create'}
                        onClick={() => setPlaceholderConfirmRemoveLevel(level)}
                        aria-label="Discard changes to this tier"
                        title="Discard changes"
                      >
                        <Trash2Icon size={18} aria-hidden />
                      </button>
                      <div className={`tier-card tier-card-stacked tier-card-studio tier-card-placeholder-editable ${placeholderWillSave ? 'tier-card-will-save' : 'tier-card-wont-save'}`}>
                        <div
                          className="tier-card-cover-wrap-studio tier-card-cover-gradient-wrap"
                          onClick={() => {
                            setPlaceholderCoverLevel(level);
                            setTimeout(() => tierCoverInputRef.current?.click(), 0);
                          }}
                          role="button"
                          tabIndex={0}
                          onKeyDown={(e) => e.key === 'Enter' && (setPlaceholderCoverLevel(level), setTimeout(() => tierCoverInputRef.current?.click(), 0))}
                          aria-label="Change cover"
                        >
                          <span className="tier-card-cover-hint">Recommended: {TIER_COVER_IMAGE_RECOMMENDED}</span>
                          {pending.tier_cover_preview_url ? (
                            <div className="tier-card-cover tier-card-cover-img">
                              <img src={pending.tier_cover_preview_url} alt="" />
                            </div>
                          ) : (
                            <div className="tier-card-cover tier-card-cover-gradient" />
                          )}
                          <span className="tier-card-cover-hover-overlay" aria-hidden />
                          <span className="tier-card-cover-camera">
                            {uploadingPlaceholderCoverLevel === level ? '...' : (
                              <CameraIcon size={20} aria-hidden />
                            )}
                          </span>
                        </div>
                        <div className="tier-card-body">
                          <input
                            type="text"
                            className="tier-card-name tier-card-name-input"
                            value={pending.tier_name}
                            onChange={(e) => updatePlaceholderData(level, { tier_name: e.target.value })}
                            placeholder="Tier name"
                            maxLength={50}
                            aria-label="Tier name"
                          />
                          <textarea
                            className="tier-card-desc tier-card-desc-input"
                            value={pending.tier_desc}
                            onChange={(e) => updatePlaceholderData(level, { tier_desc: e.target.value })}
                            onKeyDown={(e) => {
                              if (e.key === 'Enter') {
                                const ta = e.currentTarget;
                                const start = ta.selectionStart ?? 0;
                                const end = ta.selectionEnd ?? start;
                                const v = ta.value;
                                const before = v.slice(0, start);
                                const after = v.slice(end);
                                const newLine = '\nвЂў ';
                                const newVal = before + newLine + after;
                                if (newVal.length <= 255) {
                                  updatePlaceholderData(level, { tier_desc: newVal });
                                  e.preventDefault();
                                  const pos = start + newLine.length;
                                  setTimeout(() => { ta.focus(); ta.setSelectionRange(pos, pos); }, 0);
                                }
                              }
                            }}
                            placeholder={'вЂў Description point\nвЂў Another point'}
                            rows={3}
                            maxLength={255}
                            aria-label="Tier description"
                          />
                          <div className="tier-card-price-row">
                            <input
                              type="number"
                              min={0}
                              className="tier-card-price-input"
                              value={pending.price === 0 ? '' : pending.price}
                              onChange={(e) => updatePlaceholderData(level, { price: parseInt(e.target.value, 10) || 0 })}
                              aria-label="Price"
                            />
                            <div className="form-tag-chips tier-card-currency-chips" role="group" aria-label="Currency">
                              {CURRENCIES.map((c) => (
                                <button
                                  key={c}
                                  type="button"
                                  className={`tag-chip ${pending.tier_currency === c ? 'active' : ''}`}
                                  onClick={() => updatePlaceholderData(level, { tier_currency: c })}
                                  aria-pressed={pending.tier_currency === c}
                                >
                                  {c}
                                </button>
                              ))}
                            </div>
                          </div>
                          <span className="tier-card-level-badge" aria-label={`Level ${level}`}>Level {level}</span>
                        </div>
                      </div>
                    </li>
                  );
                }

                return null;
              })}
            </ul>
            </div>
            {tierIdConfirmRemove !== null && (
              <div className="tier-delete-overlay" role="dialog" aria-modal="true" aria-labelledby="tier-remove-confirm-title" onClick={() => setTierIdConfirmRemove(null)}>
                <div className="tier-delete-card card" style={{ maxWidth: 360 }} onClick={(e) => e.stopPropagation()}>
                  <h2 id="tier-remove-confirm-title" className="form-title">Remove this tier?</h2>
                  <p className="form-subtitle" style={{ marginBottom: '1rem' }}>
                    This cannot be undone. <strong>Active subscriptions</strong> to this tier will be ended. <strong>Posts</strong> that required this tier will become unlocked
                  </p>
                  <div style={{ display: 'flex', gap: '0.5rem' }}>
                    <button type="button" className="btn btn-primary" onClick={() => tierIdConfirmRemove !== null && removeTier(tierIdConfirmRemove)}>Remove</button>
                    <button type="button" className="btn btn-secondary" onClick={() => setTierIdConfirmRemove(null)}>Cancel</button>
                  </div>
                </div>
              </div>
            )}
            {placeholderConfirmRemoveLevel !== null && (
              <div className="tier-delete-overlay" role="dialog" aria-modal="true" aria-labelledby="placeholder-remove-confirm-title" onClick={() => setPlaceholderConfirmRemoveLevel(null)}>
                <div className="tier-delete-card card" style={{ maxWidth: 360 }} onClick={(e) => e.stopPropagation()}>
                  <h2 id="placeholder-remove-confirm-title" className="form-title">Discard changes?</h2>
                  <p className="form-subtitle" style={{ marginBottom: '1rem' }}>This tier level will not be saved</p>
                  <div style={{ display: 'flex', gap: '0.5rem' }}>
                    <button type="button" className="btn btn-primary" onClick={() => placeholderConfirmRemoveLevel !== null && resetPlaceholder(placeholderConfirmRemoveLevel)}>Discard</button>
                    <button type="button" className="btn btn-secondary" onClick={() => setPlaceholderConfirmRemoveLevel(null)}>Cancel</button>
                  </div>
                </div>
              </div>
            )}
          </section>
        </aside>
      </div>

      {postToConfirmRemove !== null && (
        <div className="tier-delete-overlay" role="dialog" aria-modal="true" aria-labelledby="post-remove-confirm-title" onClick={() => setPostToConfirmRemove(null)}>
          <div className="tier-delete-card card" style={{ maxWidth: 360 }} onClick={(e) => e.stopPropagation()}>
            <h2 id="post-remove-confirm-title" className="form-title">
              {postToConfirmRemove.isPlaceholder || postToConfirmRemove.post._isNew ? 'Discard this draft?' : 'Remove this post?'}
            </h2>
            <p className="form-subtitle" style={{ marginBottom: '1rem' }}>
              {postToConfirmRemove.isPlaceholder || postToConfirmRemove.post._isNew ? 'This post will not be saved' : 'This cannot be undone'}
            </p>
            <div style={{ display: 'flex', gap: '0.5rem' }}>
              <button type="button" className="btn btn-primary" onClick={() => postToConfirmRemove !== null && removePost(postToConfirmRemove)}>
                {postToConfirmRemove.isPlaceholder || postToConfirmRemove.post._isNew ? 'Discard' : 'Remove'}
              </button>
              <button type="button" className="btn btn-secondary" onClick={() => setPostToConfirmRemove(null)}>Cancel</button>
            </div>
          </div>
        </div>
      )}

      {tierModal?.tier != null && (
        <TierFormModal
          tier={tierModal.tier}
          isNew={tierModal.isNew}
          onSave={saveTierFromModal}
          onClose={() => setTierModal(null)}
        />
      )}

      {socialModalKey && profileDraft && (
        <SocialLinkModal
          keyName={socialModalKey}
          label={SOCIAL_LINKS_CONFIG.find((c) => c.key === socialModalKey)!.label}
          value={profileDraft[socialModalKey]}
          onSave={(value) => {
            updateProfileDraft({ [socialModalKey]: value });
            setSocialModalKey(null);
          }}
          onRemove={() => {
            updateProfileDraft({ [socialModalKey]: '' });
            setSocialModalKey(null);
          }}
          onClose={() => setSocialModalKey(null)}
        />
      )}
    </div>
  );
}
