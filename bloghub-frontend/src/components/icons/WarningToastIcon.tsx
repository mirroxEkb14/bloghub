import type { IconProps } from './IconProps';

export default function WarningToastIcon(props: IconProps) {
  const size = props.size ?? 20;
  const { className, ariaHidden = true } = props;
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 20 20"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden={ariaHidden}
      className={className}
    >
      <path d="M10 3.5L2 16.5h16L10 3.5z" stroke="currentColor" strokeWidth="2" strokeLinejoin="round" fill="none" />
      <path d="M10 8v3M10 13v1" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
    </svg>
  );
}
