<template>
  <div class="flex items-center h-9">
    <Checkbox
      :id="id"
      :model-value="modelValue === true"
      :label="input.placeholder || ''"
      @update:model-value="$emit('update:modelValue', $event ? true : null)"
    />
  </div>
</template>

<script>
import { Checkbox } from 'laravel-nova-ui'

export default {
  components: { Checkbox },
  props: ['id', 'input', 'modelValue', 'hasError'],
  emits: ['update:modelValue'],
}
</script>
