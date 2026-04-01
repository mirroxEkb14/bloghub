import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function InfoIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <circle cx="12" cy="12" r="10" />
      <path d="M12 16v-4" />
      <path d="M12 8h.01" />
    </svg>
  );
}
