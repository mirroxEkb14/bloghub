import type { IconProps } from './IconProps';
import { iconSvgProps } from './IconProps';

export default function DocumentDollarIcon(props: IconProps) {
  return (
    <svg {...iconSvgProps(props)}>
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
      <polyline points="14 2 14 8 20 8" />
      <line x1="16" y1="12" x2="8" y2="12" />
      <line x1="16" y1="16" x2="8" y2="16" />
      <line x1="10" y1="8" x2="8" y2="8" />
    </svg>
  );
}
