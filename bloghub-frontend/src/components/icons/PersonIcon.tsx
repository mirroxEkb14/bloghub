import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function PersonIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
      <circle cx="12" cy="7" r="4" />
    </svg>
  );
}
