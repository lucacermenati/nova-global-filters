<template>
  <div class="flex flex-col justify-end px-1 pt-2">
    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 leading-tight">
      {{ card.title }}
    </h3>
    <p v-if="card.subtitle" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ card.subtitle }}
    </p>
  </div>
</template>

<script>
export default {
  props: ['card'],
}
</script>
