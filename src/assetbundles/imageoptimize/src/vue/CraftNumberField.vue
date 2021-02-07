<template>
  <craft-field-wrapper
    :label="label"
    :instructions="instructions"
    :classes="['width-25']"
  >
    <input
      :id="id"
      :name="name"
      :value="value"
      :size="size"
      :min="min"
      :max="max"
      type="number"
      class="text"
      autocomplete="off"
      step="1"
      @input="$emit('update:' + field, parseInt($event.target.value))"
    />
  </craft-field-wrapper>
</template>

<script lang="ts">
import Vue from 'vue';
import CraftFieldWrapper from './CraftFieldWrapper.vue';

export default Vue.extend({
  components: {
    'craft-field-wrapper': CraftFieldWrapper,
  },
  props: {
    value: {
      type: Number,
      default: 0,
    },
    field: {
      type: String,
      default: 'field',
    },
    name: {
      type: String,
      default: 'types[woof]',
    },
    size: {
      type: Number,
      default: 5,
    },
    label: {
      type: String,
      default: 'Label',
    },
    instructions: {
      type: String,
      default: 'Instructions',
    },
    min: {
      type: Number,
      default: 1,
    },
    max: {
      type: Number,
      default: 10000,
    },

  },
  data(): Record<string, unknown> {
    return {
      id: null,
    }
  },
  mounted(): void {
    this.id = this._uid;
  },
  methods: {}
});
</script>
