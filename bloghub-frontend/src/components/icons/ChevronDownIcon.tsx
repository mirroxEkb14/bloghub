import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function ChevronDownIcon(props: IconProps) {
  const size = props.size ?? 16;
  return (
    <svg {...iconSvgProps({ ...props, size })}>
      <polyline points="6 9 12 15 18 9" />
    </svg>
  );
}
