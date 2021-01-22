<template>
  <div class="matrix" style="position: relative;">
    <div class="variant-blocks">
      <div v-for="sizesData in sizesDataList">
        <sizes-visualization
          :id="id"
          v-bind="sizesData"
          :key="sizesData.breakpointValue"
          :widthMultiplier="widthMultiplier"
        >
        </sizes-visualization>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import SizesVisualization from '../vue/SizesVisualization.vue';

const maxNormalizedWidth:number = 1000;

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
          numUp: 4,
          breakpointValue: 1280,
          breakpointUnits: 'px',
        },
        {
          numUp: 2,
          breakpointValue: 1024,
          breakpointUnits: 'px',
        },
        {
          numUp: 1,
          breakpointValue: 768,
          breakpointUnits: 'px',
        },
      ]
    },
  },
  computed: {
    widthMultiplier():number {
      let multiplier:number = 1;
      const largest:number = Math.max(...this.sizesDataList.map((sizesData:Object) => sizesData.breakpointValue));

      return largest > maxNormalizedWidth ? maxNormalizedWidth / largest : 1;
    }
  },
  data() {
    return {
      title: '',
    }
  },
  mounted() {
  },
  methods: {
  }
}
</script>
