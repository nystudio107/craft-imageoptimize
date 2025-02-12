<template>
  <div class="matrixblock">
    <div class="titlebar">
      <div class="blocktype">
        <code><span class="text-gray-500">srcset="</span>example.jpg {{ imageWidth }}w<span
          class="text-gray-500"
        >"</span></code>
        <code><span class="text-gray-500">sizes="</span>{{ title }}<span class="text-gray-500">"</span></code>
      </div>
      <div class="preview"/>
    </div>
    <div class="actions"/>

    <div class="fields">
      <div class="flex-fields">
        <craft-number-field
          :max="2560"
          :min="300"
          :size="10"
          :value="breakpointValue"
          field="breakpointValue"
          instructions=""
          label="CSS breakpoint"
          name="types[breakpointValue]"
          v-on="$listeners"
        />

        <craft-number-field
          :max="8"
          :min="1"
          :size="5"
          :value="numUp"
          field="numUp"
          instructions=""
          label="Images per row"
          name="types[numUp]"
          v-on="$listeners"
        />

        <craft-number-field
          :max="1000"
          :min="0"
          :size="10"
          :value="rowPaddingValue"
          field="rowPaddingValue"
          instructions=""
          label="Row Padding"
          name="types[rowPaddingValue]"
          v-on="$listeners"
        />

        <craft-number-field
          :max="1000"
          :min="0"
          :size="10"
          :value="cellPaddingValue"
          field="cellPaddingValue"
          instructions=""
          label="Cell Padding"
          name="types[cellPaddingValue]"
          v-on="$listeners"
        />
      </div>
      <div class="field">
        <svg
          :viewBox="'0 0 ' + breakpointValue + ' ' + (calcHeight(imageWidth) + 100)"
          :width="breakpointWidth"
          preserveAspectRatio="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <arrow-line
            :label="breakpointValue + breakpointUnits"
          />
          <rect
            :height="calcHeight(imageWidth) + 80"
            :width="breakpointValue - 2"
            :x="1"
            :y="20"
            fill="#DDD"
            stroke="#AAA"
            stroke-width="2"
          />
          <hatch-box
            :height="calcHeight(imageWidth) + 80"
            :stroke-width="2"
            :width="rowPaddingValue"
            :x="0"
            :y="20"
            hatch-color="#AAA"
            stroke-color="#AAA"
          />
          <hatch-box
            :height="calcHeight(imageWidth) + 80"
            :stroke-width="2"
            :width="rowPaddingValue"
            :x="breakpointValue - rowPaddingValue"
            :y="20"
            hatch-color="#AAA"
            stroke-color="#AAA"
          />
          <svg
            v-for="(n,i) in numUp"
            :key="'svg' + i"
          >
            <hatch-box
              :height="calcHeight(imageWidth) + 40"
              :stroke-width="2"
              :width="cellWidth"
              :x="cellX(n)"
              :y="40"
              hatch-color="rgb(163, 193, 226)"
              stroke-color="rgb(163, 193, 226)"
            />
            <image-preview-box
              :height="calcHeight(imageWidth)"
              :sawtooth="!useAspectRatio"
              :width="imageWidth"
              :x="imageX(n)"
              :y="60"
            />
          </svg>
        </svg>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import ArrowLine from '@/vue/ArrowLine.vue';
import HatchBox from '@/vue/HatchBox.vue';
import ImagePreviewBox from "@/vue/ImagePreviewBox.vue";
import CraftNumberField from "@/vue/CraftNumberField.vue";

//const remPx = 16;
//const emPx = 16;
const maxNormalizedWidth = 1000;
/*
const normalizeUnitsToPx = (value: number, units: string) => {
  let result: number;
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
*/

export default defineComponent({
  components: {
    'image-preview-box': ImagePreviewBox,
    'craft-number-field': CraftNumberField,
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
  data() {
    return {}
  },
  computed: {
    breakpointWidth(): string {
      let percent: number = (((this.breakpointValue * this.widthMultiplier) / maxNormalizedWidth) * 100);
      return percent + '%';
    },
    rowWidth(): number {
      return this.breakpointValue - (this.rowPaddingValue * 2);
    },
    cellWidth(): number {
      return this.rowWidth / this.numUp;
    },
    imageWidth(): number {
      return Math.round(this.cellWidth - (this.cellPaddingValue * 2));
    },
    title(): string {
      let vw: number = Math.round(100 / this.numUp);
      const displayBreakpoint: string = this.breakpointValue + this.breakpointUnits;
      const displayVw: string = vw + 'vw';
      const displayPadding: string = (Math.round((this.rowPaddingValue * 2) / this.numUp)) + this.rowPaddingUnits;
      const displayCellPadding: string = (this.cellPaddingValue * 2) + this.cellPaddingUnits;

      return `(min-width: ${displayBreakpoint}) calc((${displayVw} - ${displayPadding}) - ${displayCellPadding})`;
    }
  },
  methods: {
    cellX(n: number): number {
      return this.rowPaddingValue + ((n - 1) * (this.rowWidth / this.numUp));
    },
    imageX(n: number): number {
      return this.cellPaddingValue + this.cellX(n);
    },
    xForRect(n: number): number {
      return (n - 1) * (this.breakpointValue / this.numUp);
    },
    calcHeight(w: number): number {
      return w * (this.ratioY / this.ratioX);
    },
  }
});
</script>
