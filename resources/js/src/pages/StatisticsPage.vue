<template>
  <AppShell>
    <template #header><TopBar eyebrow="Statistics" title="Progress insights" /></template>
    <div class="grid gap-6 xl:grid-cols-2">
      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">Progress cards</h2></template>
        <div class="grid gap-4 sm:grid-cols-2">
          <StatCard label="Completion" value="78%" caption="This week">
            <template #icon><ChartColumn class="h-5 w-5" /></template>
          </StatCard>
          <StatCard label="Consistency" value="12 days" caption="Current streak">
            <template #icon><Flame class="h-5 w-5" /></template>
          </StatCard>
        </div>
      </SectionCard>
      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">Placeholder charts</h2></template>
        <div class="grid h-64 grid-cols-5 items-end gap-3 rounded-[28px] bg-slate-50 p-5">
          <div v-for="bar in bars" :key="bar" class="rounded-t-2xl bg-emerald-500" :style="{ height: bar + '%' }"></div>
        </div>
      </SectionCard>
      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">Weekly summary</h2></template>
        <ul class="space-y-3 text-sm text-slate-600">
          <li v-for="item in weekly" :key="item">{{ item }}</li>
        </ul>
      </SectionCard>
      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">Monthly summary</h2></template>
        <ul class="space-y-3 text-sm text-slate-600">
          <li v-for="item in monthly" :key="item">{{ item }}</li>
        </ul>
      </SectionCard>
    </div>
  </AppShell>
</template>

<script setup>
import { ChartColumn, Flame } from 'lucide-vue-next';
import AppShell from '../components/AppShell.vue';
import SectionCard from '../components/SectionCard.vue';
import StatCard from '../components/StatCard.vue';
import TopBar from '../components/TopBar.vue';

const bars = [44, 72, 58, 90, 64];
const weekly = ['5 habits completed on average each day.', 'Mood stayed above 7/10 for most days.', 'Sleep consistency improved midweek.'];
const monthly = ['Average streak rose from 7 to 12 days.', 'Hydration remained the most consistent habit.', 'Two low-energy days were recovered quickly.'];
</script>
