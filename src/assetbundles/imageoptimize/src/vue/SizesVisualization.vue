<template>
  <div class="matrixblock">
    <div class="titlebar">
      <div class="blocktype">
        <code><span class="text-gray-500">sizes="</span>{{ title }}<span class="text-gray-500">"</span></code>
      </div>
      <div class="preview"></div>
    </div>
    <div class="actions">
    </div>

  <div class="fields">
    <svg
      :width="breakpointWidth"
      :viewBox="'0 0 ' + breakpointValue + ' 220'"
      preserveAspectRatio="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <arrow-line
        :id="id"
        :label="breakpointValue + breakpointUnits"
      />

      <rect x="1" y="20" :width="breakpointValue - 2" height="200" fill="#DDD" stroke="#AAA" stroke-width="2">
      </rect>

      <hatch-box x="0"
                 y="20"
                 :width="rowPaddingValue"
                 :height="200"
                 stroke="#AAA"
                 stroke-width="2"
                 hatch-color="#AAA"
      />
      <hatch-box :x="breakpointValue - rowPaddingValue"
                 :y="20"
                 :width="rowPaddingValue"
                 :height="200"
                 stroke="#AAA"
                 stroke-width="2"
                 hatch-color="#AAA"
      />

      <svg v-for="n in numUp">
        <hatch-box :x="cellX(n)"
                   :y="40"
                   :width="cellWidth"
                   :height="160"
                   stroke="rgb(163, 193, 226)"
                   stroke-width="2"
                   hatch-color="rgb(163, 193, 226)"
        />
        <aspect-ratio-box :numUp="numUp"
                          :x="imageX(n)"
                          :y="60"
                          :width="imageWidth"
                          :height="130"
        />
      </svg>
    </svg>
  </div>
  </div>
</template>

<script lang="ts">

import ArrowLine from './ArrowLine.vue';
import HatchBox from './HatchBox.vue';
import AspectRatioBox from "./AspectRatioBox.vue";

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
    AspectRatioBox,
    'arrow-line': ArrowLine,
    'hatch-box': HatchBox,
  },
  props: {
    id: {
      type: String,
      default: '',
    },
    numUp: {
      type: Number,
      default: 3,
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
    rowPaddingValue: {
      type: Number,
      default: 100,
    },
    rowPaddingUnits: {
      type: String,
      default: 'px',
    },
    cellPaddingValue: {
      type: Number,
      default: 20,
    },
    cellPaddingUnits: {
      type: String,
      default: 'px',
    },
  },
  computed: {
    breakpointWidth():string {
      let percent:number = (((this.breakpointValue * this.widthMultiplier) / maxNormalizedWidth) * 100);
      return percent + '%';
    },
    rowWidth():number {
      return this.breakpointValue - (this.rowPaddingValue * 2);
    },
    cellWidth():number {
      return this.rowWidth / this.numUp;
    },
    imageWidth():number {
      return Math.round(this.cellWidth - (this.cellPaddingValue * 2));
    },
    title():string {
      let vw:number = Math.round(100 / this.numUp);
      const displayBreakpoint: string = this.breakpointValue + this.breakpointUnits;
      const displayVw: string = Math.round(100 / this.numUp) + 'vw';
      const displayPadding: string = ((this.rowPaddingValue * 2) / this.numUp) + this.rowPaddingUnits;
      const displayCellPadding: string = (this.cellPaddingValue * 2) + this.cellPaddingUnits;
      const title: string =
        `(min-width: ${displayBreakpoint}) calc((${displayVw} - ${displayPadding}) - ${displayCellPadding})`;

      return title;
    }
  },
  data() {
    return {
    }
  },
  mounted() {
  },
  methods: {
    cellX(n:number):number {
      return this.rowPaddingValue + ((n - 1) * (this.rowWidth / this.numUp));
    },
    imageX(n:number):number {
      return this.cellPaddingValue + this.cellX(n);
    },
    xForRect(n:number):number {
      return (n - 1) * (this.breakpointValue / this.numUp);
    },
  }
}
</script>
