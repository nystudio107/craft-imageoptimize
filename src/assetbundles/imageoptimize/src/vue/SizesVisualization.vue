<template>
  <svg
    :width="breakpointWidth"
    :viewBox="'0 0 ' + breakpointValue + ' 200'"
    preserveAspectRatio="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <arrow-line
      :id="id"
      :label="breakpointValue + breakpointUnits"
    ></arrow-line>

    <rect x="1" y="20" :width="breakpointValue - 2" height="198" fill="#DDD" stroke="#AAA" stroke-width="2" stroke-opacity="0.5" fill-opacity="0.0" stroke-dasharray="5, 5">
    </rect>

    <rect x="1" y="20" :width="breakpointValue - 2" height="180" fill="#DDD" stroke="#AAA"  stroke-width="2">
    </rect>

    <defs>
      <pattern id="svg-triangle-pattern" width="16" height="10" patternUnits="userSpaceOnUse">
        <path class="svg-triangle" d="M0,0 L8,8 16,0" fill="rgb(221, 231, 242)" stroke="rgb(163, 193, 226)" stroke-linecap="square" ></path>
      </pattern>'
    </defs>

    <svg v-for="n in numUp">
      <rect :x="xForRect(n)" y="40" :width="breakpointValue / numUp" height="130" fill="rgb(221, 231, 242)" stroke-width="2">
      </rect>
      <polyline :points="pointsForRect(n)" stroke="rgb(163, 193, 226)" stroke-width="2" fill="none"></polyline>
      <rect x="0" y="170" :width="breakpointValue" height="8" fill="url(#svg-triangle-pattern)">
      </rect>

    </svg>

    <text x="50%" y="50%" text-anchor="middle" alignment-baseline="central" font-size="40">
      hi
    </text>
  </svg>
</template>

<script lang="ts">

import ArrowLine from '../vue/ArrowLine.vue';
const remPx:number = 16;
const emPx:number = 16;
const maxNormalizedWidth = 1000;

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
    numUp: {
      type: Number,
      default: 2,
    },
    widthMultiplier: {
      type: Number,
      default: 1,
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
    breakpointWidth():String {
      let percent:Number = (((this.breakpointValue * this.widthMultiplier) / maxNormalizedWidth) * 100);

      return percent + '%';
    }
  },
  data() {
    return {
    }
  },
  methods: {
    xForRect(n:Number):Number {
      return (n - 1) * (this.breakpointValue / this.numUp);
    },
    pointsForRect(n:Number):String {
      const x:Number = this.xForRect(n) + 1;
      const w:Number = (this.breakpointValue / this.numUp);

      return `${x},170 ${x},40 ${x + w},40 ${x + w},170`;
    }
  }
}
</script>
