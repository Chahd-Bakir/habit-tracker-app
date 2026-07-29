<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="$emit('cancel')">
    <div class="w-full max-w-sm rounded-[28px] border border-slate-100 bg-white p-6 shadow-lg animate-fade-in">
      <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600 mb-4">
        <Trash2 class="h-6 w-6" />
      </div>
      <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
      <p class="mt-2 text-sm text-slate-500">{{ message }}</p>
      <div class="mt-6 flex gap-3 justify-end">
        <button @click="$emit('cancel')" class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</button>
        <button @click="$emit('confirm')" class="rounded-2xl bg-red-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-600 transition">Delete</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Trash2 } from 'lucide-vue-next';

defineProps({
  isOpen: Boolean,
  title: { type: String, default: 'Delete habit' },
  message: { type: String, default: 'Are you sure you want to delete this habit? Past history will be preserved.' },
});
defineEmits(['confirm', 'cancel']);
</script>
