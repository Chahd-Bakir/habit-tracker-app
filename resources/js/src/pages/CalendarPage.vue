<template>
  <AppShell>
    <template #header><TopBar eyebrow="Calendar" title="Monthly calendar" /></template>
    <SectionCard>
      <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
        <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span> Completed</span>
        <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-slate-200"></span> Upcoming</span>
      </div>
      <div class="mt-5 rounded-[28px] border border-slate-100 p-4">
        <CalendarGrid :cells="cells" />
      </div>
    </SectionCard>
  </AppShell>
</template>

<script setup>
import AppShell from '../components/AppShell.vue';
import CalendarGrid from '../components/CalendarGrid.vue';
import SectionCard from '../components/SectionCard.vue';
import TopBar from '../components/TopBar.vue';

const cells = Array.from({ length: 35 }, (_, index) => ({
  key: index,
  label: index < 2 ? '' : String(index - 1),
  completed: [2, 3, 6, 7, 10, 12, 13, 15, 17, 18, 21, 22, 25, 26, 29, 30].includes(index),
}));
</script>
