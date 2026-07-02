<template>
  <AppShell>
    <template #header><TopBar eyebrow="Mood Journal" title="How do you feel today?" /></template>
    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">Choose a mood</h2></template>
        <MoodPicker />
        <textarea rows="6" placeholder="Write a few notes about your day" class="mt-5 w-full rounded-[24px] border border-slate-200 bg-slate-50 p-4 outline-none focus:border-emerald-400"></textarea>
        <div class="mt-4 flex justify-end"><UiButton>Save</UiButton></div>
      </SectionCard>
      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">Mood history</h2></template>
        <div class="space-y-4">
          <article v-for="entry in history" :key="entry.date" class="rounded-[26px] border border-slate-100 p-4">
            <div class="flex items-center justify-between gap-3">
              <p class="text-lg">{{ entry.emoji }} <span class="ml-2 font-medium text-slate-900">{{ entry.label }}</span></p>
              <span class="text-sm text-slate-400">{{ entry.date }}</span>
            </div>
            <p class="mt-2 text-sm text-slate-500">{{ entry.note }}</p>
          </article>
        </div>
      </SectionCard>
    </div>
  </AppShell>
</template>

<script setup>
import AppShell from '../components/AppShell.vue';
import MoodPicker from '../components/MoodPicker.vue';
import SectionCard from '../components/SectionCard.vue';
import TopBar from '../components/TopBar.vue';
import UiButton from '../components/UiButton.vue';

const history = [
  { emoji: '😌', label: 'Calm', date: 'Today', note: 'Focused and steady throughout the afternoon.' },
  { emoji: '✨', label: 'Inspired', date: 'Yesterday', note: 'Completed the most important habits early.' },
  { emoji: '🤍', label: 'Peaceful', date: 'Mon', note: 'A lighter day with a gentle pace.' },
];
</script>
