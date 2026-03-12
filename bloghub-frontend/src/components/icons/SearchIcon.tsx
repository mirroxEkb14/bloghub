import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function SearchIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <circle cx="11" cy="11" r="8" />
      <path d="m21 21-4.3-4.3" />
    </svg>
  );
}
