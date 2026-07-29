<template>
  <AdminLayout>
    <template #header>
      <h1 class="text-lg font-semibold text-slate-900">Dashboard</h1>
      <p class="text-sm text-slate-500">Platform overview</p>
    </template>

    <div class="mb-4 flex flex-wrap items-center gap-3">
      <input type="date" v-model="startDate" @change="fetchStats" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-400" />
      <span class="text-sm text-slate-400">to</span>
      <input type="date" v-model="endDate" @change="fetchStats" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-400" />
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Total Users</p>
        <p class="mt-1 text-3xl font-bold text-slate-900">{{ stats.total_users }}</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">DAU</p>
        <p class="mt-1 text-3xl font-bold text-slate-900">{{ stats.dau }}</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Retention</p>
        <p class="mt-1 text-3xl font-bold text-slate-900">{{ stats.retention_rate }}%</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">New today / week</p>
        <p class="mt-1 text-3xl font-bold text-slate-900">{{ stats.new_users_today }}<span class="text-base font-normal text-slate-400"> / {{ stats.new_users_this_week }}</span></p>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5">
      <h2 class="mb-4 text-sm font-semibold text-slate-900">New users over time</h2>
      <div class="relative" style="height: 280px">
        <canvas ref="chartRef"></canvas>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
import AdminLayout from '../components/AdminLayout.vue';
import { getStoredToken } from '../utils/auth';

Chart.register(...registerables);

const chartRef = ref(null);
let chartInstance = null;

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

const buildChart = (chartData) => {
  if (chartInstance) chartInstance.destroy();
  if (!chartRef.value) return;

  chartInstance = new Chart(chartRef.value, {
    type: 'bar',
    data: {
      labels: chartData.labels,
      datasets: [{
        label: chartData.datasets[0].label,
        data: chartData.datasets[0].data,
        backgroundColor: 'rgba(99, 102, 241, 0.6)',
        borderColor: 'rgb(99, 102, 241)',
        borderWidth: 1,
        borderRadius: 4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45 } },
        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } } },
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
    buildChart(json.chart);
  } catch (e) {
    console.error('Stats fetch error:', e);
  }
};

onMounted(fetchStats);
</script>
