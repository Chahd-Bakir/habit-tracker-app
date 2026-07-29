<template>
  <SectionCard>
    <template #header>
      <div><h2 class="text-lg font-semibold text-slate-900">Weekly summary</h2><p class="text-sm text-slate-500">{{ weekLabel }}</p></div>
    </template>
    <div v-if="loading" class="py-6 text-center text-sm text-slate-400">Loading...</div>
    <div v-else class="grid gap-4 sm:grid-cols-3">
      <div class="rounded-2xl bg-emerald-50 p-4 text-center">
        <p class="text-3xl font-bold text-emerald-600">{{ stats.completion_percentage }}%</p>
        <p class="mt-1 text-xs font-medium text-emerald-800 uppercase tracking-wide">Completion</p>
      </div>
      <div class="rounded-2xl bg-blue-50 p-4 text-center">
        <p class="text-sm font-semibold text-blue-700">{{ stats.best_day?.label ?? '-' }}</p>
        <p class="text-lg font-bold text-blue-600">{{ stats.best_day?.date ?? '—' }}</p>
        <p class="mt-1 text-xs font-medium text-blue-800 uppercase tracking-wide">Best day</p>
      </div>
      <div class="rounded-2xl bg-amber-50 p-4 text-center">
        <p class="text-3xl font-bold text-amber-600">{{ stats.total_checkins }}</p>
        <p class="mt-1 text-xs font-medium text-amber-800 uppercase tracking-wide">Check-ins</p>
      </div>
    </div>
  </SectionCard>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import SectionCard from './SectionCard.vue';
import { getStoredToken } from '../utils/auth';

const props = defineProps({
  date: { type: String, default: '' },
});

const loading = ref(true);
const data = ref(null);

const weekLabel = computed(() => {
  if (!data.value) return '';
  const s = data.value.week_start;
  const e = data.value.week_end;
  if (!s || !e) return '';
  const start = new Date(s + 'T12:00:00');
  const end = new Date(e + 'T12:00:00');
  const opts = { month: 'short', day: 'numeric' };
  return `${start.toLocaleDateString('en-US', opts)} – ${end.toLocaleDateString('en-US', opts)}`;
});

const stats = computed(() => ({
  completion_percentage: data.value?.completion_percentage ?? 0,
  best_day: data.value?.best_day ?? null,
  total_checkins: data.value?.total_checkins ?? 0,
}));

const fetchWeek = async () => {
  loading.value = true;
  const token = getStoredToken();
  const params = props.date ? `?date=${props.date}` : '';
  try {
    const res = await fetch(`/api/stats/weekly${params}`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const json = await res.json();
    if (res.ok && json.success) data.value = json;
  } catch (e) {
    console.error('Weekly stats error:', e);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchWeek);
</script>
