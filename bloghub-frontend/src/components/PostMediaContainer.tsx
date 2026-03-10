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
  mediaBlurClassName?: string;
};

export default function PostMediaContainer({
  mediaUrl,
  mediaType,
  figureClassName,
  videoWrapClassName = 'post-card-media-video-wrap',
  videoAttrs = {},
  as: Wrapper = 'figure',
  children,
  mediaBlurClassName,
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

  const mediaContent =
    mediaType === 'Image' || mediaType === 'Gif' ? (
      <img src={mediaUrl} alt="" onLoad={handleImageLoad} />
    ) : (
      <div className={videoWrapClassName}>
        <video
          src={mediaUrl}
          {...videoAttrs}
          onLoadedMetadata={handleVideoLoadedMetadata}
        />
      </div>
    );

  const wrappedMedia = mediaBlurClassName ? (
    <div className={mediaBlurClassName}>{mediaContent}</div>
  ) : (
    mediaContent
  );

  return (
    <Wrapper className={figureClassName} style={{ aspectRatio }}>
      {wrappedMedia}
      {children}
    </Wrapper>
  );
}
