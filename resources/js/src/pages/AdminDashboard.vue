<template>
  <AdminLayout>
    <template #header>
      <TopBar eyebrow="Overview" title="Dashboard" />
    </template>

    <div class="mb-8 flex flex-wrap items-center gap-3">
      <input type="date" v-model="startDate" @change="fetchStats" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-emerald-400" />
      <span class="text-sm text-slate-400">to</span>
      <input type="date" v-model="endDate" @change="fetchStats" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-emerald-400" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard tone="emerald" label="Total users" :value="String(stats.total_users)" caption="Registered accounts">
        <template #icon><Users class="h-5 w-5" /></template>
      </StatCard>
      <StatCard tone="teal" label="Daily active users" :value="String(stats.dau)" caption="Active in the last 24 hours">
        <template #icon><Activity class="h-5 w-5" /></template>
      </StatCard>
      <StatCard tone="green" label="Retention rate" :value="`${stats.retention_rate}%`" caption="Users returning to the app">
        <template #icon><TrendingUp class="h-5 w-5" /></template>
      </StatCard>
      <StatCard tone="lime" label="New users" :value="`${stats.new_users_today} / ${stats.new_users_this_week}`" caption="Today vs this week">
        <template #icon><UserPlus class="h-5 w-5" /></template>
      </StatCard>
    </div>

    <SectionCard class="mt-8">
      <template #header>
        <div>
          <h2 class="text-lg font-semibold text-slate-900">New users over time</h2>
          <p class="text-sm text-slate-500">Daily registrations for the selected period</p>
        </div>
      </template>
      <div class="rounded-2xl bg-slate-50/70 p-4 sm:p-5">
        <div class="relative" style="height: 280px">
          <canvas ref="newUsersRef"></canvas>
        </div>
      </div>
    </SectionCard>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
      <SectionCard>
        <template #header>
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Role distribution</h2>
            <p class="text-sm text-slate-500">Share of admin vs user accounts</p>
          </div>
        </template>
        <div class="rounded-2xl bg-slate-50/70 p-4 sm:p-5">
          <div class="relative" style="height: 260px">
            <canvas ref="rolesRef"></canvas>
          </div>
        </div>
      </SectionCard>

      <SectionCard>
        <template #header>
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Most created habits</h2>
            <p class="text-sm text-slate-500">Top 5 habit titles across all users</p>
          </div>
        </template>
        <div class="rounded-2xl bg-slate-50/70 p-4 sm:p-5">
          <div class="relative" style="height: 260px">
            <canvas ref="topHabitsRef"></canvas>
          </div>
        </div>
      </SectionCard>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
      <SectionCard>
        <template #header>
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Activity over period</h2>
            <p class="text-sm text-slate-500">Total habit check-ins per day</p>
          </div>
        </template>
        <div class="rounded-2xl bg-slate-50/70 p-4 sm:p-5">
          <div class="relative" style="height: 260px">
            <canvas ref="activityRef"></canvas>
          </div>
        </div>
      </SectionCard>

      <SectionCard>
        <template #header>
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Avg completion by weekday</h2>
            <p class="text-sm text-slate-500">Global completion rate per day of the week</p>
          </div>
        </template>
        <div class="rounded-2xl bg-slate-50/70 p-4 sm:p-5">
          <div class="relative" style="height: 260px">
            <canvas ref="weekdayRef"></canvas>
          </div>
        </div>
      </SectionCard>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';
import { Users, Activity, TrendingUp, UserPlus } from 'lucide-vue-next';
import AdminLayout from '../components/AdminLayout.vue';
import SectionCard from '../components/SectionCard.vue';
import StatCard from '../components/StatCard.vue';
import TopBar from '../components/TopBar.vue';
import { getStoredToken } from '../utils/auth';

Chart.register(...registerables);

const emerald = { bg: 'rgba(16, 185, 129, 0.6)', border: 'rgb(16, 185, 129)' };
const teal = { bg: 'rgba(20, 184, 166, 0.6)', border: 'rgb(20, 184, 166)' };
const green = { bg: 'rgba(34, 197, 94, 0.6)', border: 'rgb(34, 197, 94)' };

const newUsersRef = ref(null);
const rolesRef = ref(null);
const topHabitsRef = ref(null);
const activityRef = ref(null);
const weekdayRef = ref(null);

const charts = {};

const registerChart = (key, canvas, config) => {
  if (charts[key]) charts[key].destroy();
  if (!canvas) return;
  charts[key] = new Chart(canvas, config);
};

const now = new Date();
const startDate = ref(new Date(now.getFullYear(), now.getMonth(), now.getDate() - 30).toISOString().split('T')[0]);
const endDate = ref(now.toISOString().split('T')[0]);

const stats = reactive({
  total_users: 0,
  new_users_today: 0,
  new_users_this_week: 0,
  dau: 0,
  retention_rate: 0,
});

const formatShortDate = (value) => {
  const d = new Date(`${value}T00:00:00`);
  if (isNaN(d)) return value;
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const timeSeriesOptions = (color, stepSize = 1) => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: {
      grid: { display: false },
      ticks: { font: { size: 11 }, maxRotation: 0, minRotation: 0, autoSkip: true, maxTicksLimit: 10, callback: formatShortDate },
    },
    y: { beginAtZero: true, ticks: { stepSize, font: { size: 11 } } },
  },
});

const renderCharts = (data) => {
  const chartData = data.chart;

  registerChart('newUsers', newUsersRef.value, {
    type: 'bar',
    data: {
      labels: chartData.labels,
      datasets: [{
        label: chartData.datasets[0].label,
        data: chartData.datasets[0].data,
        backgroundColor: emerald.bg,
        borderColor: emerald.border,
        borderWidth: 1,
        borderRadius: 6,
      }],
    },
    options: timeSeriesOptions(emerald),
  });

  const roles = data.role_distribution || [];
  registerChart('roles', rolesRef.value, {
    type: 'doughnut',
    data: {
      labels: roles.map((r) => r.label),
      datasets: [{
        data: roles.map((r) => r.count),
        backgroundColor: roles.map((r) => (r.role === 'admin' ? '#10b981' : '#cbd5e1')),
        borderColor: '#ffffff',
        borderWidth: 2,
        hoverOffset: 6,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16, font: { size: 12 } } },
      },
    },
  });

  const habits = (data.top_habits || []).slice().reverse();
  registerChart('topHabits', topHabitsRef.value, {
    type: 'bar',
    data: {
      labels: habits.map((h) => h.title),
      datasets: [{
        data: habits.map((h) => h.count),
        backgroundColor: teal.bg,
        borderColor: teal.border,
        borderWidth: 1,
        borderRadius: 6,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      plugins: { legend: { display: false } },
      scales: {
        x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(15, 23, 42, 0.05)' } },
        y: { grid: { display: false }, ticks: { font: { size: 12 } } },
      },
    },
  });

  const activity = data.activity || { labels: [], data: [] };
  registerChart('activity', activityRef.value, {
    type: 'line',
    data: {
      labels: activity.labels,
      datasets: [{
        data: activity.data,
        borderColor: teal.border,
        backgroundColor: 'rgba(20, 184, 166, 0.15)',
        fill: true,
        tension: 0.35,
        pointRadius: 2,
        pointHoverRadius: 5,
        borderWidth: 2,
      }],
    },
    options: timeSeriesOptions(teal),
  });

  const weekdays = data.weekday_completion || { labels: [], data: [] };
  registerChart('weekday', weekdayRef.value, {
    type: 'bar',
    data: {
      labels: weekdays.labels,
      datasets: [{
        data: weekdays.data,
        backgroundColor: green.bg,
        borderColor: green.border,
        borderWidth: 1,
        borderRadius: 6,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: { beginAtZero: true, ticks: { font: { size: 11 }, callback: (v) => `${v}%` } },
      },
    },
  });
};

const fetchStats = async () => {
  const token = getStoredToken();
  try {
    const res = await fetch(`/api/admin/stats?start_date=${startDate.value}&end_date=${endDate.value}`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const json = await res.json();
    if (!json.success) return;

    stats.total_users = json.stats.total_users;
    stats.new_users_today = json.stats.new_users_today;
    stats.new_users_this_week = json.stats.new_users_this_week;
    stats.dau = json.stats.dau;
    stats.retention_rate = json.stats.retention_rate;

    await nextTick();
    renderCharts(json);
  } catch (e) {
    console.error('Stats fetch error:', e);
  }
};

onMounted(fetchStats);

onBeforeUnmount(() => {
  Object.values(charts).forEach((chart) => chart.destroy());
});
</script>
