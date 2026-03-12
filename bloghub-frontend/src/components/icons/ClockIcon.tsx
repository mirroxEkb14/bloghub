import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function ClockIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <circle cx="12" cy="12" r="10" />
      <polyline points="12 6 12 12 16 14" />
    </svg>
  );
}
