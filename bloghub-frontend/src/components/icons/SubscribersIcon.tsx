import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function SubscribersIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
      <circle cx="12" cy="7" r="4" />
      <path d="M12 3v4" />
      <path d="M12 15v4" />
      <path d="M8 11h8" />
    </svg>
  );
}
