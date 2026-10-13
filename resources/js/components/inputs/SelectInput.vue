<template>
  <SelectControl
    :id="id"
    :model-value="modelValue ?? ''"
    :options="options"
    :has-error="hasError"
    @update:model-value="$emit('update:modelValue', $event === '' ? null : $event)"
  />
</template>

<script>
export default {
  props: ['id', 'input', 'modelValue', 'hasError'],
  emits: ['update:modelValue'],

  computed: {
    options() {
      return [{ value: '', label: this.input.anyLabel }, ...this.input.options]
    },
  },
}
</script>
