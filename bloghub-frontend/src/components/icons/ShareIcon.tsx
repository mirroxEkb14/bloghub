import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function ShareIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <circle cx="6" cy="12" r="3" />
      <circle cx="18" cy="5" r="3" />
      <circle cx="18" cy="19" r="3" />
      <line x1="6" y1="12" x2="18" y2="5" />
      <line x1="6" y1="12" x2="18" y2="19" />
    </svg>
  );
}
