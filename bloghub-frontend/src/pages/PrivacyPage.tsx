export default function PrivacyPage() {
  return (
    <div className="page-center">
      <div className="card legal-page">
        <h1 className="form-title">Privacy Policy</h1>
        <p className="form-subtitle">Last updated: March 2026</p>
        <div className="legal-content">
          <p>
            <strong>Local use only.</strong> BlogHub is currently intended for local use only (e.g. on your
            own machine or a local network). Data is stored on the instance you run and is not sent to
            external services except those you explicitly configure (such as payment providers if you
            enable them).
          </p>
          <p>
            <strong>Data we store.</strong> When you register and use BlogHub, we store account data (name,
            username, email, password hash, phone if provided), your acceptance of these terms and the
            privacy policy, creator profile data, posts, comments, likes, and subscription information.
            Payment data is handled by the payment provider you configure (e.g. Stripe), not stored by
            BlogHub beyond what is needed to link subscriptions to your account.
          </p>
          <p>
            <strong>How we use it.</strong> Stored data is used to run the application: to identify you,
            show your profile and posts, manage comments and likes, and handle subscriptions. We do not
            sell or share your data with third parties for marketing. In a local setup, all of this
            stays on your own server or machine.
          </p>
          <p>
            <strong>Security.</strong> Passwords are hashed; we do not store plain-text passwords. You are
            responsible for securing the machine and database where BlogHub runs. For local use, access
            is limited to whoever can reach that environment.
          </p>
          <p>
            <strong>Changes.</strong> This policy may be updated. Continued use of BlogHub after changes
            constitutes acceptance of the updated policy. If you use BlogHub in a production or
            publicly hosted environment later, you may need to review and expand this policy to match
            that use.
          </p>
        </div>
      </div>
    </div>
  );
}
