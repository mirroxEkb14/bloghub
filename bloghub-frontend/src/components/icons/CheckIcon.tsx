import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function CheckIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <polyline points="20 6 9 17 4 12" />
    </svg>
  );
}
