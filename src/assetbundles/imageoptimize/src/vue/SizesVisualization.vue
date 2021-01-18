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
    
    <svg v-for="n in numUp">
      <polyline :points="pointsForImagePoly(n)" stroke="rgb(163, 193, 226)" stroke-width="2" fill="rgb(221, 231, 242)"></polyline>
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
const maxNormalizedWidth:number = 1000;

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
    breakpointWidth():string {
      let percent:Number = (((this.breakpointValue * this.widthMultiplier) / maxNormalizedWidth) * 100);

      return percent + '%';
    }
  },
  data() {
    return {
    }
  },
  methods: {
    xForRect(n:number):number {
      return (n - 1) * (this.breakpointValue / this.numUp);
    },
    pointsForImagePoly(n:number):string {
      const x:number = this.xForRect(n) + 1;
      let x2:number = (this.breakpointValue / this.numUp) + x;
      const y:number = 40;
      let y2:number = 170;
      let polyPoints:string = `${x},${y2} ${x},${y} ${x2},${y} ${x2},${y2}`;
      let yStep:number = 10;
      let xStep:number = 10;
      // Loop through to add the "saw" look
      while (x2 > x) {
        polyPoints += ` ${x2},${y2}`;
        x2 -= xStep;
        y2 -= yStep;
        yStep *= -1;
      }
      polyPoints += ` ${x2},${y2}`;

      return polyPoints;
    }
  }
}
</script>
