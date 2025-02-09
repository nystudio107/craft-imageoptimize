<template>
  <g>
    <pattern
      :id="`diagonalHatch${id}`"
      height="4"
      patternUnits="userSpaceOnUse"
      width="4"
    >
      <path
        :stroke="hatchColor"
        :stroke-width="hatchWidth"
        d="M-1,1 l2,-2
           M0,4 l4,-4
           M3,5 l2,-2"
      />
    </pattern>

    <rect
      :fill="`url(#diagonalHatch${id})`"
      :height="height"
      :stroke="strokeColor"
      :stroke-width="strokeWidth"
      :width="width"
      :x="x"
      :y="y"
    />
  </g>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import {nanoid} from 'nanoid'

export default defineComponent({
  props: {
    x: {
      type: Number,
      default: 1,
    },
    y: {
      type: Number,
      default: 1,
    },
    width: {
      type: Number,
      default: 1,
    },
    height: {
      type: Number,
      default: 1,
    },
    strokeColor: {
      type: String,
      default: '#AAA',
    },
    strokeWidth: {
      type: Number,
      default: 2,
    },
    hatchColor: {
      type: String,
      default: '#AAA',
    },
    hatchWidth: {
      type: Number,
      default: 1,
    }
  },
  data() {
    return {
      id: '',
    }
  },
  mounted(): void {
    this.id = nanoid();
  },
  methods: {}
});
</script>
