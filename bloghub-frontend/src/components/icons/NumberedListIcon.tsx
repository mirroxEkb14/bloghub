import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function NumberedListIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)} strokeWidth="1.5">
      <text x="3" y="8" fontSize="6" fontFamily="sans-serif" fill="currentColor">1</text>
      <line x1="10" y1="6" x2="20" y2="6" />
      <text x="3" y="14" fontSize="6" fontFamily="sans-serif" fill="currentColor">2</text>
      <line x1="10" y1="12" x2="20" y2="12" />
      <text x="3" y="20" fontSize="6" fontFamily="sans-serif" fill="currentColor">3</text>
      <line x1="10" y1="18" x2="20" y2="18" />
    </svg>
  );
}
