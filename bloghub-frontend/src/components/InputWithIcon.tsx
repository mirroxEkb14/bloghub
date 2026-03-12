import type { InputHTMLAttributes } from 'react';
import { EnvelopeIcon, PersonIcon } from './icons';

const icons = {
  email: () => <EnvelopeIcon size={20} />,
  user: () => <PersonIcon size={20} />,
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
