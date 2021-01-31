<template>
  <g>
    <polyline :points="pointsForImagePoly()"
              :stroke="strokeColor"
              :stroke-width="strokeWidth"
              :fill="fillColor"
              style="transition: point 2s ease;"
    />

    <svg :width="width - 10" :x="x + 5" y="60">
      <arrow-line
        v-if="showArrow"
        :label="width + 'w'"
        :stroke-color="'rgb(163, 193, 226)'"
        :fill-color="'rgb(221, 231, 242)'"
      />
    </svg>

    <svg v-if="showImage"
         :x="placeholderX()"
         y="10"
         :width="40"
         viewBox="0 0 185 170"
         xmlns="http://www.w3.org/2000/svg"
    >
      <path :fill="strokeColor"
            d="M15 130.896V16.994c0-1.097.898-1.994 2.007-1.994h150.986A2 2 0 0 1 170 16.994v69.147L133.015 51 87.26 120.377l-35.863-17.758L15 130.896zM2 0C.895 0 0 .887 0 2v166c0 1.105.887 2 2 2h181c1.105 0 2-.887 2-2V2c0-1.105-.887-2-2-2H2zm49 72c11.046 0 20-9.178 20-20.5S62.046 31 51 31s-20 9.178-20 20.5S39.954 72 51 72z"
            fill-rule="evenodd"
      />
    </svg>
  </g>
</template>

<script lang="ts">
import ArrowLine from '../vue/ArrowLine.vue';

export default {
  components: {
    'arrow-line': ArrowLine,
  },
  props: {
    x: Number,
    y: Number,
    width: Number,
    height: Number,
    strokeColor: {
      type: String,
      default: 'rgb(163, 193, 226)',
    },
    strokeWidth: {
      type: Number,
      default: 2,
    },
    fillColor: {
      type: String,
      default: 'rgb(221, 231, 242)',
    },
    hatchColor: {
      type: String,
      default: '#AAA',
    },
    sawtooth: {
      type: Boolean,
      default: true,
    },
    sawToothSize: {
      type: Number,
      default: 10,
    },
    showArrow: {
      type: Boolean,
      default: true,
    },
    showImage: {
      type: Boolean,
      default: true,
    }
  },
  data() {
    return {
      id: null,
    }
  },
  methods: {
    pointsForImagePoly(n:number):string {
      const x:number = this.x + 1;
      let x2:number = x + this.width;
      const y:number = this.y;
      let y2:number = this.y + this.height;
      const lastSawToothAdjust = this.width % (this.sawToothSize * 2);
      let polyPoints:string = `${x},${y2 - lastSawToothAdjust} ${x},${y} ${x2},${y} ${x2},${y2}`;
      if (this.sawtooth) {
        let yStep: number = this.sawToothSize;
        let xStep: number = this.sawToothSize;
        // Loop through to add the "saw" look
        while (x2 > x) {
          polyPoints += ` ${x2},${y2}`;
          x2 -= xStep;
          y2 -= yStep;
          yStep *= -1;
        }
        polyPoints += ` ${x},${y2 + x2 - x}`;
        polyPoints += ` ${x},${this.y + this.height - (this.sawToothSize * 2)}`;
      } else {
        polyPoints += ` ${x},${y2}`;
        polyPoints += ` ${x},${y2 - (this.sawToothSize * 2)}`;
      }

      return polyPoints;
    },
    placeholderX():number {
      return this.x + ((this.width / 2) - (this.placeholderWidth() / 2));
    },
    placeholderWidth():number {
      let calc:number = this.width / 5;
      return Math.min(calc, 50);
    },
  }
}
</script>
