<template>
  <div width="80%">
    <div :width="breakpointWidth" height="50px" class="border bg-gray-100">
      <arrow-line :id="id" :label="breakpointValue + breakpointUnits" :width="200"></arrow-line>
    </div>
  </div>
</template>

<script lang="ts">

import ArrowLine from '../vue/ArrowLine.vue';
const remPx:number = 16;
const emPx:number = 16;

const normalizeUnitsToPx = (value: number, units: string) => {
  let result:number;
  switch (units) {
    case 'rem':
      result = value * remPx;
      break;
    case 'em':
      result = value * remPx;
      break;
    default:
    case 'px':
      result = value;
      break;
  }

  return result;
}

export default {
  components: {
    'arrow-line': ArrowLine,
  },
  props: {
    id: {
      type: String,
      default: '',
    },
    breakpointValue: {
      type: Number,
      default: 1000,
    },
    breakpointUnits: {
      type: String,
      default: '',
    },
  },
  computed: {
    breakpointWidth() {
      return normalizeUnitsToPx(this.breakpointValue, this.breakpointUnits);
    }
  },
  data() {
    return {
    }
  },
  methods: {
  }
}
</script>
