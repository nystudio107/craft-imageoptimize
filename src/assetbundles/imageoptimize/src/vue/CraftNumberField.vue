<template>
  <div :id="id + '-field'" class="field" :aria-describedby="id + '-field-instructions'">
    <div class="heading">
      <label :id="id + '-field-label'" :for="id">{{ label }}</label>                        </div>
    <div :id="id + '-field-instructions'" class="instructions">
      <p>{{ instructions }}</p>
    </div>
    <div class="input ltr">
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
    </div>
  </div>
</template>

<script lang="ts">
export default {
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
