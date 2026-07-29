<template>
  <AppShell>
    <template #header><TopBar eyebrow="Statistics" title="Progress insights" /></template>
    <div v-if="loading" class="py-12 text-center text-sm text-slate-400">Loading statistics...</div>
    <div v-else class="grid gap-6 xl:grid-cols-2">
      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">Progress cards</h2></template>
        <div class="grid gap-4 sm:grid-cols-2">
          <StatCard :label="'Completion'" :value="completionPercent" caption="This week">
            <template #icon><ChartColumn class="h-5 w-5" /></template>
          </StatCard>
          <StatCard :label="'Check-ins'" :value="String(totalCheckins)" caption="Total this week">
            <template #icon><Flame class="h-5 w-5" /></template>
          </StatCard>
        </div>
      </SectionCard>
      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">This week's activity</h2></template>
        <div class="grid h-64 grid-cols-7 items-end gap-2 rounded-[28px] bg-slate-50 p-5">
          <div v-for="d in daily" :key="d.date" class="flex flex-col items-center gap-1">
            <div class="w-full rounded-t-2xl bg-emerald-500 transition-all" :style="{ height: barHeight(d) + 'px' }"></div>
            <span class="text-xs text-slate-500">{{ d.label }}</span>
          </div>
        </div>
      </SectionCard>
      <SectionCard>
        <template #header><h2 class="text-lg font-semibold text-slate-900">Weekly summary</h2></template>
        <ul class="space-y-3 text-sm text-slate-600">
          <li v-for="item in weeklySummary" :key="item">{{ item }}</li>
        </ul>
      </SectionCard>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { ChartColumn, Flame } from 'lucide-vue-next';
import AppShell from '../components/AppShell.vue';
import SectionCard from '../components/SectionCard.vue';
import StatCard from '../components/StatCard.vue';
import TopBar from '../components/TopBar.vue';
import { getStoredToken } from '../utils/auth';

const loading = ref(true);
const data = ref(null);

const completionPercent = computed(() => `${data.value?.completion_percentage ?? 0}%`);
const totalCheckins = computed(() => data.value?.total_checkins ?? 0);
const daily = computed(() => data.value?.daily_breakdown ?? []);

const weeklySummary = computed(() => {
  if (!data.value) return [];
  const { completion_percentage, best_day, total_checkins, daily_breakdown } = data.value;
  const lines = [];
  if (best_day) {
    const dayData = daily_breakdown.find(d => d.date === best_day.date);
    const detail = dayData ? `${dayData.completed}/${dayData.total} habits` : '';
    lines.push(`Best day was ${best_day.label} (${best_day.date}) — ${detail}.`);
  }
  const avg = daily_breakdown.filter(d => d.total > 0).length;
  lines.push(`${completion_percentage}% completion rate across ${avg} active days this week.`);
  lines.push(`${total_checkins} total check-ins — keep the momentum going.`);
  return lines;
});

const barHeight = (d) => {
  if (d.total === 0) return 2;
  const maxCompleted = Math.max(...daily.value.map(x => x.completed), 1);
  return Math.max(Math.round((d.completed / maxCompleted) * 180), 4);
};

const fetchStats = async () => {
  const token = getStoredToken();
  try {
    const res = await fetch('/api/stats/weekly', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const json = await res.json();
    if (res.ok && json.success) data.value = json;
  } catch (e) {
    console.error('Stats fetch error:', e);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchStats);
</script>
