import { createRouter, createWebHistory } from 'vue-router';
import AuthPage from '../pages/AuthPage.vue';
import DashboardPage from '../pages/DashboardPage.vue';
import HabitsPage from '../pages/HabitsPage.vue';
import MoodPage from '../pages/MoodPage.vue';
import CalendarPage from '../pages/CalendarPage.vue';
import StatisticsPage from '../pages/StatisticsPage.vue';
import ProfilePage from '../pages/ProfilePage.vue';
import { getStoredToken } from '../utils/auth';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: AuthPage },
  { path: '/dashboard', component: DashboardPage },
  { path: '/habits', component: HabitsPage },
  { path: '/mood', component: MoodPage },
  { path: '/calendar', component: CalendarPage },
  { path: '/statistics', component: StatisticsPage },
  { path: '/profile', component: ProfilePage },
  { path: '/:pathMatch(.*)*', redirect: '/login' },
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach((to, from, next) => {
  const token = getStoredToken();
  const isAuthenticated = !!token;

  if (to.path === '/login' && isAuthenticated) {
    next('/dashboard');
  } else if (to.path !== '/login' && !isAuthenticated) {
    next('/login');
  } else {
    next();
  }
});

export default router;
