<template>
  <input
    :id="id"
    type="date"
    class="w-full form-control form-input form-control-bordered"
    :class="{ 'form-control-bordered-error': hasError }"
    :min="input.min || undefined"
    :max="input.max || undefined"
    :placeholder="input.placeholder || ''"
    :value="modelValue ?? ''"
    @input="$emit('update:modelValue', $event.target.value || null)"
  />
</template>

<script>
export default {
  props: ['id', 'input', 'modelValue', 'hasError'],
  emits: ['update:modelValue'],
}
</script>
