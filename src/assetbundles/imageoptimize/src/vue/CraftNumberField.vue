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
      :class="inputClasses"
      class="text"
      type="number"
      autocomplete="off"
      step="1"
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
import Vue, {PropType} from 'vue';
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
    errors: {
      type: Array as PropType<string[]>,
      default: () => [],
    }
  },
  data(): Record<string, unknown> {
    return {
      id: null,
      inputErrors: this.errors,
    }
  },
  computed: {
    inputClasses(): array {
      let result = [];
      if (this.inputErrors.length) {
        result.push('error');
      }

      return result;
    }
  },
  mounted(): void {
    this.id = this._uid;
  },
  methods: {
    validateInput(e:Event): void {
      let val = e.target.value;
      this.inputErrors = [];
      if (val < this.min) {
        this.inputErrors.push('Too small');
      }
      if (val > this.max) {
        this.inputErrors.push('Too big');
      }
      this.$emit('update:' + this.field, parseInt(val));
    }
  }
});
</script>
