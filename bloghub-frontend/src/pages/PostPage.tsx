import { useCallback, useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ApiError, commentsApi, postsApi, type Comment, type Post } from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import LoadingPage from '../components/LoadingPage';
import { formatDateTimeLocal } from '../utils/date';

type SubscriptionRequiredBody = {
  requires_subscription?: boolean;
  required_tier?: { id: number; tier_name: string; level: number };
};

export default function PostPage() {
  const { slug, postSlug } = useParams<{ slug: string; postSlug: string }>();
  const { user } = useAuth();
  const [post, setPost] = useState<Post | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [subscriptionRequired, setSubscriptionRequired] = useState<{ tierName: string } | null>(null);
  const [comments, setComments] = useState<Comment[]>([]);
  const [commentsLoading, setCommentsLoading] = useState(false);
  const [commentError, setCommentError] = useState<string | null>(null);
  const [newCommentText, setNewCommentText] = useState('');
  const [submittingComment, setSubmittingComment] = useState(false);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [slug, postSlug]);

  useEffect(() => {
    if (!slug || !postSlug) return;
    let cancelled = false;
    (async () => {
      setLoading(true);
      setError(null);
      setSubscriptionRequired(null);
      try {
        const data = await postsApi.getBySlug(slug, postSlug);
        if (!cancelled) setPost(data);
      } catch (e) {
        if (cancelled) return;
        if (e instanceof ApiError && e.status === 403) {
          const body = e.body as SubscriptionRequiredBody | undefined;
          if (body?.requires_subscription && body?.required_tier) {
            setSubscriptionRequired({ tierName: body.required_tier.tier_name });
            return;
          }
        }
        setError(e instanceof Error ? e.message : 'Failed to load post');
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [slug, postSlug]);

  useEffect(() => {
    if (!user || !slug || !postSlug || !post) return;
    postsApi.recordView(slug, postSlug).catch(() => { /* ignore */ });
  }, [user, slug, postSlug, post]);

  const fetchComments = useCallback(async () => {
    if (!slug || !postSlug) return;
    setCommentsLoading(true);
    setCommentError(null);
    try {
      const list = await commentsApi.list(slug, postSlug);
      setComments(list);
    } catch (e) {
      setCommentError(e instanceof Error ? e.message : 'Failed to load comments');
    } finally {
      setCommentsLoading(false);
    }
  }, [slug, postSlug]);

  useEffect(() => {
    if (post) fetchComments();
  }, [post, fetchComments]);

  const handleSubmitComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!slug || !postSlug || !user || !newCommentText.trim() || submittingComment) return;
    setSubmittingComment(true);
    setCommentError(null);
    try {
      const created = await commentsApi.create(slug, postSlug, { content_text: newCommentText.trim() });
      setComments((prev) => [...prev, created]);
      setNewCommentText('');
    } catch (e) {
      setCommentError(e instanceof Error ? e.message : 'Failed to post comment');
    } finally {
      setSubmittingComment(false);
    }
  };

  if (loading) {
    return <LoadingPage message="Loading post..." />;
  }

  if (subscriptionRequired) {
    return (
      <div className="page-center">
        <div className="card" style={{ maxWidth: 420 }}>
          <h1 className="form-title">Subscriber-only post</h1>
          <p className="form-subtitle">
            This post is for <strong>{subscriptionRequired.tierName}</strong> subscribers. Subscribe to this creator to read it
          </p>
          {slug && (
            <Link to={`/creator/${slug}#profile-tiers`} className="btn btn-primary" style={{ display: 'inline-block', marginTop: '1rem' }}>
              View subscription tiers
            </Link>
          )}
        </div>
      </div>
    );
  }

  if (error || !post) {
    return (
      <div className="page-center">
        <div className="card" style={{ maxWidth: 420 }}>
          <h1 className="form-title">Post not found</h1>
          <p className="form-subtitle">{error ?? 'This post may have been removed.'}</p>
          {slug && (
            <Link to={`/creator/${slug}`} className="btn btn-primary" style={{ display: 'inline-block', marginTop: '1rem' }}>
              Back to creator
            </Link>
          )}
        </div>
      </div>
    );
  }

  return (
    <article className="post-page">
      <div className="post-page-header">
        {slug && (
          <Link to={`/creator/${slug}`} className="post-page-back">
            ← Back to creator
          </Link>
        )}
        {post.required_tier && (
          <span className="post-tier-badge" title={`Tier: ${post.required_tier.tier_name}`}>
            {post.required_tier.tier_name}
          </span>
        )}
      </div>
      <h1 className="post-page-title">{post.title}</h1>
      {(post.created_at || post.updated_at) && (
        <p className="post-page-meta">
          {post.created_at && formatDateTimeLocal(post.created_at)}
        </p>
      )}
      {post.media_url && (post.media_type === 'Image' || post.media_type === 'Gif') && (
        <figure className="post-media post-media-image">
          <img src={post.media_url} alt="" />
        </figure>
      )}
      {post.media_url && (post.media_type === 'Audio' || post.media_type === 'Video') && (
        <figure className="post-media">
          {post.media_type === 'Video' ? (
            <video src={post.media_url} controls />
          ) : (
            <audio src={post.media_url} controls />
          )}
        </figure>
      )}
      {post.content_text && (
        <div className="post-page-content">
          {post.content_text.split('\n').map((line, i) => (
            <p key={i}>{line || '\u00A0'}</p>
          ))}
        </div>
      )}

      <section className="comments-section" aria-label="Comments">
        <h2 className="comments-section-title">
          Comments {comments.length > 0 && `(${comments.length})`}
        </h2>
        {commentsLoading && <p className="comments-loading">Loading comments...</p>}
        {commentError && (
          <p className="comments-error" role="alert">
            {commentError}
          </p>
        )}
        {!commentsLoading && comments.length === 0 && !commentError && (
          <p className="comments-empty">No comments yet. Be the first to comment</p>
        )}
        <ul className="comments-list">
          {comments.map((c) => {
            const displayName = c.user?.name ?? c.user?.username ?? 'User';
            const initial = displayName.charAt(0).toUpperCase();
            const avatarUrl = c.user?.avatar_url;
            return (
            <li key={c.id} className="comment-item">
              <div className="comment-item-header">
                <div className="comment-avatar" aria-hidden>
                  {avatarUrl ? (
                    <img src={avatarUrl} alt="" width={40} height={40} />
                  ) : (
                    <span className="comment-avatar-initial">{initial}</span>
                  )}
                </div>
                <div className="comment-item-meta">
                  <span className="comment-author">{displayName}</span>
                  <time className="comment-date" dateTime={c.created_at} title={formatDateTimeLocal(c.created_at)}>
                    {formatDateTimeLocal(c.created_at)}
                  </time>
                </div>
              </div>
              <div className="comment-body">
                {(typeof c.content_text === 'string' ? c.content_text : '').split('\n').map((line, i) => (
                  <p key={i}>{line || '\u00A0'}</p>
                ))}
              </div>
            </li>
          );
          })}
        </ul>
        {user && (
          <form className="comment-form" onSubmit={handleSubmitComment}>
            <label htmlFor="new-comment" className="comment-form-label">
              Add a comment
            </label>
            <textarea
              id="new-comment"
              className="comment-form-textarea"
              value={newCommentText}
              onChange={(e) => setNewCommentText(e.target.value)}
              placeholder="Write your comment..."
              rows={3}
              maxLength={2000}
              disabled={submittingComment}
            />
            <div className="comment-form-actions">
              <span className="comment-form-count">{newCommentText.length}/2000</span>
              <button type="submit" className="btn btn-primary" disabled={!newCommentText.trim() || submittingComment}>
                {submittingComment ? 'Posting...' : 'Post comment'}
              </button>
            </div>
          </form>
        )}
        {!user && (
          <p className="comments-sign-in">
            <Link to="/login">Sign in</Link> to leave a comment
          </p>
        )}
      </section>
    </article>
  );
}
