<template>
  <Card class="px-6 py-4">
    <form @submit.prevent="apply" :dusk="`${card.uriKey}-form`">
      <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 leading-tight">
          {{ card.name }}
        </h3>

        <span
          class="text-sm"
          :class="summary ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500'"
        >
          <template v-if="summary">
            <span class="font-semibold">{{ __('Active filter') }}:</span> {{ summary }}
          </template>
          <template v-else>{{ __('No filter active') }}</template>
        </span>
      </div>

      <div class="flex flex-wrap items-end gap-4">
        <div
          v-for="input in card.inputs"
          :key="input.attribute"
          class="flex-1 min-w-[180px]"
        >
          <FormLabel :label-for="`${card.uriKey}-${input.attribute}`" class="mb-2 text-sm font-medium">
            {{ input.label }}
          </FormLabel>

          <component
            :is="componentFor(input)"
            :id="`${card.uriKey}-${input.attribute}`"
            :input="input"
            :model-value="form[input.attribute]"
            :has-error="hasError(input)"
            @update:model-value="form[input.attribute] = $event"
          />

          <HelpText v-if="hasError(input)" class="mt-2 text-red-500">
            {{ firstError(input) }}
          </HelpText>
          <HelpText v-else-if="input.help" class="mt-2">
            {{ input.help }}
          </HelpText>
        </div>

        <div class="flex gap-2">
          <Button
            type="submit"
            :label="__('Apply')"
            :loading="working"
            :disabled="working"
            :dusk="`${card.uriKey}-apply`"
          />
          <Button
            type="button"
            variant="ghost"
            :label="__('Clear')"
            :disabled="working || !hasValue"
            :dusk="`${card.uriKey}-clear`"
            @click="clear"
          />
        </div>
      </div>
    </form>
  </Card>
</template>

<script>
import { Errors } from 'laravel-nova'
import { Button } from 'laravel-nova-ui'
import BooleanInput from './inputs/BooleanInput'
import DateInput from './inputs/DateInput'
import DateRangeInput from './inputs/DateRangeInput'
import SelectInput from './inputs/SelectInput'
import TextInput from './inputs/TextInput'

const INPUTS = {
  boolean: BooleanInput,
  date: DateInput,
  'date-range': DateRangeInput,
  select: SelectInput,
  text: TextInput,
}

export default {
  components: { Button },

  props: {
    card: { type: Object, required: true },
    dashboard: { type: String, required: false },
    resourceName: { type: String, default: '' },
    resourceId: { type: [Number, String], default: '' },
    lens: { type: String, default: '' },
  },

  data() {
    return {
      form: this.cloneValue(this.card.value),
      summary: this.card.summary,
      hasValue: this.card.hasValue,
      errors: new Errors(),
      working: false,
    }
  },

  computed: {
    endpoint() {
      return `/nova-vendor/nova-global-filters/filters/${this.card.filterKey}`
    },

    defaults() {
      return Object.fromEntries(
        this.card.inputs.map(input => [input.attribute, this.cloneValue(input.default)])
      )
    },
  },

  methods: {
    componentFor(input) {
      return INPUTS[input.type] || TextInput
    },

    cloneValue(value) {
      return value === undefined ? null : JSON.parse(JSON.stringify(value))
    },

    errorKeys(input) {
      return input.type === 'date-range'
        ? [input.attribute, `${input.attribute}.start`, `${input.attribute}.end`]
        : [input.attribute]
    },

    hasError(input) {
      return this.errorKeys(input).some(key => this.errors.has(key))
    },

    firstError(input) {
      // Errors.has() also matches nested keys by prefix, so look for a key that carries a message.
      const key = this.errorKeys(input).find(key => this.errors.get(key).length > 0)
      return key ? this.errors.first(key) : null
    },

    async apply() {
      this.working = true
      this.errors = new Errors()

      try {
        const { data } = await Nova.request().post(this.endpoint, {
          ...this.form,
          scope: this.card.scope,
        })

        this.applyState(data)
        Nova.$emit('metric-refresh')
        Nova.success(this.__('Filter applied.'))
      } catch (error) {
        this.handleError(error)
      } finally {
        this.working = false
      }
    },

    async clear() {
      this.working = true
      this.errors = new Errors()

      try {
        const { data } = await Nova.request().delete(this.endpoint, {
          params: { scope: this.card.scope },
        })

        this.form = this.defaults
        this.applyState(data)
        Nova.$emit('metric-refresh')
        Nova.success(this.__('Filter cleared.'))
      } catch (error) {
        this.handleError(error)
      } finally {
        this.working = false
      }
    },

    applyState(data) {
      this.summary = data.summary
      this.hasValue = data.hasValue
      this.form = this.cloneValue(data.value)
    },

    handleError(error) {
      if (error.response && error.response.status === 422) {
        this.errors = new Errors(error.response.data.errors)
        return
      }

      Nova.error(this.__('Something went wrong while saving the filter.'))
    },
  },
}
</script>
