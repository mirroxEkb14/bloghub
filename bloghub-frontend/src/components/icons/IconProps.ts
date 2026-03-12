export type IconProps = {
  className?: string;
  size?: number;
  ariaHidden?: boolean;
};

const defaultSize = 24;

export function iconSvgProps(props: IconProps) {
  const { className, size = defaultSize, ariaHidden = true } = props;
  return {
    width: size,
    height: size,
    viewBox: '0 0 24 24',
    fill: 'none' as const,
    stroke: 'currentColor',
    strokeWidth: 2,
    strokeLinecap: 'round' as const,
    strokeLinejoin: 'round' as const,
    'aria-hidden': ariaHidden,
    ...(className ? { className } : {}),
  };
}

export const DEFAULT_ICON_SIZE = defaultSize;
