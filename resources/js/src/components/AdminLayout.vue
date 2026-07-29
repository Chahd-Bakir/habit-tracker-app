<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <div class="flex min-h-screen">
      <aside class="flex w-64 flex-col bg-slate-900 text-slate-300">
        <div class="flex items-center gap-3 border-b border-slate-800 px-5 py-5">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500 text-white text-sm font-bold">A</div>
          <div>
            <p class="text-sm font-semibold text-white">Admin Panel</p>
            <p class="text-xs text-slate-500">{{ user?.name ?? 'Admin' }}</p>
          </div>
        </div>

        <nav class="flex-1 space-y-1 px-3 py-4">
          <router-link
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
            :class="$route.path === item.to ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
          >
            <component :is="item.icon" class="h-5 w-5" />
            {{ item.label }}
          </router-link>
        </nav>

        <div class="border-t border-slate-800 px-4 py-4">
          <button @click="logout" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-800 hover:text-red-400 transition">
            <LogOut class="h-5 w-5" />
            Sign out
          </button>
        </div>
      </aside>

      <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white px-6 py-4">
          <div class="flex items-center justify-between">
            <div>
              <slot name="header" />
            </div>
            <span class="text-sm text-slate-400">{{ user?.email }}</span>
          </div>
        </header>

        <main class="flex-1 px-6 py-6">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { LayoutDashboard, Users, Lightbulb, Tags, LogOut } from 'lucide-vue-next';
import { getStoredUser, clearAuthSession } from '../utils/auth';

const router = useRouter();
const route = useRoute();
const user = computed(() => getStoredUser());

const navItems = [
  { label: 'Dashboard', to: '/admin/dashboard', icon: LayoutDashboard },
  { label: 'Users', to: '/admin/users', icon: Users },
  { label: 'Suggestions', to: '/admin/suggestions', icon: Lightbulb },
  { label: 'Categories', to: '/admin/categories', icon: Tags },
];

const logout = () => {
  clearAuthSession();
  router.push('/admin/login');
};
</script>
