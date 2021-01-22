<template>
  <svg width="100%" height="20px" xmlns="http://www.w3.org/2000/svg">
    <marker :id="`${ id }startarrow`" :markerWidth="markerWidth" :markerHeight="markerHeight"
            :refX="markerWidth" :refY="markerHeight / 2" orient="auto">
      <polygon :points="`${ markerWidth } 0, ${ markerWidth } ${ markerHeight }, 0 ${ markerHeight / 2 }`" :fill="strokeColor" />
    </marker>
    <marker :id="`${ id }endarrow`" :markerWidth="markerWidth" :markerHeight="markerHeight"
            :refX="markerWidth" :refY="markerHeight / 2" orient="auto" markerUnits="strokeWidth">
      <polygon :points="`0 0, ${ markerWidth } ${ markerHeight / 2 }, 0 ${ markerHeight }`" :fill="strokeColor" />
    </marker>
    <line :x1="markerWidth * 2" :y1="lineY" x2="100%" :y2="lineY" :stroke="strokeColor" stroke-width="2" :marker-end="`url(#${ id }endarrow)`" :marker-start="`url(#${ id }startarrow)`" />
    <text x="50%" :y="lineY + 4" text-anchor="middle" :stroke="fillColor" stroke-width="5">{{ label }}</text>
    <text x="50%" :y="lineY + 4" text-anchor="middle" :fill="strokeColor">{{ label }} </text>
  </svg>
</template>

<script lang="ts">
    export default {
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
            id: null,
            markerWidth: 4,
            markerHeight: 4,
            lineY: 12,
          }
        },
      mounted () {
        this.id = this._uid
      },
      methods: {
        }
    }
</script>
