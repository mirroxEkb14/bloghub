import { useState, useRef, useEffect } from 'react';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { authApi } from '../api/client';
import { useAuth } from '../contexts/AuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { useToast } from '../contexts/ToastContext';
import AcceptLegalModal from './AcceptLegalModal';
import {
  HomeIcon,
  ExploreIcon,
  PersonIcon,
  GlobeIcon,
  LockIcon,
  CreditCardIcon,
  DocumentDollarIcon,
  LayersIcon,
  ShieldIcon,
  FileTextIcon,
  LogOutIcon,
  Share2Icon,
  UserPlusIcon,
  UserCheckIcon,
  EditIcon,
  FilePlusIcon,
  SunIcon,
  MoonIcon,
  ChevronDownIcon,
} from './icons';

const navIconSize = 22;

const Icons = {
  Home: () => <HomeIcon size={navIconSize} />,
  Explore: () => <ExploreIcon size={navIconSize} />,
  MyPage: () => <PersonIcon size={navIconSize} />,
  PublicPosts: () => <GlobeIcon size={navIconSize} />,
  TierPosts: () => <LockIcon size={navIconSize} />,
  Memberships: () => <CreditCardIcon size={navIconSize} />,
  Following: () => <UserCheckIcon size={navIconSize} />,
  Supporting: () => <DocumentDollarIcon size={navIconSize} />,
  Billings: () => <DocumentDollarIcon size={navIconSize} />,
  Tiers: () => <LayersIcon size={navIconSize} />,
  Privacy: () => <ShieldIcon size={navIconSize} />,
  Terms: () => <FileTextIcon size={navIconSize} />,
  Logout: () => <LogOutIcon size={navIconSize} />,
  Social: () => <Share2Icon size={navIconSize} />,
  Register: () => <UserPlusIcon size={navIconSize} />,
  Profile: () => <EditIcon size={navIconSize} />,
  NewPost: () => <FilePlusIcon size={navIconSize} />,
  Sun: () => <SunIcon size={navIconSize} />,
  Moon: () => <MoonIcon size={navIconSize} />,
  ChevronDown: () => <ChevronDownIcon size={16} />,
};

function NavLink({
  to,
  children,
  icon: Icon,
  exact,
}: { to: string; children: React.ReactNode; icon: React.ComponentType; exact?: boolean }) {
  const location = useLocation();
  const isActive = exact
    ? location.pathname === to
    : location.pathname === to || (to !== '/' && location.pathname.startsWith(to));
  return (
    <Link to={to} className={`sidebar-link ${isActive ? 'active' : ''}`}>
      <span className="sidebar-link-icon"><Icon /></span>
      <span className="sidebar-link-label">{children}</span>
    </Link>
  );
}

export default function Layout() {
  const { user, loading, logout, refreshUser } = useAuth();
  const { theme, toggleTheme } = useTheme();
  const { showToast } = useToast();
  const location = useLocation();
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);
  const [myPageDropdownOpen, setMyPageDropdownOpen] = useState(false);
  const [subscriptionsDropdownOpen, setSubscriptionsDropdownOpen] = useState(false);

  const initial = user?.name?.charAt(0)?.toUpperCase()
    ?? user?.username?.charAt(0)?.toUpperCase()
    ?? '?';

  const myPageHref = user?.creator_profile?.slug
    ? `/creator/${user.creator_profile.slug}`
    : '/creator/new';


  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (menuRef.current && !menuRef.current.contains(e.target as Node)) {
        setMenuOpen(false);
      }
    }
    if (menuOpen) {
      document.addEventListener('click', handleClickOutside);
      return () => document.removeEventListener('click', handleClickOutside);
    }
  }, [menuOpen]);

  const isMyPageSection = location.pathname === myPageHref || location.pathname === '/creator/edit';
  const isSubscriptionsSection = location.pathname === '/subscriptions' || location.pathname.startsWith('/subscriptions/');
  useEffect(() => {
    if (isMyPageSection) setMyPageDropdownOpen(true);
  }, [isMyPageSection]);
  useEffect(() => {
    if (isSubscriptionsSection) setSubscriptionsDropdownOpen(true);
  }, [isSubscriptionsSection]);
  useEffect(() => {
    if (!isMyPageSection) setMyPageDropdownOpen(false);
    if (!isSubscriptionsSection) setSubscriptionsDropdownOpen(false);
  }, [location.pathname, isMyPageSection, isSubscriptionsSection]);

  const [resendVerificationLoading, setResendVerificationLoading] = useState(false);
  const verificationHandledRef = useRef(false);

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const verified = params.get('email_verified');
    const error = params.get('error');
    if (verified === null && error === null) {
      verificationHandledRef.current = false;
      return;
    }
    if (verificationHandledRef.current) return;
    verificationHandledRef.current = true;
    if (verified === '1') {
      showToast('Email verified. You can use your account fully now', 'success');
      navigate(location.pathname || '/', { replace: true });
    } else if (verified === '0' && error === 'invalid') {
      showToast('Verification link is invalid or expired.', 'error');
      navigate(location.pathname || '/', { replace: true });
    }
  }, [location.search, location.pathname, navigate, showToast]);

  async function handleResendVerification() {
    if (resendVerificationLoading) return;
    setResendVerificationLoading(true);
    try {
      await authApi.resendVerificationEmail();
      showToast('Verification email sent. Check your inbox', 'success');
      refreshUser();
    } catch {
      showToast('Failed to send verification email', 'error');
    } finally {
      setResendVerificationLoading(false);
    }
  }

  async function handleLogout() {
    await logout();
    setMenuOpen(false);
    navigate('/login', { replace: true });
  }

  return (
    <>
      <AcceptLegalModal />
      <div className="app-with-sidebar">
        <aside className="app-sidebar">
          <Link to="/" className="sidebar-logo">
            BlogHub
          </Link>

          <nav className="sidebar-nav">
            <section className="sidebar-section" aria-label="Main">
              <NavLink to="/" icon={Icons.Home}>Home</NavLink>
              <NavLink to="/explore" icon={Icons.Explore}>Explore</NavLink>
            </section>

            <section className="sidebar-section sidebar-theme" aria-label="Appearance">
              <button
                type="button"
                className="sidebar-link sidebar-theme-toggle"
                onClick={toggleTheme}
                title={theme === 'dark' ? 'Turn on the lights' : 'Turn off the lights'}
                aria-label={theme === 'dark' ? 'Turn on the lights' : 'Turn off the lights'}
              >
                <span className="sidebar-link-icon">
                  {theme === 'dark' ? <Icons.Sun /> : <Icons.Moon />}
                </span>
                <span className="sidebar-link-label">
                  {theme === 'dark' ? 'Light mode' : 'Dark mode'}
                </span>
              </button>
            </section>

            {user && (
              <section className="sidebar-section" aria-label="Account">
                {user?.creator_profile?.slug ? (
                  <div className="sidebar-dropdown sidebar-my-page-group">
                    <Link
                      to={myPageHref}
                      className={`sidebar-link sidebar-dropdown-trigger ${location.pathname === myPageHref ? 'active' : ''} ${myPageDropdownOpen ? 'open' : ''}`}
                      onClick={() => setMyPageDropdownOpen((o) => !o)}
                      aria-expanded={myPageDropdownOpen}
                    >
                      <span className="sidebar-link-icon"><Icons.MyPage /></span>
                      <span className="sidebar-link-label">My page</span>
                      <span className="sidebar-dropdown-chevron" aria-hidden>
                        <Icons.ChevronDown />
                      </span>
                    </Link>
                    <div
                      className={`sidebar-dropdown-children ${myPageDropdownOpen ? 'open' : ''}`}
                      aria-hidden={!myPageDropdownOpen}
                    >
                      <Link
                        to="/creator/edit"
                        className={`sidebar-link sidebar-link-sub ${location.pathname === '/creator/edit' ? 'active' : ''}`}
                      >
                        <span className="sidebar-link-icon"><Icons.Profile /></span>
                        <span className="sidebar-link-label">Edit Creator</span>
                      </Link>
                    </div>
                  </div>
                ) : (
                  <div className="sidebar-my-page-group">
                    <NavLink to={myPageHref} icon={Icons.MyPage}>My page</NavLink>
                  </div>
                )}
                <NavLink to="/feed/public" icon={Icons.PublicPosts}>Public posts</NavLink>
                <NavLink to="/feed/tier" icon={Icons.TierPosts}>Tier posts</NavLink>
                <div className="sidebar-dropdown sidebar-my-page-group">
                  <Link
                    to="/subscriptions"
                    className={`sidebar-link sidebar-dropdown-trigger ${location.pathname === '/subscriptions' ? 'active' : ''} ${subscriptionsDropdownOpen ? 'open' : ''}`}
                    onClick={() => setSubscriptionsDropdownOpen((o) => !o)}
                    aria-expanded={subscriptionsDropdownOpen}
                  >
                    <span className="sidebar-link-icon"><Icons.Memberships /></span>
                    <span className="sidebar-link-label">Subscriptions</span>
                    <span className="sidebar-dropdown-chevron" aria-hidden>
                      <Icons.ChevronDown />
                    </span>
                  </Link>
                  <div
                    className={`sidebar-dropdown-children ${subscriptionsDropdownOpen ? 'open' : ''}`}
                    aria-hidden={!subscriptionsDropdownOpen}
                  >
                    <Link
                      to="/subscriptions/following"
                      className={`sidebar-link sidebar-link-sub ${location.pathname === '/subscriptions/following' ? 'active' : ''}`}
                    >
                      <span className="sidebar-link-icon"><Icons.Following /></span>
                      <span className="sidebar-link-label">Following</span>
                    </Link>
                    <Link
                      to="/subscriptions/supporting"
                      className={`sidebar-link sidebar-link-sub ${location.pathname === '/subscriptions/supporting' ? 'active' : ''}`}
                    >
                      <span className="sidebar-link-icon"><Icons.Supporting /></span>
                      <span className="sidebar-link-label">Supporting</span>
                    </Link>
                  </div>
                </div>
              </section>
            )}

            {!loading && !user && (
              <section className="sidebar-section sidebar-section-auth" aria-label="Auth">
                <Link to="/login" className={`sidebar-link ${location.pathname === '/login' ? 'active' : ''}`}>
                  <span className="sidebar-link-icon"><Icons.MyPage /></span>
                  <span className="sidebar-link-label">Log in</span>
                </Link>
                <Link to="/register" className={`sidebar-link ${location.pathname === '/register' ? 'active' : ''}`}>
                  <span className="sidebar-link-icon"><Icons.Register /></span>
                  <span className="sidebar-link-label">Register</span>
                </Link>
              </section>
            )}
          </nav>

          {user && (
            <div className="sidebar-footer" ref={menuRef}>
              <button
                type="button"
                className="sidebar-user-badge"
                onClick={() => setMenuOpen((o) => !o)}
                aria-expanded={menuOpen}
                aria-haspopup="true"
                aria-label="User menu"
              >
                {user.avatar_url ? (
                  <img src={user.avatar_url} alt="" className="sidebar-user-avatar" />
                ) : (
                  <span className="sidebar-user-initial">{initial}</span>
                )}
                <span className="sidebar-user-info">
                  <span className="sidebar-user-name">{user.name || 'User'}</span>
                  <span className="sidebar-user-username">@{user.username}</span>
                </span>
              </button>
              {menuOpen && (
                <div className="sidebar-user-menu" role="menu">
                  <Link to="/profile" className="sidebar-user-menu-item" role="menuitem" onClick={() => setMenuOpen(false)}>
                    <Icons.Profile />
                    <span>Edit profile</span>
                  </Link>
                  <Link to="/privacy" className="sidebar-user-menu-item" role="menuitem" onClick={() => setMenuOpen(false)}>
                    <Icons.Privacy />
                    <span>Privacy</span>
                  </Link>
                  <Link to="/terms" className="sidebar-user-menu-item" role="menuitem" onClick={() => setMenuOpen(false)}>
                    <Icons.Terms />
                    <span>Terms</span>
                  </Link>
                  <button type="button" className="sidebar-user-menu-item" role="menuitem" onClick={handleLogout}>
                    <Icons.Logout />
                    <span>Logout</span>
                  </button>
                </div>
              )}
            </div>
          )}
        </aside>
        <div className="app-main-wrap">
          {user && !user.email_verified_at && (
            <div className="email-verify-banner">
              <span className="email-verify-banner-text">Please verify your email to get the most out of BlogHub</span>
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={handleResendVerification}
                disabled={resendVerificationLoading}
              >
                {resendVerificationLoading ? 'Sending…' : 'Resend verification email'}
              </button>
            </div>
          )}
          <main className="app-main">
            <Outlet />
          </main>
        </div>
      </div>
    </>
  );
}
