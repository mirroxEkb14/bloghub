import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function LockIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
      <path d="M7 11V7a5 5 0 0 1 10 0v4" />
    </svg>
  );
}
