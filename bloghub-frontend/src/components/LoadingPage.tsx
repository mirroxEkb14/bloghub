type LoadingPageProps = {
  message?: string;
};

export default function LoadingPage({ message = 'Loading...' }: LoadingPageProps) {
  return (
    <div className="loading-page">
      <div className="loading-page-spinner" aria-hidden />
      <p className="loading-page-message">{message}</p>
    </div>
  );
}
