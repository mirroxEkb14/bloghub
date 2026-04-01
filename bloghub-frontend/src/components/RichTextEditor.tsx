import { useEffect, useRef, useState } from 'react';
import { useEditor, EditorContent } from '@tiptap/react';
import { createPortal } from 'react-dom';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Strike from '@tiptap/extension-strike';
import Link from '@tiptap/extension-link';
import { NumberedListIcon } from './icons';
import '../styles/rich-text-editor.css';

type RichTextEditorProps = {
  value: string;
  onChange: (html: string) => void;
  placeholder?: string;
  id?: string;
};

const DEFAULT_HINT = 'Write your post...';

export default function RichTextEditor({ value, onChange, placeholder = DEFAULT_HINT, id }: RichTextEditorProps) {
  const onChangeRef = useRef(onChange);
  onChangeRef.current = onChange;
  const [isFocused, setIsFocused] = useState(false);
  const [linkDialogOpen, setLinkDialogOpen] = useState(false);
  const [linkDialogUrl, setLinkDialogUrl] = useState('');
  const [linkDialogHadLink, setLinkDialogHadLink] = useState(false);
  const linkInputRef = useRef<HTMLInputElement>(null);

  const editor = useEditor({
    extensions: [
      StarterKit.configure({
        heading: false,
        codeBlock: false,
        blockquote: false,
        horizontalRule: false,
      }),
      Underline,
      Strike,
      Link.configure({
        openOnClick: false,
        HTMLAttributes: { target: '_blank', rel: 'noopener noreferrer' },
      }),
    ],
    content: (value ?? '') || '<p></p>',
    editorProps: {
      attributes: {
        ...(id ? { id } : {}),
        class: 'rich-text-editor-inner',
      },
      handleDOMEvents: {
        focus: () => {
          setIsFocused(true);
        },
        blur: () => {
          setIsFocused(false);
        },
      },
    },
    onUpdate: ({ editor }) => {
      onChangeRef.current(editor.getHTML());
    },
  });

  useEffect(() => {
    if (!editor) return;
    const current = editor.getHTML();
    const next = value?.trim() || '<p></p>';
    if (next !== current) {
      editor.commands.setContent(next, { emitUpdate: false });
    }
  }, [value, editor]);

  const handleContentAreaClick = () => {
    setIsFocused(true);
    editor?.commands.focus();
  };

  const isEmpty = editor.isEmpty;
  const showHint = isEmpty && !isFocused;

  const openLinkDialog = () => {
    if (!editor) return;
    const previousUrl = editor.getAttributes('link').href ?? '';
    setLinkDialogUrl(previousUrl);
    setLinkDialogHadLink(!!previousUrl);
    setLinkDialogOpen(true);
    setTimeout(() => linkInputRef.current?.focus(), 0);
  };

  const closeLinkDialog = () => {
    setLinkDialogOpen(false);
    setLinkDialogUrl('');
    setLinkDialogHadLink(false);
    editor?.commands.focus();
  };

  const applyLink = () => {
    if (!editor) return;
    const url = linkDialogUrl.trim();
    if (url === '') {
      editor.chain().focus().unsetLink().run();
    } else {
      const href = /^[a-zA-Z][a-zA-Z0-9+.-]*:/.test(url) ? url : `https://${url}`;
      editor.chain().focus().setLink({ href }).run();
    }
    closeLinkDialog();
  };

  const removeLink = () => {
    editor?.chain().focus().unsetLink().run();
    closeLinkDialog();
  };

  useEffect(() => {
    if (!linkDialogOpen) return;
    const onKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        e.preventDefault();
        closeLinkDialog();
      }
    };
    document.addEventListener('keydown', onKeyDown);
    return () => document.removeEventListener('keydown', onKeyDown);
  }, [linkDialogOpen]);

  if (!editor) return null;

  return (
    <div className="rich-text-editor">
      <div className="rich-text-editor-toolbar" role="toolbar" aria-label="Formatting">
        <button
          type="button"
          className={`rich-text-editor-btn ${editor.isActive('bold') ? 'active' : ''}`}
          onClick={() => editor.chain().focus().toggleBold().run()}
          title="Bold"
          aria-label="Bold"
        >
          <strong>B</strong>
        </button>
        <button
          type="button"
          className={`rich-text-editor-btn ${editor.isActive('italic') ? 'active' : ''}`}
          onClick={() => editor.chain().focus().toggleItalic().run()}
          title="Italic"
          aria-label="Italic"
        >
          <em>I</em>
        </button>
        <button
          type="button"
          className={`rich-text-editor-btn ${editor.isActive('underline') ? 'active' : ''}`}
          onClick={() => editor.chain().focus().toggleUnderline().run()}
          title="Underline"
          aria-label="Underline"
        >
          <span className="rich-text-editor-underline">U</span>
        </button>
        <button
          type="button"
          className={`rich-text-editor-btn ${editor.isActive('strike') ? 'active' : ''}`}
          onClick={() => editor.chain().focus().toggleStrike().run()}
          title="Strikethrough"
          aria-label="Strikethrough"
        >
          <span className="rich-text-editor-strike">S</span>
        </button>
        <span className="rich-text-editor-toolbar-divider" aria-hidden />
        <button
          type="button"
          className={`rich-text-editor-btn ${editor.isActive('link') ? 'active' : ''}`}
          onClick={openLinkDialog}
          title="Link"
          aria-label="Link"
        >
          <span className="rich-text-editor-icon-link" aria-hidden />
        </button>
        <span className="rich-text-editor-toolbar-divider" aria-hidden />
        <button
          type="button"
          className={`rich-text-editor-btn ${editor.isActive('bulletList') ? 'active' : ''}`}
          onClick={() => editor.chain().focus().toggleBulletList().run()}
          title="Bullet list"
          aria-label="Bullet list"
        >
          <span className="rich-text-editor-icon-bullet-list" aria-hidden />
        </button>
        <button
          type="button"
          className={`rich-text-editor-btn ${editor.isActive('orderedList') ? 'active' : ''}`}
          onClick={() => editor.chain().focus().toggleOrderedList().run()}
          title="Numbered list"
          aria-label="Numbered list"
        >
          <span className="rich-text-editor-icon-numbered-list" aria-hidden>
            <NumberedListIcon />
          </span>
        </button>
      </div>
      <div
        className="rich-text-editor-content-wrap"
        onClick={handleContentAreaClick}
        role="presentation"
      >
        {showHint && (
          <div className="rich-text-editor-hint" aria-hidden>
            {placeholder}
          </div>
        )}
        <EditorContent editor={editor} />
      </div>

      {linkDialogOpen &&
        createPortal(
          <div
            className="rich-text-editor-link-backdrop"
            onClick={closeLinkDialog}
            role="presentation"
          >
            <div
              className="rich-text-editor-link-dialog"
              onClick={(e) => e.stopPropagation()}
              role="dialog"
              aria-modal="true"
              aria-labelledby="link-dialog-title"
            >
              <h2 id="link-dialog-title" className="rich-text-editor-link-dialog-title">
                {linkDialogHadLink ? 'Edit link' : 'Insert link'}
              </h2>
              <div className="rich-text-editor-link-dialog-body">
                <label htmlFor="link-dialog-url" className="rich-text-editor-link-dialog-label">
                  URL
                </label>
                <input
                  ref={linkInputRef}
                  id="link-dialog-url"
                  type="url"
                  className="rich-text-editor-link-dialog-input"
                  placeholder="https://example.com"
                  value={linkDialogUrl}
                  onChange={(e) => setLinkDialogUrl(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      e.preventDefault();
                      applyLink();
                    }
                  }}
                  autoComplete="url"
                />
                <div className="rich-text-editor-link-dialog-actions">
                  <button type="button" className="btn btn-primary" onClick={applyLink}>
                    Apply
                  </button>
                  <button type="button" className="btn btn-secondary" onClick={closeLinkDialog}>
                    Cancel
                  </button>
                  {linkDialogHadLink && (
                    <button
                      type="button"
                      className="btn btn-secondary rich-text-editor-link-dialog-remove"
                      onClick={removeLink}
                    >
                      Remove link
                    </button>
                  )}
                </div>
              </div>
            </div>
          </div>,
          document.body
        )}
    </div>
  );
}
