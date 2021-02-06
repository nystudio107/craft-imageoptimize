<template>
  <div :id="id + '-field'" class="field" :class="classes" :aria-describedby="id + '-field-instructions'">
    <div class="heading">
      <label :id="id + '-field-label'" :for="id">{{ label }}</label>                        </div>
    <div :id="id + '-field-instructions'" class="instructions" v-if="instructions">
      <p>{{ instructions }}</p>
    </div>
    <div class="input ltr">
      <slot></slot>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  props: {
    label: {
      type: String,
      default: 'Label',
    },
    instructions: {
      type: String,
      default: 'Instructions',
    },
    classes: {
      type: Array,
      default: [],
    }
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
