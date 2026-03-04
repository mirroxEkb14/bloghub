import type { InputHTMLAttributes } from 'react';

const EnvelopeIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="20"
    height="20"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth="2"
    strokeLinecap="round"
    strokeLinejoin="round"
    aria-hidden
  >
    <rect width="20" height="16" x="2" y="4" rx="2" />
    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
  </svg>
);

const UserIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="20"
    height="20"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth="2"
    strokeLinecap="round"
    strokeLinejoin="round"
    aria-hidden
  >
    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
    <circle cx="12" cy="7" r="4" />
  </svg>
);

const icons = {
  email: EnvelopeIcon,
  user: UserIcon,
} as const;

type IconType = keyof typeof icons;

type InputWithIconProps = Omit<InputHTMLAttributes<HTMLInputElement>, 'className'> & {
  label: string;
  icon: IconType;
  error?: string;
};

export default function InputWithIcon({
  id,
  label,
  icon,
  error,
  ...inputProps
}: InputWithIconProps) {
  const Icon = icons[icon];

  return (
    <div className="form-group">
      <label htmlFor={id}>{label}</label>
      <div className={`input-with-icon${error ? ' has-error' : ''}`}>
        <span className="input-with-icon-left" aria-hidden>
          <Icon />
        </span>
        <input id={id} className="input-with-icon-input" aria-invalid={!!error} aria-describedby={error ? `${id}-error` : undefined} {...inputProps} />
      </div>
      {error && (
        <p id={`${id}-error`} className="field-error" role="alert">
          {error}
        </p>
      )}
    </div>
  );
}
