import PostMediaContainer from '../../components/PostMediaContainer';
import RichTextEditor from '../../components/RichTextEditor';
import { CameraIcon } from '../../components/icons';
import type { DraftPost, DraftTier } from './types';
import { slugify } from './utils';

export function PostCardStudio({
  post,
  isPlaceholder,
  willSave,
  visibleTiers,
  postMediaHint,
  uploading,
  onUpdate,
  onMediaClick,
}: {
  post: DraftPost;
  isPlaceholder: boolean;
  placeholderIndex?: number;
  willSave: boolean;
  visibleTiers: DraftTier[];
  postMediaHint: string;
  uploading: boolean;
  onUpdate: (u: Partial<DraftPost>) => void;
  onMediaClick: () => void;
}) {
  const mediaDisplayUrl = post.media_preview_url ?? post.media_url;
  return (
    <article
      className={`post-card post-card-studio ${willSave ? 'post-card-will-save' : 'post-card-wont-save'}`}
    >
      <div className="post-card-studio-body">
        <div className="post-card-title-row post-card-studio-title-row">
          <input
            type="text"
            className="post-card-title post-card-title-input"
            value={post.title ?? ''}
            onChange={(e) =>
              onUpdate({ title: e.target.value, slug: slugify(e.target.value) })
            }
            placeholder="Title"
            maxLength={50}
            aria-label="Post title"
          />
        </div>
        <div className="post-card-studio-slug-row">
          <label className="post-card-studio-slug-label">URL slug</label>
          <input
            type="text"
            className="post-card-studio-slug-input"
            value={post.slug ?? ''}
            onChange={(e) => onUpdate({ slug: e.target.value })}
            placeholder={slugify(post.title || '') || 'post'}
            aria-label="URL slug"
          />
        </div>
        <div
          className="post-card-media-wrap-studio"
          onClick={post.media_type === 'Audio' ? undefined : onMediaClick}
          role={post.media_type === 'Audio' ? undefined : 'button'}
          tabIndex={post.media_type === 'Audio' ? undefined : 0}
          onKeyDown={post.media_type === 'Audio' ? undefined : (e) => e.key === 'Enter' && onMediaClick()}
          aria-label={post.media_type === 'Audio' ? undefined : 'Change media'}
        >
          <span className="post-card-media-hint">{postMediaHint}</span>
          {mediaDisplayUrl &&
            (post.media_type === 'Image' || post.media_type === 'Gif') && (
              <PostMediaContainer
                mediaUrl={mediaDisplayUrl}
                mediaType={post.media_type}
                figureClassName="post-card-media"
                videoWrapClassName="post-card-media-video-wrap"
              />
            )}
          {mediaDisplayUrl && post.media_type === 'Video' && (
            <PostMediaContainer
              mediaUrl={mediaDisplayUrl}
              mediaType="Video"
              figureClassName="post-card-media"
              videoWrapClassName="post-card-media-video-wrap"
              videoAttrs={{ controls: true }}
            />
          )}
          {mediaDisplayUrl && post.media_type === 'Audio' && (
            <>
              <figure className="post-card-media post-card-media-audio">
                <audio src={mediaDisplayUrl} controls />
              </figure>
              <button
                type="button"
                className="post-card-media-audio-bar"
                onClick={onMediaClick}
                aria-label="Change media"
              >
                <span className="post-card-media-audio-bar-hint">{postMediaHint}</span>
              </button>
            </>
          )}
          {!mediaDisplayUrl && (
            <div className="post-card-media post-card-media-placeholder" />
          )}
          <span className="post-card-media-hover-overlay" aria-hidden />
          <span className="post-card-media-camera">
            {uploading ? (
              '…'
            ) : (
              <CameraIcon size={20} />
            )}
          </span>
        </div>
        <div className="post-card-studio-excerpt-row">
          <label className="post-card-studio-label">Excerpt (optional)</label>
          <textarea
            className="post-card-studio-excerpt-input post-card-studio-excerpt-input-scroll"
            value={post.excerpt ?? ''}
            onChange={(e) => onUpdate({ excerpt: e.target.value || null })}
            placeholder="Short summary for previews"
            maxLength={255}
            rows={3}
            aria-label="Excerpt"
          />
        </div>
        <div className="post-card-studio-content-row">
          <label className="post-card-studio-label post-card-studio-label-required">
            Content
          </label>
          <RichTextEditor
            value={post.content_text ?? ''}
            onChange={(html) => onUpdate({ content_text: html })}
            placeholder="Write your post..."
          />
        </div>
        <div className="post-card-studio-tier-row">
          <label className="post-card-studio-label">Required tier</label>
          <div
            className="form-tag-chips post-card-studio-tier-chips"
            role="group"
            aria-label="Required tier"
          >
            <button
              type="button"
              className={`tag-chip ${!post.required_tier ? 'active' : ''}`}
              onClick={() => onUpdate({ required_tier: null })}
              aria-pressed={!post.required_tier}
            >
              Public
            </button>
            {visibleTiers.map((t) => (
              <button
                key={t.id}
                type="button"
                className={`tag-chip ${post.required_tier?.id === t.id ? 'active' : ''}`}
                onClick={() =>
                  onUpdate({
                    required_tier: {
                      id: t.id,
                      level: t.level,
                      tier_name: t.tier_name,
                    },
                  })
                }
                aria-pressed={post.required_tier?.id === t.id}
              >
                {t.tier_name || `Level ${t.level}`}
              </button>
            ))}
          </div>
        </div>
        <footer className="post-card-footer post-card-footer-studio">
          <span className="post-card-stat" title="Views">
            <span className="post-card-stat-icon" aria-hidden>
              👁
            </span>{' '}
            {post.views_count ?? 0}
          </span>
          <span className="post-card-stat" title="Likes">
            <span className="post-card-stat-icon" aria-hidden>
              ♥
            </span>{' '}
            {post.likes_count ?? 0}
          </span>
          <span className="post-card-stat">
            <span className="post-card-stat-icon" aria-hidden>
              💬
            </span>{' '}
            {post.comments_count ?? 0}
          </span>
          <span className="post-card-stat post-card-stat-bookmark">
            <span className="post-card-stat-icon" aria-hidden>
              🔖
            </span>{' '}
            0
          </span>
          {post.required_tier && (
            <span className="post-card-stat">
              {post.required_tier.tier_name}
            </span>
          )}
          {isPlaceholder && <span className="post-card-seen">Draft</span>}
        </footer>
      </div>
    </article>
  );
}
