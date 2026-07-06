<template>
  <div class="min-h-screen bg-[linear-gradient(180deg,#f8fff9_0%,#ffffff_28%,#f7faf8_100%)] text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-[1600px]">
      <aside class="hidden lg:flex lg:w-72 lg:flex-col lg:border-r lg:border-emerald-100 lg:bg-white/90 lg:backdrop-blur-xl">
        <nav class="flex-1 px-4 py-5">
          <SidebarNav />
        </nav>
        <div class="border-t border-emerald-100 px-6 py-5">
          <p class="text-sm text-slate-500">Signed in as {{ user?.name ?? 'Guest' }}</p>
          <p class="mt-1 text-sm font-medium text-slate-900">{{ user?.email ?? 'No email found' }}</p>
        </div>
      </aside>

      <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 border-b border-emerald-100 bg-white/85 px-4 py-4 backdrop-blur-xl sm:px-6 lg:px-8">
          <slot name="header" />
        </header>

        <main class="flex-1 px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SidebarNav from './SidebarNav.vue';
import { getStoredUser } from '../utils/auth';

const user = computed(() => getStoredUser());
</script>
