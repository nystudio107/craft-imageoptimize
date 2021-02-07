<template>
  <div
    :id="id + '-field'"
    class="field"
    :class="classes"
    :aria-describedby="id + '-field-instructions'"
  >
    <div class="heading">
      <label
        :id="id + '-field-label'"
        :for="id"
      >
        {{ label }}
      </label>
    </div>
    <div
      v-if="instructions"
      :id="id + '-field-instructions'"
      class="instructions"
    >
      <p>
        {{ instructions }}
      </p>
    </div>
    <div class="input ltr">
      <slot></slot>
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend({
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
