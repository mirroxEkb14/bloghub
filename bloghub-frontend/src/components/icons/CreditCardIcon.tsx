import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function CreditCardIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <rect x="2" y="5" width="20" height="14" rx="2" />
      <line x1="2" y1="10" x2="22" y2="10" />
    </svg>
  );
}
