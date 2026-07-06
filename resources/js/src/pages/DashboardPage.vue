<template>
  <AppShell>
    <template #header><TopBar eyebrow="Overview" :title="`Good Morning, ${greetingName} 👋`"><button class="h-11 w-11 rounded-full bg-slate-100 text-slate-500">{{ greetingInitials }}</button></TopBar></template>

    <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
      <div class="grid gap-6">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
          <StatCard label="Daily progress" value="78%" caption="5 of 7 habits completed">
            <template #icon><ChartColumn class="h-5 w-5" /></template>
          </StatCard>
          <StatCard label="Current streak" value="12 days" caption="Keep the chain going">
            <template #icon><Flame class="h-5 w-5" /></template>
          </StatCard>
          <StatCard label="Water today" value="6 cups" caption="2 cups away from target">
            <template #icon><Droplets class="h-5 w-5" /></template>
          </StatCard>
          <StatCard label="Mood score" value="8.6/10" caption="Calm, steady energy">
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
        <ProgressRing :value="78" label="Daily progress" description="You are closer than yesterday." />
        <SectionCard>
          <template #header><h2 class="text-lg font-semibold text-slate-900">Recent activity</h2></template>
          <div class="space-y-3">
            <ActivityItem title="Completed morning stretch" description="5 minutes of movement after waking up" time="08:10">
              <template #icon><StretchHorizontal class="h-4 w-4" /></template>
            </ActivityItem>
            <ActivityItem title="Logged a calm mood" description="Noted gratitude and steady focus" time="10:30">
              <template #icon><Smile class="h-4 w-4" /></template>
            </ActivityItem>
            <ActivityItem title="Drank water" description="Reached half of hydration goal" time="12:05">
              <template #icon><GlassWater class="h-4 w-4" /></template>
            </ActivityItem>
          </div>
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
import { computed, ref, onMounted } from 'vue';
import { ChartColumn, Flame, Droplets, Sparkles, Smile, GlassWater, StretchHorizontal } from 'lucide-vue-next';
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
});
</script>
