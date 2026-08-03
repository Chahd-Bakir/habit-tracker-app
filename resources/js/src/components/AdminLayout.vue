<template>
  <AppShell
    :nav-items="navItems"
    brand-subtitle="Admin panel"
    :show-focus-card="false"
    logout-target="/admin/login"
  >
    <template #header>
      <slot name="header" />
    </template>
    <slot />
  </AppShell>
</template>

<script setup>
import AppShell from './AppShell.vue';
import { LayoutDashboard, Users, Lightbulb, Tags } from 'lucide-vue-next';

const navItems = [
  { label: 'Dashboard', to: '/admin/dashboard', icon: LayoutDashboard },
  { label: 'Users', to: '/admin/users', icon: Users },
  { label: 'Suggestions', to: '/admin/suggestions', icon: Lightbulb },
  { label: 'Categories', to: '/admin/categories', icon: Tags },
];
</script>
