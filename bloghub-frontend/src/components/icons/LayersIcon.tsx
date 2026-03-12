import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function LayersIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <rect x="4" y="4" width="16" height="4" rx="1" />
      <rect x="4" y="10" width="16" height="4" rx="1" />
      <rect x="4" y="16" width="16" height="4" rx="1" />
    </svg>
  );
}
