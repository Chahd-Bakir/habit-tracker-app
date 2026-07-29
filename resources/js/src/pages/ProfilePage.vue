<template>
  <AppShell>
    <template #header><TopBar eyebrow="Profile" title="Profile settings"><UiButton variant="secondary">Edit Profile</UiButton></TopBar></template>
    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
      <SectionCard>
        <div class="flex flex-col items-center text-center">
          <div class="flex h-24 w-24 items-center justify-center rounded-full bg-emerald-100 text-3xl font-semibold text-emerald-700">{{ initials }}</div>
          <h2 class="mt-4 text-2xl font-semibold text-slate-900">{{ user?.name ?? 'Guest user' }}</h2>
          <p class="text-sm text-slate-500">{{ user?.email ?? 'No email available' }}</p>
        </div>
      </SectionCard>
      <SectionCard>
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="rounded-[24px] bg-slate-50 p-4"><p class="text-sm text-slate-500">Name</p><p class="mt-1 font-medium text-slate-900">{{ user?.name ?? 'Guest user' }}</p></div>
          <div class="rounded-[24px] bg-slate-50 p-4"><p class="text-sm text-slate-500">Email</p><p class="mt-1 font-medium text-slate-900">{{ user?.email ?? 'No email available' }}</p></div>
          <div class="rounded-[24px] bg-slate-50 p-4"><p class="text-sm text-slate-500">Language</p><p class="mt-1 font-medium text-slate-900">{{ user?.language ?? 'en' }}</p></div>
          <div class="rounded-[24px] bg-slate-50 p-4"><p class="text-sm text-slate-500">Goals</p><p class="mt-1 font-medium text-slate-900">{{ goalSummary }}</p></div>
        </div>
      </SectionCard>
    </div>
    <div class="mt-6">
      <ReminderSettings />
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import AppShell from '../components/AppShell.vue';
import ReminderSettings from '../components/ReminderSettings.vue';
import SectionCard from '../components/SectionCard.vue';
import TopBar from '../components/TopBar.vue';
import UiButton from '../components/UiButton.vue';
import { getStoredUser, getUserInitials } from '../utils/auth';

const user = computed(() => getStoredUser());
const initials = computed(() => getUserInitials(user.value));
const goalSummary = computed(() => {
  const goals = user.value?.goals ?? [];

  if (!goals.length) {
    return 'No goals selected yet';
  }

  return goals.map((goal) => goal.title).join(', ');
});
</script>
