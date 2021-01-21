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
      ></arrow-line>

      <pattern id="imageDiagonalHatch" patternUnits="userSpaceOnUse" width="4" height="4">
        <path d="M-1,1 l2,-2
             M0,4 l4,-4
             M3,5 l2,-2"
              style="stroke:rgb(163, 193, 226); stroke-width:1" />
      </pattern>

      <pattern id="paddingDiagonalHatch" patternUnits="userSpaceOnUse" width="4" height="4">
        <path d="M-1,1 l2,-2
             M0,4 l4,-4
             M3,5 l2,-2"
              style="stroke:#AAA; stroke-width:1" />
      </pattern>

      <rect x="1" y="20" :width="breakpointValue - 2" height="200" fill="#DDD" stroke="#AAA" stroke-width="2">
      </rect>

      <rect :x="0" y="20" :width="rowPaddingValue" height="200" stroke="#AAA" stroke-width="2" fill="url(#paddingDiagonalHatch)"></rect>

      <rect :x="breakpointValue - rowPaddingValue" y="20" :width="rowPaddingValue" height="200" stroke="#AAA" stroke-width="2" fill="url(#paddingDiagonalHatch)"></rect>

      <svg v-for="n in numUp">

        <rect :x="cellX(n)" y="40" :width="cellWidth" height="160" stroke="rgb(163, 193, 226)" stroke-width="2" fill="url(#imageDiagonalHatch)"></rect>

        <polyline :points="pointsForImagePoly(n)" stroke="rgb(163, 193, 226)" stroke-width="2" fill="rgb(221, 231, 242)">
        </polyline>

        <svg :width="imageWidth - 10" :x="imageX(n) + 5" y="60">
          <arrow-line
            :id="id"
            :label="imageWidth + 'w'"
            :stroke-color="'rgb(163, 193, 226)'"
            :fill-color="'rgb(221, 231, 242)'"
          ></arrow-line>
        </svg>

        <svg :x="placeholderX(n)" y="10" :width="placeholderWidth" viewBox="0 0 185 170" xmlns="http://www.w3.org/2000/svg">
          <path fill="rgb(163, 193, 226)" d="M15 130.896V16.994c0-1.097.898-1.994 2.007-1.994h150.986A2 2 0 0 1 170 16.994v69.147L133.015 51 87.26 120.377l-35.863-17.758L15 130.896zM2 0C.895 0 0 .887 0 2v166c0 1.105.887 2 2 2h181c1.105 0 2-.887 2-2V2c0-1.105-.887-2-2-2H2zm49 72c11.046 0 20-9.178 20-20.5S62.046 31 51 31s-20 9.178-20 20.5S39.954 72 51 72z" fill-rule="evenodd"/>
        </svg>
      </svg>
    </svg>
  </div>
  </div>
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
    placeholderWidth():number {
      let calc:number = 200 / this.numUp;
      return Math.min(calc, 50);
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
    placeholderX(n:number):number {
      return this.imageX(n) + ((this.imageWidth / 2) - (this.placeholderWidth / 2));
    },
    cellX(n:number):number {
      return this.rowPaddingValue + ((n - 1) * (this.rowWidth / this.numUp));
    },
    imageX(n:number):number {
      return this.cellPaddingValue + this.cellX(n);
    },
    xForRect(n:number):number {
      return (n - 1) * (this.breakpointValue / this.numUp);
    },
    pointsForImagePoly(n:number):string {
      const x:number = this.imageX(n) + 1;
      let x2:number = x + this.imageWidth;
      const y:number = 60;
      let y2:number = 185;
      const lastSawToothAdjust = this.imageWidth % 20;
      let polyPoints:string = `${x},${y2 - lastSawToothAdjust} ${x},${y} ${x2},${y} ${x2},${y2}`;
      let yStep:number = 10;
      let xStep:number = 10;
      // Loop through to add the "saw" look
      while (x2 > x) {
        polyPoints += ` ${x2},${y2}`;
        x2 -= xStep;
        y2 -= yStep;
        yStep *= -1;
      }
      polyPoints += ` ${x},${y2 + x2 - x}`;
      polyPoints += ` ${x},160`;

      return polyPoints;
    }
  }
}
</script>
