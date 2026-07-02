<template>
  <AppShell>
    <template #header><TopBar eyebrow="Overview" title="Good Morning, Chahd 👋"><button class="h-11 w-11 rounded-full bg-slate-100 text-slate-500">C</button></TopBar></template>

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
          <template #header><div><h2 class="text-lg font-semibold text-slate-900">Today's habits</h2><p class="text-sm text-slate-500">A minimal list of your key intentions</p></div><UiButton variant="secondary">Add habit</UiButton></template>
          <div class="space-y-3">
            <div v-for="habit in habits" :key="habit.name" class="flex items-center justify-between gap-4 rounded-3xl border border-slate-100 p-4 transition hover:border-emerald-200 hover:bg-emerald-50/40">
              <div class="flex items-start gap-3">
                <input type="checkbox" checked class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-500">
                <div>
                  <p class="font-medium text-slate-900">{{ habit.name }}</p>
                  <p class="text-sm text-slate-500">{{ habit.time }}</p>
                </div>
              </div>
              <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">{{ habit.frequency }}</span>
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
  </AppShell>
</template>

<script setup>
import { ChartColumn, Flame, Droplets, Sparkles, Smile, GlassWater, StretchHorizontal } from 'lucide-vue-next';
import ActivityItem from '../components/ActivityItem.vue';
import AppShell from '../components/AppShell.vue';
import MoodPicker from '../components/MoodPicker.vue';
import ProgressRing from '../components/ProgressRing.vue';
import SectionCard from '../components/SectionCard.vue';
import SidebarNav from '../components/SidebarNav.vue';
import StatCard from '../components/StatCard.vue';
import TopBar from '../components/TopBar.vue';
import UiButton from '../components/UiButton.vue';

const habits = [
  { name: 'Morning stretch', time: '07:30 AM', frequency: 'Daily' },
  { name: 'Read for 20 minutes', time: '09:00 PM', frequency: 'Weekdays' },
  { name: 'Drink 8 glasses of water', time: 'All day', frequency: 'Daily' },
];

const week = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
</script>
