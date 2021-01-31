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
        :viewBox="'0 0 ' + breakpointValue + ' ' + (calcHeight(imageWidth) + 100)"
        preserveAspectRatio="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <arrow-line
          :label="breakpointValue + breakpointUnits"
        />
        <rect x="1"
              y="20"
              :width="breakpointValue - 2"
              :height="calcHeight(imageWidth) + 90"
              fill="#DDD"
              stroke="#AAA"
              stroke-width="2"
        />
        <hatch-box x="0"
                   y="20"
                   :width="rowPaddingValue"
                   :height="calcHeight(imageWidth) + 90"
                   stroke-color="#AAA"
                   :stroke-width="2"
                   hatch-color="#AAA"
        />
        <hatch-box :x="breakpointValue - rowPaddingValue"
                   :y="20"
                   :width="rowPaddingValue"
                   :height="calcHeight(imageWidth) + 90"
                   stroke-color="#AAA"
                   :stroke-width="2"
                   hatch-color="#AAA"
        />
        <svg v-for="n in numUp">
          <hatch-box :x="cellX(n)"
                     :y="40"
                     :width="cellWidth"
                     :height="calcHeight(imageWidth) + 40"
                     stroke-color="rgb(163, 193, 226)"
                     :stroke-width="2"
                     hatch-color="rgb(163, 193, 226)"
          />
          <image-preview-box :x="imageX(n)"
                             :y="60"
                             :width="imageWidth"
                             :height="calcHeight(imageWidth)"
                             :sawtooth="!useAspectRatio"
          />
        </svg>
      </svg>
    </div>
  </div>
</template>

<script lang="ts">
import ArrowLine from './ArrowLine.vue';
import HatchBox from './HatchBox.vue';
import ImagePreviewBox from "./ImagePreviewBox.vue";

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
    'image-preview-box': ImagePreviewBox,
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
    ratioX: {
      type: Number,
      default: 16,
    },
    ratioY: {
      type: Number,
      default: 9,
    },
    useAspectRatio: {
      type: Boolean,
      default: true,
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
    calcHeight(w:number):number {
      const calcHeight:number = w * (this.ratioY / this.ratioX);

      return calcHeight;
    },
  }
}
</script>
