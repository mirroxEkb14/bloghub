import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function UserCheckIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <circle cx="12" cy="7" r="4" />
      <path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2" />
      <path d="M16 19l2 2 4-5" className="profile-stat-icon-check" />
    </svg>
  );
}
