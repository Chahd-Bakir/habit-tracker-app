<template>
  <article
    :class="[
      'rounded-[28px] border p-5 shadow-sm transition-all duration-300 select-none',
      completed
        ? 'border-emerald-200 bg-emerald-50/60'
        : 'border-emerald-100 bg-white hover:-translate-y-0.5 hover:bg-emerald-50/30',
    ]"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="flex items-center gap-3 min-w-0 flex-1" @click="handleToggle">
        <button
          type="button"
          class="check-btn relative mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full border-2 transition-all duration-300"
          :class="completed
            ? 'border-emerald-500 bg-emerald-500 scale-110 shadow-sm'
            : 'border-slate-300 bg-white hover:border-emerald-400'"
        >
          <Transition name="pop">
            <Check v-if="showCheck" class="h-3.5 w-3.5 text-white" />
          </Transition>
        </button>
        <div
          class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border-2 text-2xl"
          :style="{ borderColor: habit.color, backgroundColor: habit.color + '15' }"
        >
          {{ habit.icon }}
        </div>
        <div class="min-w-0">
          <h3
            class="font-semibold truncate"
            :class="completed ? 'text-slate-400 line-through' : 'text-slate-900'"
          >
            {{ habit.title }}
          </h3>
          <p class="mt-1 text-sm text-slate-500 capitalize truncate">
            {{ habit.frequency === 'custom' && habit.custom_days ? habit.custom_days.join(', ') : habit.frequency }}
          </p>
        </div>
      </div>
      <GripVertical class="drag-handle mt-1 h-5 w-5 flex-shrink-0 text-slate-300 cursor-grab active:cursor-grabbing" />
    </div>
    <div v-if="habit.reminder_time" class="mt-4 flex items-center gap-2 text-sm text-slate-500">
      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ habit.reminder_time }}
    </div>
  </article>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Check, GripVertical } from 'lucide-vue-next';

const props = defineProps({
  habit: { type: Object, required: true },
  completed: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle']);

const showCheck = ref(props.completed);

watch(() => props.completed, (val) => {
  if (val && !showCheck.value) {
    showCheck.value = true;
  } else if (!val) {
    setTimeout(() => { showCheck.value = false; }, 150);
  }
});

const handleToggle = () => {
  if (navigator.vibrate) {
    navigator.vibrate(10);
  }
  const becomingCompleted = !props.completed;
  showCheck.value = becomingCompleted;
  emit('toggle', props.habit);
};
</script>

<style scoped>
.pop-enter-active {
  animation: pop-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pop-leave-active {
  animation: pop-in 0.15s ease-in reverse;
}
@keyframes pop-in {
  0% { transform: scale(0) rotate(-30deg); opacity: 0; }
  100% { transform: scale(1) rotate(0deg); opacity: 1; }
}
</style>
