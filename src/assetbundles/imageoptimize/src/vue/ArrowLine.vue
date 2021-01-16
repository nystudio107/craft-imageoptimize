<template>
  <svg :width="width" height="20px">
    <marker :id="`${ name } startarrow`" :markerWidth="markerWidth" :markerHeight="markerHeight"
            :refX="markerWidth" :refY="markerHeight / 2" orient="auto">
      <polygon :points="`${ markerWidth } 0, ${ markerWidth } ${ markerHeight }, 0 ${ markerHeight / 2 }`" :fill="strokeColor" />
    </marker>
    <marker :id="`${ name }}endarrow`" :markerWidth="markerWidth" :markerHeight="markerHeight"
            refX="0" :refY="markerHeight / 2" orient="auto" markerUnits="strokeWidth">
      <polygon points="`0 0, ${ markerWidth } ${ markerHeight / 2 }, 0 ${ markerHeight }`" :fill="strokeColor" />
    </marker>
    <line :x1="markerWidth * 2" :y1="lineY" :x2="width - (markerWidth * 2)" :y2="lineY" :stroke="strokeColor" stroke-width="2" marker-end="`url(#${ name }endarrow)`" marker-start="`url(#${ name }startarrow)`" />
    <text :x="width / 2" :y="lineY + 4" text-anchor="middle" fill="white" stroke="white" stroke-width="5">{{ width }}w</text>
    <text :x="width / 2" :y="lineY + 4" text-anchor="middle" :fill="strokeColor">{{ width }}w</text>
  </svg>
</template>

<script>
    export default {
        props: {
          name: 'woof',
          width: 200,
        },
        data: () => ({
          strokeColor: '#AAA',
          fillColor: '#DDD',
          markerWidth: 4,
          markerHeight: 4,
          lineY: 12,
        }),
        methods: {
        }
    }
</script>
