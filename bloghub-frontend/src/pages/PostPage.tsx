import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { postsApi, type Post } from '../api/client';

export default function PostPage() {
  const { slug, postSlug } = useParams<{ slug: string; postSlug: string }>();
  const [post, setPost] = useState<Post | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!slug || !postSlug) return;
    let cancelled = false;
    (async () => {
      setLoading(true);
      setError(null);
      try {
        const data = await postsApi.getBySlug(slug, postSlug);
        if (!cancelled) setPost(data);
      } catch (e) {
        if (!cancelled) setError(e instanceof Error ? e.message : 'Failed to load post');
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [slug, postSlug]);

  if (loading) {
    return (
      <div className="page-center">
        <p className="form-subtitle">Loading…</p>
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
          {post.created_at && new Date(post.created_at).toLocaleDateString(undefined, { dateStyle: 'medium' })}
        </p>
      )}
      {post.media_url && post.media_type === 'Image' && (
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
    </article>
  );
}
