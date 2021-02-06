<template>
  <craft-field-wrapper :label="label"
                       :instructions="instructions"
                       :classes="['width-25']"
  >
    <input :name="name"
               :id="id"
               @input="$emit('update:' + field, parseInt($event.target.value))"
               :value="value"
               :size="size"
               :min="min"
               :max="max"
               type="number"
               class="text"
               autocomplete="off"
               step="1"
        />
  </craft-field-wrapper>
</template>

<script lang="ts">
import CraftFieldWrapper from './CraftFieldWrapper.vue';

export default {
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
  data() {
    return {
      id: null,
    }
  },
  mounted() {
    this.id = this._uid;
  },
  methods: {
  }
}
</script>
