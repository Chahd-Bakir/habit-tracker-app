export const getStoredUser = () => {
  const rawUser = localStorage.getItem('auth_user');

  if (!rawUser) {
    return null;
  }

  try {
    return JSON.parse(rawUser);
  } catch {
    return null;
  }
};

export const getUserInitials = (user) => {
  if (!user?.name) {
    return 'U';
  }

  return user.name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('');
};

export const clearAuthSession = () => {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('auth_user');
};

export const getStoredToken = () => localStorage.getItem('auth_token');

export const saveAuthSession = (user, token) => {
  localStorage.setItem('auth_token', token);
  localStorage.setItem('auth_user', JSON.stringify(user));
};

export const updateStoredUser = (user) => {
  localStorage.setItem('auth_user', JSON.stringify(user));
};

export const getUserRole = (user) => {
  if (!user) {
    return 'user';
  }

  if (user.role === 'admin' || (Array.isArray(user.roles) && user.roles.includes('admin'))) {
    return 'admin';
  }

  return 'user';
};