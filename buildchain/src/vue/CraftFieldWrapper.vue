<template>
  <div
    :id="id + '-field'"
    :aria-describedby="id + '-field-instructions'"
    :class="classes"
    class="field"
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
      <slot/>
    </div>
  </div>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import {nanoid} from "nanoid";

export default defineComponent({
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
      default: () => [],
    }
  },
  data() {
    return {
      id: '',
    }
  },
  mounted(): void {
    this.id = nanoid();
  },
  methods: {}
});
</script>
