import { useCallback, useState } from 'react';

const DEFAULT_RATIO = 16 / 9;

type PostMediaContainerProps = {
  mediaUrl: string;
  mediaType: 'Image' | 'Gif' | 'Video';
  figureClassName: string;
  videoWrapClassName?: string;
  videoAttrs?: React.ComponentPropsWithoutRef<'video'>;
  as?: 'figure' | 'div';
  children?: React.ReactNode;
};

export default function PostMediaContainer({
  mediaUrl,
  mediaType,
  figureClassName,
  videoWrapClassName = 'post-card-media-video-wrap',
  videoAttrs = {},
  as: Wrapper = 'figure',
  children,
}: PostMediaContainerProps) {
  const [aspectRatio, setAspectRatio] = useState<string>('16 / 9');

  const applyRatio = useCallback((width: number, height: number) => {
    if (height <= 0) return;
    const ratio = width / height;
    if (ratio > DEFAULT_RATIO) {
      setAspectRatio(`${width} / ${height}`);
    }
  }, []);

  const handleImageLoad = useCallback(
    (e: React.SyntheticEvent<HTMLImageElement>) => {
      const img = e.currentTarget;
      if (img.naturalWidth && img.naturalHeight) {
        applyRatio(img.naturalWidth, img.naturalHeight);
      }
    },
    [applyRatio]
  );

  const handleVideoLoadedMetadata = useCallback(
    (e: React.SyntheticEvent<HTMLVideoElement>) => {
      const video = e.currentTarget;
      if (video.videoWidth && video.videoHeight) {
        applyRatio(video.videoWidth, video.videoHeight);
      }
    },
    [applyRatio]
  );

  if (mediaType === 'Image' || mediaType === 'Gif') {
    return (
      <Wrapper className={figureClassName} style={{ aspectRatio }}>
        <img src={mediaUrl} alt="" onLoad={handleImageLoad} />
        {children}
      </Wrapper>
    );
  }

  return (
    <Wrapper className={figureClassName} style={{ aspectRatio }}>
      <div className={videoWrapClassName}>
        <video
          src={mediaUrl}
          {...videoAttrs}
          onLoadedMetadata={handleVideoLoadedMetadata}
        />
      </div>
      {children}
    </Wrapper>
  );
}
