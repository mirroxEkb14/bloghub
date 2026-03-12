import { useEffect } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { ToastProvider } from './contexts/ToastContext';
import Layout from './components/Layout';
import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import ExplorePage from './pages/Explore';
import CreatorProfilePage from './pages/CreatorProfilePage';
import CreatorStudioPage from './pages/CreatorStudioPage';
import PostPage from './pages/PostPage';
import SubscriptionsPage from './pages/SubscriptionsPage';
import FollowingPage from './pages/FollowingPage';
import SupportingPage from './pages/SupportingPage';
import PublicPostsPage from './pages/PublicPostsPage';
import TierPostsPage from './pages/TierPostsPage';
import TermsPage from './pages/TermsPage';
import PrivacyPage from './pages/PrivacyPage';
import ProfilePage from './pages/ProfilePage';
import './index.css';

function App() {
  useEffect(() => {
    if ('scrollRestoration' in history) {
      history.scrollRestoration = 'manual';
    }
  }, []);

  return (
    <AuthProvider>
      <ToastProvider>
        <BrowserRouter>
          <Routes>
          <Route element={<Layout />}>
            <Route path="/" element={<Home />} />
            <Route path="/explore" element={<ExplorePage />} />
            <Route path="/feed/public" element={<PublicPostsPage />} />
            <Route path="/feed/tier" element={<TierPostsPage />} />
            <Route path="/memberships" element={<Navigate to="/subscriptions" replace />} />
            <Route path="/memberships/billings" element={<Navigate to="/subscriptions" replace />} />
            <Route path="/creator/new" element={<CreatorStudioPage />} />
            <Route path="/creator/edit" element={<CreatorStudioPage />} />
            <Route path="/creator/tiers" element={<Navigate to="/creator/edit" replace />} />
            <Route path="/creator/post/new" element={<Navigate to="/creator/edit" replace />} />
            <Route path="/creator/:slug" element={<CreatorProfilePage />} />
            <Route path="/creator/:slug/post/:postSlug" element={<PostPage />} />
            <Route path="/subscriptions" element={<SubscriptionsPage />} />
            <Route path="/subscriptions/following" element={<FollowingPage />} />
            <Route path="/subscriptions/supporting" element={<SupportingPage />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/terms" element={<TermsPage />} />
            <Route path="/privacy" element={<PrivacyPage />} />
            <Route path="/profile" element={<ProfilePage />} />
            <Route path="/profile/social" element={<Navigate to="/creator/edit" replace />} />
            <Route path="*" element={<Navigate to="/" replace />} />
          </Route>
        </Routes>
      </BrowserRouter>
      </ToastProvider>
    </AuthProvider>
  );
}

export default App;
