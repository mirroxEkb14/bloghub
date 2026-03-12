import type { IconProps } from './IconProps';

export default function UnlockIcon(props: IconProps) {
  return (
    <svg
      viewBox="0 0 36 36"
      fill="currentColor"
      stroke="currentColor"
      strokeWidth="1.2"
      strokeLinejoin="round"
      className={props.className}
      aria-hidden
    >
      <path d="M26,2a8.2,8.2,0,0,0-8,8.36V15H2V32a2,2,0,0,0,2,2H22a2,2,0,0,0,2-2V15H20V10.36A6.2,6.2,0,0,1,26,4a6.2,6.2,0,0,1,6,6.36v6.83a1,1,0,0,0,2,0V10.36A8.2,8.2,0,0,0,26,2ZM7,17L20,17Q22,17,22,20L22,29Q22,32,19,32L7,32Q4,32,4,29L4,20Q4,17,7,17Z" />
    </svg>
  );
}
