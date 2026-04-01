import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function XCircleIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <circle cx="12" cy="12" r="10" />
      <path d="m15 9-6 6" />
      <path d="m9 9 6 6" />
    </svg>
  );
}
