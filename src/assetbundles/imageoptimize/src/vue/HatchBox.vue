<template>
  <g>
    <pattern :id="`diagonalHatch${id}`"
             patternUnits="userSpaceOnUse"
             width="4"
             height="4"
    >
      <path d="M-1,1 l2,-2
             M0,4 l4,-4
             M3,5 l2,-2"
            :stroke="hatchColor"
            :stroke-width="hatchWidth"
      />
    </pattern>

    <rect :x="x"
          :y="y"
          :width="width"
          :height="height"
          :stroke="strokeColor"
          :stroke-width="strokeWidth"
          :fill="`url(#diagonalHatch${id})`"
    />
  </g>
</template>

<script lang="ts">
export default {
  props: {
    x: Number,
    y: Number,
    width: Number,
    height: Number,
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
      id: null,
    }
  },
  mounted () {
    this.id = this._uid
  },
  methods: {
  }
}
</script>
