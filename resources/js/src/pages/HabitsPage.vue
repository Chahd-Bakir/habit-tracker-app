<template>
  <AppShell>
    <template #header><TopBar eyebrow="Habits" title="My Habits"><UiButton variant="secondary">Add Habit</UiButton></TopBar></template>
    <SectionCard>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <input type="search" placeholder="Search habits" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-400 sm:max-w-md">
        <UiButton>Add Habit</UiButton>
      </div>
      <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="habit in habits" :key="habit.name" class="rounded-[28px] border border-emerald-100 p-5 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-50/30">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="font-semibold text-slate-900">{{ habit.name }}</h3>
              <p class="mt-1 text-sm text-slate-500">{{ habit.description }}</p>
            </div>
            <input type="checkbox" checked class="h-4 w-4 rounded border-slate-300 text-emerald-500">
          </div>
          <div class="mt-4 flex items-center justify-between text-sm">
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">{{ habit.frequency }}</span>
            <span class="text-slate-500">{{ habit.progress }}%</span>
          </div>
          <div class="mt-3 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-emerald-500" :style="{ width: habit.progress + '%' }"></div></div>
        </article>
      </div>
    </SectionCard>
  </AppShell>
</template>

<script setup>
import AppShell from '../components/AppShell.vue';
import SectionCard from '../components/SectionCard.vue';
import TopBar from '../components/TopBar.vue';
import UiButton from '../components/UiButton.vue';

const habits = [
  { name: 'Morning stretch', description: 'Wake up with five minutes of movement.', frequency: 'Daily', progress: 80 },
  { name: 'Drink water', description: 'Keep hydration steady through the day.', frequency: 'Daily', progress: 70 },
  { name: 'Read a book', description: 'Wind down with a calm reading session.', frequency: 'Weekdays', progress: 55 },
];
</script>
