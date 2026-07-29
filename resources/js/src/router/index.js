import { createRouter, createWebHistory } from 'vue-router';
import AdminLogin from '../pages/AdminLogin.vue';
import AuthPage from '../pages/AuthPage.vue';
import DashboardPage from '../pages/DashboardPage.vue';
import HabitsPage from '../pages/HabitsPage.vue';
import MoodPage from '../pages/MoodPage.vue';
import CalendarPage from '../pages/CalendarPage.vue';
import StatisticsPage from '../pages/StatisticsPage.vue';
import ProfilePage from '../pages/ProfilePage.vue';
import { getStoredToken, getStoredUser } from '../utils/auth';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: AuthPage },
  { path: '/dashboard', component: DashboardPage },
  { path: '/habits', component: HabitsPage },
  { path: '/mood', component: MoodPage },
  { path: '/calendar', component: CalendarPage },
  { path: '/statistics', component: StatisticsPage },
  { path: '/profile', component: ProfilePage },
  { path: '/admin', redirect: '/admin/dashboard' },
  { path: '/admin/login', component: AdminLogin },
  { path: '/admin/dashboard', component: () => import('../pages/AdminDashboard.vue') },
  { path: '/admin/users', component: () => import('../pages/AdminUsers.vue') },
  { path: '/admin/suggestions', component: () => import('../pages/AdminSuggestions.vue') },
  { path: '/admin/categories', component: () => import('../pages/AdminCategories.vue') },
  { path: '/:pathMatch(.*)*', redirect: '/login' },
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach((to, from, next) => {
  const token = getStoredToken();
  const isAuthenticated = !!token;
  const user = getStoredUser();
  const isAdminPage = to.path.startsWith('/admin/');

  if (isAdminPage && to.path === '/admin/login') {
    return next();
  }

  if (isAdminPage) {
    if (!isAuthenticated) return next('/admin/login');
    const roles = user?.roles ?? [];
    if (!roles.includes('admin')) return next('/admin/login');
    return next();
  }

  if (to.path !== '/login' && !isAuthenticated) {
    next('/login');
  } else {
    next();
  }
});

export default router;
