import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function MoonIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
    </svg>
  );
}
