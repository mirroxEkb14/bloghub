import type { Tag } from '../../api/client';

export function TagsModal({
  tags,
  selectedIds,
  onToggle,
  onClose,
}: {
  tags: Tag[];
  selectedIds: number[];
  onToggle: (id: number) => void;
  onClose: () => void;
}) {
  return (
    <div
      className="tier-delete-overlay"
      role="dialog"
      aria-modal="true"
      aria-labelledby="tags-modal-title"
      onClick={onClose}
    >
      <div
        className="tier-delete-card card tags-modal-card"
        onClick={(e) => e.stopPropagation()}
      >
        <h2 id="tags-modal-title" className="form-title">
          Add or remove tags
        </h2>
        <p className="form-subtitle" style={{ marginBottom: '1rem' }}>
          Click a tag to add or remove it from your profile
        </p>
        <div className="tags-modal-list" role="group" aria-label="Tags">
          {tags.map((tag) => {
            const selected = selectedIds.includes(tag.id);
            return (
              <button
                key={tag.id}
                type="button"
                className={`tag-chip ${selected ? 'active' : ''}`}
                onClick={() => onToggle(tag.id)}
                aria-pressed={selected}
              >
                {tag.name}
              </button>
            );
          })}
        </div>
        {tags.length === 0 && (
          <p className="form-hint">No tags available</p>
        )}
        <div style={{ marginTop: '1rem' }}>
          <button
            type="button"
            className="btn btn-primary"
            onClick={onClose}
          >
            Done
          </button>
        </div>
      </div>
    </div>
  );
}
