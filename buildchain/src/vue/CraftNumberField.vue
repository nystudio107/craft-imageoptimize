<template>
  <craft-field-wrapper
    :classes="['width-25']"
    :instructions="instructions"
    :label="label"
  >
    <input
      :id="id"
      :class="inputClasses"
      :max="max"
      :min="min"
      :name="name"
      :size="size"
      :value="value"
      autocomplete="off"
      class="text"
      step="1"
      type="number"
      @input="validateInput($event)"
    >
    <ul
      v-if="inputErrors.length"
      class="errors"
    >
      <li
        v-for="(error, index) in inputErrors"
        :key="'error' + index"
      >
        {{ error }}
      </li>
    </ul>
  </craft-field-wrapper>
</template>

<script lang="ts">
import {defineComponent, PropType} from 'vue';
import CraftFieldWrapper from '@/vue/CraftFieldWrapper.vue';
import {nanoid} from "nanoid";

export default defineComponent({
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
    errors: {
      type: Array as PropType<string[]>,
      default: () => [],
    }
  },
  data() {
    return {
      id: '',
      inputErrors: this.errors,
    }
  },
  computed: {
    inputClasses(): string[] {
      let result = [];
      if (this.inputErrors.length) {
        result.push('error');
      }

      return result;
    }
  },
  mounted(): void {
    this.id = nanoid();
  },
  methods: {
    validateInput(e: Event): void {
      const target = e.target as HTMLInputElement;
      let val = parseInt(target.value);
      this.inputErrors = [];
      if (val < this.min) {
        this.inputErrors.push('Too small');
      }
      if (val > this.max) {
        this.inputErrors.push('Too big');
      }
      this.$emit('update:' + this.field, val);
    }
  }
});
</script>
