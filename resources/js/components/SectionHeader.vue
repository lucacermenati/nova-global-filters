<template>
  <Card class="px-6 py-3">
    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 leading-tight">
      {{ card.title }}
    </h3>
    <p v-if="card.subtitle" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ card.subtitle }}
    </p>
  </Card>
</template>

<script>
export default {
  props: ['card'],
}
</script>
