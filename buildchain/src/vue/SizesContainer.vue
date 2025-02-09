<template>
  <div>
    <craft-field-wrapper
      instructions="Choose the aspect ratio that the images in this srcset should be displayed in"
      label="Aspect Ratio"
    >
      <aspect-ratio-chooser
        :ratio-x.sync="containerRatioX"
        :ratio-y.sync="containerRatioY"
        :use-aspect-ratio.sync="containerUseAspectRatio"
      />
    </craft-field-wrapper>
    <craft-field-wrapper
      instructions="Describe how the images will be laid out on the page for each CSS breakpoint"
      label="Image srcset"
    >
      <div
        class="matrix"
        style="position: relative;"
      >
        <div class="variant-blocks">
          <div
            v-for="(sizesData, index) in sizesDataList"
            :key="'sizes' + index"
          >
            <sizes-visualization
              :id="id"
              v-bind.sync="containerSizesDataList[index]"
              :ratio-x="ratioX"
              :ratio-y="ratioY"
              :use-aspect-ratio="useAspectRatio"
              :width-multiplier="widthMultiplier"
            />
          </div>
        </div>
      </div>
    </craft-field-wrapper>
  </div>
</template>

<script lang="ts">
import {defineComponent, PropType} from 'vue';
import SizesVisualization from '@/vue/SizesVisualization.vue';
import AspectRatioChooser from '@/vue/AspectRatioChooser.vue';
import CraftFieldWrapper from '@/vue/CraftFieldWrapper.vue';

const maxNormalizedWidth = 1000;

export default defineComponent({
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
      type: Array as PropType<SizesData[]>,
      default: <SizesData[]>[
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
      ],
    },
  },
  data() {
    return {
      title: '',
      containerRatioX: this.ratioX,
      containerRatioY: this.ratioY,
      containerUseAspectRatio: this.useAspectRatio,
      containerSizesDataList: this.sizesDataList,
    }
  },
  computed: {
    widthMultiplier(): number {
      let largest = 0;
      largest = Math.max(...this.sizesDataList.map((sizesData: SizesData) => sizesData.breakpointValue));

      return largest > maxNormalizedWidth ? maxNormalizedWidth / largest : 1;
    }
  },
  methods: {}
});
</script>
