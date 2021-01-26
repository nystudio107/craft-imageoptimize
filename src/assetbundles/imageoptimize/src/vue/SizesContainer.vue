<template>
  <div>
    <aspect-ratio-chooser :ratio-x="ratioX"
                          :ratio-y="ratioY"
                          @aspectRatioSelected="onAspectRatioSelected"
    />
    <div class="matrix" style="position: relative;">
      <div class="variant-blocks">
        <div v-for="sizesData in sizesDataList">
          <sizes-visualization
            :id="id"
            v-bind="sizesData"
            :key="sizesData.breakpointValue"
            :widthMultiplier="widthMultiplier"
            :ratio-x="ratioX"
            :ratio-y="ratioY"
          >
          </sizes-visualization>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import SizesVisualization from './SizesVisualization.vue';
import AspectRatioChooser from './AspectRatioChooser.vue';

const maxNormalizedWidth:number = 1000;

export default {
  components: {
    'aspect-ratio-chooser': AspectRatioChooser,
    'sizes-visualization': SizesVisualization,
  },
  props: {
    ratioX: {
      default: 16,
      type: Number
    },
    ratioY: {
      default: 9,
      type: Number
    },
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
    onAspectRatioSelected(val) {
      this.ratioX = val.ratioX;
      this.ratioY = val.ratioY;
    }
  }
}
</script>
