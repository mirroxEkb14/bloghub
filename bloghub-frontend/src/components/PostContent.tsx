import DOMPurify from 'dompurify';
import '../styles/post-content.css';

const ALLOWED_TAGS = [
  'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'h1', 'h2', 'h3',
  'a', 'blockquote', 'code', 'pre', 'span', 'div',
];
const ALLOWED_ATTR = ['href', 'target', 'rel', 'class'];

type PostContentProps = {
  html: string | null;
  className?: string;
};

function looksLikeHtml(s: string): boolean {
  return /<[a-z][\s\S]*>/i.test(s);
}

export function stripHtml(html: string): string {
  return html.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
}

export default function PostContent({ html, className }: PostContentProps) {
  if (html == null || html.trim() === '') return null;

  if (!looksLikeHtml(html)) {
    return (
      <div className={`post-content post-content-plain ${className ?? ''}`.trim()}>
        {html.split('\n').map((line, i) => (
          <p key={i}>{line || '\u00A0'}</p>
        ))}
      </div>
    );
  }

  const sanitized = DOMPurify.sanitize(html, {
    ALLOWED_TAGS,
    ALLOWED_ATTR,
    ADD_ATTR: ['target'],
  });

  return (
    <div
      className={`post-content post-content-html ${className ?? ''}`.trim()}
      dangerouslySetInnerHTML={{ __html: sanitized }}
    />
  );
}
