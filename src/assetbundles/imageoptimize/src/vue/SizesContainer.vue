<template>
  <div width="100%">
    <sizes-visualization :id="id" v-for="sizesData in sizesDataList" v-bind="sizesData" :key="sizesData.breakpointValue">
    </sizes-visualization>
  </div>
</template>

<script lang="ts">

import SizesVisualization from '../vue/SizesVisualization.vue';

export default {
  components: {
    'sizes-visualization': SizesVisualization,
  },
  props: {
    id: {
      type: String,
      default: '',
    },
    sizesDataList: {
      type: Array,
      default: [
        {
          breakpointValue: 1000,
          breakpointUnits: 'px',
        },
        {
          breakpointValue: 600,
          breakpointUnits: 'px',
        },
      ]
    },
  },
  computed: {
  },
  data() {
    return {
    }
  },
  methods: {
  }
}
</script>
