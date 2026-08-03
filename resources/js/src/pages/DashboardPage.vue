<template>
  <AppShell>
    <template #header><TopBar eyebrow="Overview" :title="`Good Morning, ${greetingName} 👋`"><button class="h-11 w-11 rounded-full bg-slate-100 text-slate-500">{{ greetingInitials }}</button></TopBar></template>

    <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
      <div class="grid gap-6">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          <StatCard label="Daily progress" :value="`${progress.percentage}%`" :caption="`${progress.completed} of ${progress.total} habits completed`">
            <template #icon><ChartColumn class="h-5 w-5" /></template>
          </StatCard>
          <StatCard label="Current streak" :value="`${streak.current} days`" :caption="streak.current > 0 ? `Longest: ${streak.longest} days` : 'Start a habit to build a streak'">
            <template #icon><Flame class="h-5 w-5" /></template>
          </StatCard>
          <StatCard label="Mood score" :value="mood.score !== null ? `${mood.score}/10` : 'No data'" :caption="mood.label ?? 'Log a mood to see your score'">
            <template #icon><Sparkles class="h-5 w-5" /></template>
          </StatCard>
        </div>

        <SectionCard>
          <template #header><div><h2 class="text-lg font-semibold text-slate-900">Today's habits</h2><p class="text-sm text-slate-500">A minimal list of your key intentions</p></div><UiButton variant="secondary" @click="openModal" type="button">Add habit</UiButton></template>
          <div class="space-y-3">
            <div v-for="habit in habits" :key="habit.id" class="flex items-center justify-between gap-4 rounded-3xl border border-slate-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
              <div class="flex items-start gap-3">
                <input type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-500">
                <div class="flex items-center gap-3">
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg border-2 text-xl" :style="{ borderColor: habit.color, backgroundColor: habit.color + '15' }">{{ habit.icon }}</div>
                  <div>
                    <p class="font-medium text-slate-900">{{ habit.title }}</p>
                    <p class="text-sm text-slate-500 capitalize">
                      {{ habit.frequency === 'custom' && habit.custom_days ? habit.custom_days.join(', ') : habit.frequency }}
                      <span v-if="habit.reminder_time"> • {{ habit.reminder_time }}</span>
                    </p>
                  </div>
                </div>
              </div>
              <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 capitalize">
                {{ habit.frequency === 'custom' && habit.custom_days ? habit.custom_days.slice(0, 2).join(', ') + (habit.custom_days.length > 2 ? '...' : '') : habit.frequency }}
              </span>
            </div>
          </div>
        </SectionCard>

        <SectionCard>
          <template #header><div><h2 class="text-lg font-semibold text-slate-900">Mood</h2><p class="text-sm text-slate-500">Select what best matches your current state</p></div></template>
          <MoodPicker />
        </SectionCard>

        <SectionCard>
          <template #header><div><h2 class="text-lg font-semibold text-slate-900">Weekly calendar preview</h2></div></template>
          <div class="grid grid-cols-7 gap-3 text-center text-sm">
            <div v-for="day in week" :key="day" class="rounded-2xl bg-slate-50 py-3 font-medium text-slate-500">{{ day }}</div>
          </div>
        </SectionCard>
      </div>

      <div class="grid gap-6">
        <ProgressRing :value="progress.percentage" label="Daily progress" description="You are closer than yesterday." />
        <SectionCard>
          <template #header><h2 class="text-lg font-semibold text-slate-900">Recent activity</h2></template>
          <div v-if="activity.length > 0" class="space-y-3">
            <ActivityItem v-for="(item, index) in activity" :key="index" :title="item.title" :description="item.description" :time="item.time">
              <template #icon><span class="text-lg">{{ item.icon }}</span></template>
            </ActivityItem>
          </div>
          <p v-else class="py-6 text-center text-sm text-slate-400">No activity yet. Check off a habit or log a mood to see it here.</p>
        </SectionCard>
        <SectionCard>
          <template #header><h2 class="text-lg font-semibold text-slate-900">Motivational quote</h2></template>
          <p class="text-lg leading-8 text-slate-700">“Small disciplines repeated with consistency every day lead to great achievements.”</p>
          <p class="mt-4 text-sm font-medium text-emerald-700">- John C. Maxwell</p>
        </SectionCard>
      </div>
    </div>
    <AddHabitModal :is-open="isModalOpen" @close="closeModal" @habit-created="onHabitCreated" />
  </AppShell>
</template>

<script setup>
import { computed, ref, reactive, onMounted } from 'vue';
import { ChartColumn, Flame, Sparkles } from 'lucide-vue-next';
import ActivityItem from '../components/ActivityItem.vue';
import AddHabitModal from '../components/AddHabitModal.vue';
import AppShell from '../components/AppShell.vue';
import MoodPicker from '../components/MoodPicker.vue';
import ProgressRing from '../components/ProgressRing.vue';
import SectionCard from '../components/SectionCard.vue';
import StatCard from '../components/StatCard.vue';
import TopBar from '../components/TopBar.vue';
import UiButton from '../components/UiButton.vue';
import { getStoredUser, getUserInitials, getStoredToken } from '../utils/auth';

const user = computed(() => getStoredUser());

const greetingName = computed(() => user.value?.name ?? 'Guest');
const greetingInitials = computed(() => getUserInitials(user.value));

const habits = ref([]);
const isModalOpen = ref(false);
const activity = ref([]);

const progress = reactive({ completed: 0, total: 0, percentage: 0 });
const streak = reactive({ current: 0, longest: 0 });
const mood = reactive({ score: null, label: null });

const fetchHabits = async () => {
  const token = getStoredToken();
  try {
    const response = await fetch('/api/habits', {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    });
    const data = await response.json();
    if (response.ok) {
      habits.value = data.habits;
    }
  } catch (error) {
    console.error('Failed to fetch habits:', error);
  }
};

const fetchDashboard = async () => {
  const token = getStoredToken();
  try {
    const response = await fetch('/api/dashboard', {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    });
    const data = await response.json();
    if (!response.ok || !data.success) return;

    progress.completed = data.daily_progress.completed;
    progress.total = data.daily_progress.total;
    progress.percentage = data.daily_progress.percentage;

    streak.current = data.streak.current;
    streak.longest = data.streak.longest;

    mood.score = data.mood.score;
    mood.label = data.mood.label;

    activity.value = data.recent_activity;
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error);
  }
};

const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const onHabitCreated = (habit) => {
  habits.value.unshift(habit);
};

const week = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

onMounted(() => {
  fetchHabits();
  fetchDashboard();
});
</script>
