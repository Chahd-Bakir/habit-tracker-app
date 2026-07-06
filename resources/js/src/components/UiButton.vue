<template>
  <component
    :is="tag"
    :href="href"
    class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
    :class="variantClass"
    @click="$emit('click', $event)"
  >
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: { type: String, default: 'primary' },
  href: { type: String, default: '' },
});

defineEmits(['click']);

const tag = computed(() => (props.href ? 'a' : 'button'));

const variantClass = computed(() => {
  const variants = {
    primary: 'bg-emerald-500 text-white shadow-[0_16px_35px_rgba(76,175,80,0.24)] hover:bg-emerald-600',
    secondary: 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
    ghost: 'bg-transparent text-slate-600 hover:bg-slate-100',
    dark: 'bg-slate-900 text-white hover:bg-slate-800',
  };

  return variants[props.variant] ?? variants.primary;
});
</script>
