<template>
  <div class="flex flex-wrap gap-2">
    <label class="flex-1 min-w-[140px] flex items-center gap-2">
      <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ input.startLabel }}</span>
      <input
        :id="id"
        type="date"
        class="w-full form-control form-input form-control-bordered"
        :class="{ 'form-control-bordered-error': hasError }"
        :value="value.start ?? ''"
        @input="update('start', $event.target.value)"
      />
    </label>
    <label class="flex-1 min-w-[140px] flex items-center gap-2">
      <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ input.endLabel }}</span>
      <input
        :id="`${id}-end`"
        type="date"
        class="w-full form-control form-input form-control-bordered"
        :class="{ 'form-control-bordered-error': hasError }"
        :value="value.end ?? ''"
        @input="update('end', $event.target.value)"
      />
    </label>
  </div>
</template>

<script>
export default {
  props: ['id', 'input', 'modelValue', 'hasError'],
  emits: ['update:modelValue'],

  computed: {
    value() {
      return this.modelValue || { start: null, end: null }
    },
  },

  methods: {
    update(key, raw) {
      this.$emit('update:modelValue', { ...this.value, [key]: raw || null })
    },
  },
}
</script>
