import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function LockCircleIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <rect x="4" y="11" width="16" height="10" rx="2.5" ry="2.5" />
      <path d="M8 11V7.5a4 4 0 1 1 8 0V11" />
      <circle cx="12" cy="15.5" r="1.25" fill="currentColor" />
    </svg>
  );
}
