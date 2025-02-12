<template>
  <svg
    height="20px"
    width="100%"
    xmlns="http://www.w3.org/2000/svg"
  >
    <marker
      :id="`${ id }startArrow`"
      :markerHeight="markerHeight"
      :markerWidth="markerWidth"
      :refX="markerWidth"
      :refY="markerHeight / 2"
      orient="auto"
    >
      <polygon
        :fill="strokeColor"
        :points="`${ markerWidth } 0, ${ markerWidth } ${ markerHeight }, 0 ${ markerHeight / 2 }`"
      />
    </marker>
    <marker
      :id="`${ id }endArrow`"
      :markerHeight="markerHeight"
      :markerWidth="markerWidth"
      :refX="markerWidth"
      :refY="markerHeight / 2"
      markerUnits="strokeWidth"
      orient="auto"
    >
      <polygon
        :fill="strokeColor"
        :points="`0 0, ${ markerWidth } ${ markerHeight / 2 }, 0 ${ markerHeight }`"
      />
    </marker>
    <line
      :marker-end="`url(#${ id }endArrow)`"
      :marker-start="`url(#${ id }startArrow)`"
      :stroke="strokeColor"
      :x1="markerWidth * 2"
      :y1="lineY"
      :y2="lineY"
      stroke-width="2"
      x2="100%"
    />
    <text
      :stroke="fillColor"
      :y="lineY + 4"
      stroke-width="5"
      text-anchor="middle"
      x="50%"
    >
      {{ label }}
    </text>
    <text
      :fill="strokeColor"
      :y="lineY + 4"
      text-anchor="middle"
      x="50%"
    >
      {{ label }}
    </text>
  </svg>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import {nanoid} from "nanoid";

export default defineComponent({
  props: {
    label: {
      type: String,
      default: 'label',
    },
    strokeColor: {
      type: String,
      default: '#AAA',
    },
    fillColor: {
      type: String,
      default: '#FFF',
    }
  },
  data() {
    return {
      id: '',
      markerWidth: 4,
      markerHeight: 4,
      lineY: 12,
    }
  },
  mounted(): void {
    this.id = nanoid();
  },
  methods: {}
});
</script>
