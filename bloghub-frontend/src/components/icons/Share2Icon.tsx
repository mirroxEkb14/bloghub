import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function Share2Icon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <circle cx="6" cy="12" r="3" />
      <circle cx="18" cy="12" r="3" />
      <line x1="6" y1="12" x2="18" y2="12" />
    </svg>
  );
}
