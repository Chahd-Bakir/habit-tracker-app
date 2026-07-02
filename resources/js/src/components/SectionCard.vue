<template>
  <section class="rounded-[30px] border border-emerald-100 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.06)] sm:p-6">
    <div v-if="$slots.header" class="mb-5 flex items-center justify-between gap-3">
      <slot name="header" />
    </div>
    <slot />
  </section>
</template>
