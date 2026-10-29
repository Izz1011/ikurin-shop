import { api } from './api.js';

export async function requireRole(role) {
  const { data } = await api.get('/auth/me');
  const user = data.user;
  const currentPath = window.location.pathname + window.location.search;

  if (!user) {
    window.location.href = `/login.html?next=${encodeURIComponent(currentPath)}`;
    return null;
  }

  if (user.role !== role) {
    const redirectPath =
      user.role === 'ADMIN' ? '/admin/dashboard.html' : user.role === 'TOKO' ? '/seller/dashboard.html' : '/';
    window.location.href = redirectPath;
    return null;
  }

  return user;
}
