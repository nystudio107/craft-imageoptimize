<template>
  <div>
    <craft-field-wrapper label="Aspect Ratio"
                       instructions="Choose the aspect ratio that the images in this srcset should be displayed in"
    >
      <aspect-ratio-chooser :ratio-x="ratioX"
                            :ratio-y="ratioY"
                            @aspectRatioSelected="onAspectRatioSelected"
      />
    </craft-field-wrapper>
    <craft-field-wrapper label="Image srcset"
                       instructions="Describe how the images will be laid out on the page for each CSS breakpoint"
    >
      <div class="matrix" style="position: relative;">
        <div class="variant-blocks">
          <div v-for="sizesData in sizesDataList">
            <sizes-visualization
              :id="id"
              v-bind.sync="sizesData"
              :key="sizesData.breakpointValue"
              :widthMultiplier="widthMultiplier"
              :ratio-x="ratioX"
              :ratio-y="ratioY"
              :use-aspect-ratio="useAspectRatio"
            >
            </sizes-visualization>
          </div>
        </div>
      </div>
    </craft-field-wrapper>
  </div>
</template>

<script lang="ts">
import SizesVisualization from './SizesVisualization.vue';
import AspectRatioChooser from './AspectRatioChooser.vue';
import CraftFieldWrapper from './CraftFieldWrapper.vue';

const maxNormalizedWidth:number = 1000;

export default {
  components: {
    'aspect-ratio-chooser': AspectRatioChooser,
    'sizes-visualization': SizesVisualization,
    'craft-field-wrapper': CraftFieldWrapper,
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
    useAspectRatio: {
      type: Boolean,
      default: true,
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
          rowPaddingValue: 100,
          rowPaddingUnits: 'px',
          cellPaddingValue: 20,
          cellPaddingUnits: 'px',
        },
        {
          numUp: 2,
          breakpointValue: 1024,
          breakpointUnits: 'px',
          rowPaddingValue: 100,
          rowPaddingUnits: 'px',
          cellPaddingValue: 20,
          cellPaddingUnits: 'px',
        },
        {
          numUp: 1,
          breakpointValue: 768,
          breakpointUnits: 'px',
          rowPaddingValue: 100,
          rowPaddingUnits: 'px',
          cellPaddingValue: 20,
          cellPaddingUnits: 'px',
        },
      ]
    },
  },
  computed: {
    widthMultiplier():number {
      let multiplier:number = 1;
      let largest:number = 0;
      largest = Math.max(...this.sizesDataList.map((sizesData:Object) => parseInt(sizesData.breakpointValue)));

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
    onUpdateSizesProp(val) {
      this.sizesDataList.forEach((sizesData, index) => {
        if (sizesData.index === val.index) {
          sizesData[val.prop] = val.value;
        }
      });
    },
    onAspectRatioSelected(val) {
      this.ratioX = val.ratioX;
      this.ratioY = val.ratioY;
      this.useAspectRatio = val.useAspectRatio;
    }
  }
}
</script>
