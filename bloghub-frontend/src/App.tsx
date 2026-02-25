import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import Layout from './components/Layout';
import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import Discovery from './pages/Discovery';
import CreatorProfilePage from './pages/CreatorProfilePage';
import CreatorProfileForm from './pages/CreatorProfileForm';
import PostPage from './pages/PostPage';
import './index.css';

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route element={<Layout />}>
            <Route path="/" element={<Home />} />
            <Route path="/discover" element={<Discovery />} />
            <Route path="/creator/new" element={<CreatorProfileForm mode="create" />} />
            <Route path="/creator/edit" element={<CreatorProfileForm mode="edit" />} />
            <Route path="/creator/:slug" element={<CreatorProfilePage />} />
            <Route path="/creator/:slug/post/:postSlug" element={<PostPage />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="*" element={<Navigate to="/" replace />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;
