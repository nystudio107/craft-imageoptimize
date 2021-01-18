<template>
  <div class="w-full overflow-hidden">
    <sizes-visualization :id="id" v-for="sizesData in sizesDataList" v-bind="sizesData" :key="sizesData.breakpointValue" :widthMultiplier="widthMultiplier">
    </sizes-visualization>
  </div>
</template>

<script lang="ts">

import SizesVisualization from '../vue/SizesVisualization.vue';

const maxNormalizedWidth = 1000;

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
          breakpointValue: 800,
          breakpointUnits: 'px',
        },
      ]
    },
  },
  computed: {
    widthMultiplier() {
      let multiplier = 1;
      const largest = Math.max(...this.sizesDataList.map((sizesData:Object) => sizesData.breakpointValue));

      return largest > maxNormalizedWidth ? maxNormalizedWidth / largest : 1;
    }
  },
  data() {
    return {
    }
  },
  mounted() {
    console.log(this.widthMultiplier);
  },
  methods: {
  }
}
</script>
