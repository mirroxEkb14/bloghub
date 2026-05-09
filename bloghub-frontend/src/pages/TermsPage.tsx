export default function TermsPage() {
  return (
    <div className="page-center">
      <div className="card legal-page">
        <h1 className="form-title">Terms of Service</h1>
        <p className="form-subtitle">Last updated: May 2026</p>
        <div className="legal-content">
          <p>
            <strong>Scope.</strong> BlogHub is a blog and creator platform that lets you publish posts,
            run a creator profile, and offer paid tiers via subscriptions. These terms apply to your use
            of the application.
          </p>
          <p>
            <strong>Local use only.</strong> At this stage, BlogHub is intended for local use only (e.g. on
            your own machine or a local network). It is not intended for production or public hosting.
            Use it at your own risk in that context.
          </p>
          <p>
            <strong>Acceptable use.</strong> You must not use BlogHub to publish illegal content, harass
            others, or violate applicable laws. You are responsible for the content you publish and for
            keeping your account credentials secure.
          </p>
          <p>
            <strong>Subscriptions.</strong> Paid subscriptions are managed per creator and per tier.
            When you upgrade to a higher tier, no refund is given for the previous tier. You can cancel your
            own subscription at any time (no refunds); you may choose to end access immediately or at the
            current period end.
          </p>
          <p>
            <strong>No warranty.</strong> The application is provided “as is” without warranty of any kind.
            The developers are not liable for any loss or damage arising from your use of BlogHub,
            especially in a local or non-production setting.
          </p>
          <p>
            <strong>Changes.</strong> These terms may be updated from time to time. Continued use of the
            application after changes constitutes acceptance of the updated terms.
          </p>
        </div>
      </div>
    </div>
  );
}
