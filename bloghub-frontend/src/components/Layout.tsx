import { Link, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function Layout() {
  const { user, loading, logout } = useAuth();
  const location = useLocation();

  const initial = user?.name?.charAt(0)?.toUpperCase()
    ?? user?.username?.charAt(0)?.toUpperCase()
    ?? '?';

  return (
    <>
      <header className="app-header">
        <Link to="/" className="app-logo">
          BlogHub
        </Link>
        <nav className="app-nav">
          {!loading && (
            <>
              <Link to="/" className={location.pathname === '/' ? 'active' : ''}>
                Home
              </Link>
              {user ? (
                <>
                  <span className="avatar" title={user.email}>
                    {initial}
                  </span>
                  <button
                    type="button"
                    className="btn btn-secondary"
                    onClick={() => logout()}
                  >
                    Log out
                  </button>
                </>
              ) : (
                <>
                  <Link to="/login" className={location.pathname === '/login' ? 'active' : ''}>
                    Log in
                  </Link>
                  <Link to="/register" className={location.pathname === '/register' ? 'active' : ''}>
                    Register
                  </Link>
                </>
              )}
            </>
          )}
        </nav>
      </header>
      <main>
        <Outlet />
      </main>
    </>
  );
}
