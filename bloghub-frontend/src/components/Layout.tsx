import { useState, useRef, useEffect } from 'react';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import AcceptLegalModal from './AcceptLegalModal';

const iconSize = 22;

const Icons = {
  Home: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
      <polyline points="9 22 9 12 15 12 15 22" />
    </svg>
  ),
  Explore: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <circle cx="12" cy="12" r="10" />
      <line x1="2" y1="12" x2="22" y2="12" />
      <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
    </svg>
  ),
  MyPage: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
      <circle cx="12" cy="7" r="4" />
    </svg>
  ),
  PublicPosts: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <circle cx="12" cy="12" r="10" />
      <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
    </svg>
  ),
  TierPosts: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
      <path d="M7 11V7a5 5 0 0 1 10 0v4" />
    </svg>
  ),
  Privacy: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
    </svg>
  ),
  Terms: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
      <polyline points="14 2 14 8 20 8" />
      <line x1="16" y1="13" x2="8" y2="13" />
      <line x1="16" y1="17" x2="8" y2="17" />
      <polyline points="10 9 9 9 8 9" />
    </svg>
  ),
  Logout: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
      <polyline points="16 17 21 12 16 7" />
      <line x1="21" y1="12" x2="9" y2="12" />
    </svg>
  ),
  Register: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
      <circle cx="8.5" cy="7" r="4" />
      <line x1="20" y1="8" x2="20" y2="14" />
      <line x1="23" y1="11" x2="17" y2="11" />
    </svg>
  ),
  Profile: () => (
    <svg width={iconSize} height={iconSize} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
      <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
    </svg>
  ),
};

function NavLink({
  to,
  children,
  icon: Icon,
}: { to: string; children: React.ReactNode; icon: React.ComponentType }) {
  const location = useLocation();
  const isActive = location.pathname === to || (to !== '/' && location.pathname.startsWith(to));
  return (
    <Link to={to} className={`sidebar-link ${isActive ? 'active' : ''}`}>
      <span className="sidebar-link-icon"><Icon /></span>
      <span className="sidebar-link-label">{children}</span>
    </Link>
  );
}

export default function Layout() {
  const { user, loading, logout } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);

  const initial = user?.name?.charAt(0)?.toUpperCase()
    ?? user?.username?.charAt(0)?.toUpperCase()
    ?? '?';

  const myPageHref = user?.creator_profile?.slug
    ? `/creator/${user.creator_profile.slug}`
    : '/creator/new';

  const isMyPageActive =
    location.pathname === '/creator/edit' ||
    (!!user?.creator_profile?.slug && location.pathname === `/creator/${user.creator_profile.slug}`);
  const showEditCreatorSubItem = isMyPageActive && !!user?.creator_profile?.slug;

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

            {user && (
              <section className="sidebar-section" aria-label="Account">
                <div className="sidebar-my-page-group">
                  <NavLink to={myPageHref} icon={Icons.MyPage}>My page</NavLink>
                  {showEditCreatorSubItem && (
                    <Link
                      to="/creator/edit"
                      className={`sidebar-link sidebar-link-sub ${location.pathname === '/creator/edit' ? 'active' : ''}`}
                    >
                      <span className="sidebar-link-icon"><Icons.Profile /></span>
                      <span className="sidebar-link-label">Edit Creator</span>
                    </Link>
                  )}
                </div>
                <NavLink to="/feed/public" icon={Icons.PublicPosts}>Public posts</NavLink>
                <NavLink to="/feed/tier" icon={Icons.TierPosts}>Tier posts</NavLink>
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
        <main className="app-main">
          <Outlet />
        </main>
      </div>
    </>
  );
}
