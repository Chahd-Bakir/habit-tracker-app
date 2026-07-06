<template>
  <div class="flex h-full flex-col">
    <div class="flex items-center gap-3">
      <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
        <Leaf class="h-6 w-6" />
      </div>
      <div>
        <p class="text-lg font-semibold">Khotwa</p>
        <p class="text-sm text-slate-500">Habit & wellness</p>
      </div>
    </div>

    <div class="mt-8 flex flex-1 flex-col gap-2">
      <RouterLink v-for="item in items" :key="item.label" :to="item.to" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition" :class="isActive(item.to) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100'">
        <component :is="item.icon" class="h-5 w-5" />
        <span>{{ item.label }}</span>
      </RouterLink>
      <button type="button" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-100" @click="logout">
        <LogOut class="h-5 w-5" />
        <span>Logout</span>
      </button>
    </div>

    <div class="mt-4 rounded-3xl bg-emerald-50 p-4 text-sm text-emerald-900">
      <p class="font-semibold">Today's focus</p>
      <p class="mt-1 text-emerald-700">Small steps, repeated daily, shape the bigger picture.</p>
    </div>
  </div>
</template>

<script setup>
import { useRouter, useRoute, RouterLink } from 'vue-router';
import { CalendarDays, ChartColumn, Cog, LogOut, MoonStar, NotebookTabs, PanelLeft, User, Leaf } from 'lucide-vue-next';
import { clearAuthSession, getStoredToken } from '../utils/auth';

const route = useRoute();
const router = useRouter();

const items = [
  { label: 'Dashboard', to: '/dashboard', icon: PanelLeft },
  { label: 'My Habits', to: '/habits', icon: NotebookTabs },
  { label: 'Mood Journal', to: '/mood', icon: MoonStar },
  { label: 'Calendar', to: '/calendar', icon: CalendarDays },
  { label: 'Statistics', to: '/statistics', icon: ChartColumn },
  { label: 'Profile', to: '/profile', icon: User },
  { label: 'Settings', to: '/profile', icon: Cog },
];

const isActive = (to) => route.path === to;

const logout = async () => {
  const token = getStoredToken();

  try {
    if (token) {
      await fetch('/api/logout', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
      });
    }
  } finally {
    clearAuthSession();
    await router.push('/login');
  }
};
</script>
