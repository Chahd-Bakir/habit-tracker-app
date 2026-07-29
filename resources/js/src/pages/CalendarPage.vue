<template>
  <AppShell>
    <template #header><TopBar eyebrow="Calendar" title="Monthly calendar" /></template>

    <div class="flex items-center justify-between">
      <button @click="prevMonth" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition">&larr; Previous</button>
      <h3 class="text-lg font-semibold text-slate-900">{{ monthLabel }}</h3>
      <button @click="nextMonth" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition">Next &rarr;</button>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-slate-600">
      <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span> All done</span>
      <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-200"></span> Partial</span>
      <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-slate-200"></span> None</span>
    </div>

    <SectionCard class="mt-3">
      <CalendarGrid :cells="cells" @cell-click="onCellClick" />
    </SectionCard>

    <WeeklySummaryCard class="mt-6" />

    <div v-if="selectedDay" class="mt-6">
      <SectionCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">{{ selectedDayLabel }}</h3>
            <button @click="selectedDay = null" class="text-sm text-slate-400 hover:text-slate-600 transition">&times;</button>
          </div>
        </template>
        <div v-if="dayDetailsLoading" class="py-4 text-center text-sm text-slate-400">Loading...</div>
        <div v-else-if="dayDetails.length === 0" class="py-4 text-center text-sm text-slate-400">No habits for this day.</div>
        <div v-else class="grid gap-2 sm:grid-cols-2">
          <button
            v-for="h in dayDetails"
            :key="h.id"
            @click="toggleHabit(h)"
            class="flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm transition hover:shadow-sm text-left"
            :class="h.completed ? 'border-emerald-200 bg-emerald-50' : 'border-slate-100 bg-white hover:bg-slate-50'"
            :disabled="togglingId === h.id"
          >
            <span>{{ h.icon }}</span>
            <span class="flex-1 font-medium text-slate-900">{{ h.title }}</span>
            <span v-if="h.completed" class="text-emerald-600">&#10003;</span>
            <span v-else class="text-slate-300">&mdash;</span>
          </button>
        </div>
      </SectionCard>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AppShell from '../components/AppShell.vue';
import CalendarGrid from '../components/CalendarGrid.vue';
import SectionCard from '../components/SectionCard.vue';
import TopBar from '../components/TopBar.vue';
import WeeklySummaryCard from '../components/WeeklySummaryCard.vue';
import { getStoredToken } from '../utils/auth';

const currentMonth = ref('');
const completions = ref({});
const habits = ref([]);
const cells = ref([]);
const loading = ref(false);

const selectedDay = ref(null);
const dayDetails = ref([]);
const dayDetailsLoading = ref(false);
const togglingId = ref(null);

const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

const monthLabel = computed(() => {
  if (!currentMonth.value) return '';
  const [y, m] = currentMonth.value.split('-');
  return `${monthNames[parseInt(m) - 1]} ${y}`;
});

const selectedDayLabel = computed(() => {
  if (!selectedDay.value) return '';
  const d = new Date(selectedDay.value + 'T12:00:00');
  return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
});

const now = new Date();
const defaultMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;

const fetchMonth = async (month) => {
  selectedDay.value = null;
  loading.value = true;
  const token = getStoredToken();
  try {
    const response = await fetch(`/api/calendar/month?month=${month}`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error('Failed to load calendar');

    completions.value = data.completions || {};
    habits.value = data.habits || [];
    buildCells(data);
  } catch (error) {
    console.error('Calendar fetch error:', error);
  } finally {
    loading.value = false;
  }
};

const buildCells = (data) => {
  const { days_in_month, first_day_of_week } = data;
  const result = [];
  const pad = first_day_of_week;

  for (let i = 0; i < pad; i++) {
    result.push({ key: `pad-${i}`, label: '', isPadding: true, ratio: 0 });
  }

  for (let day = 1; day <= days_in_month; day++) {
    const dateStr = `${data.month}-${String(day).padStart(2, '0')}`;
    const comp = completions.value[dateStr] || { completed: 0, total: 0 };
    const ratio = comp.total > 0 ? comp.completed / comp.total : 0;

    result.push({
      key: dateStr,
      label: String(day),
      isPadding: false,
      completed: comp.completed,
      total: comp.total,
      ratio,
      date: dateStr,
    });
  }

  cells.value = result;
};

const onCellClick = (cell) => {
  if (cell.isPadding) return;
  selectedDay.value = cell.date;
  fetchDayDetails(cell.date);
};

const fetchDayDetails = async (date) => {
  dayDetailsLoading.value = true;
  const token = getStoredToken();
  try {
    const response = await fetch(`/api/calendar/day?date=${date}`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = await response.json();
    if (response.ok && data.success) {
      dayDetails.value = data.habits || [];
    }
  } catch (error) {
    console.error('Day details error:', error);
  } finally {
    dayDetailsLoading.value = false;
  }
};

const toggleHabit = async (h) => {
  togglingId.value = h.id;
  const token = getStoredToken();
  try {
    h.completed = !h.completed;
    const response = await fetch(`/api/habits/${h.id}/toggle`, {
      method: 'POST',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = await response.json();
    if (!response.ok || !data.success) {
      h.completed = !h.completed;
      throw new Error(data.message || 'Toggle failed');
    }
    h.completed = data.completed;
    fetchMonth(currentMonth.value);
  } catch (error) {
    console.error('Toggle error:', error);
  } finally {
    togglingId.value = null;
  }
};

const prevMonth = () => {
  const [y, m] = currentMonth.value.split('-').map(Number);
  const d = new Date(y, m - 2, 1);
  currentMonth.value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
};

const nextMonth = () => {
  const [y, m] = currentMonth.value.split('-').map(Number);
  const d = new Date(y, m, 1);
  currentMonth.value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
};

watch(currentMonth, (val) => {
  if (val) fetchMonth(val);
});

onMounted(() => {
  currentMonth.value = defaultMonth;
});
</script>
