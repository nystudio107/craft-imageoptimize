<template>
  <div>
    <craft-field-wrapper
      label="Aspect Ratio"
      instructions="Choose the aspect ratio that the images in this srcset should be displayed in"
    >
      <aspect-ratio-chooser
        :ratio-x.sync="ratioX"
        :ratio-y.sync="ratioY"
        :use-aspect-ratio.sync="useAspectRatio"
      />
    </craft-field-wrapper>
    <craft-field-wrapper
      label="Image srcset"
      instructions="Describe how the images will be laid out on the page for each CSS breakpoint"
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
              v-bind.sync="sizesData"
              :width-multiplier="widthMultiplier"
              :ratio-x="ratioX"
              :ratio-y="ratioY"
              :use-aspect-ratio="useAspectRatio"
            />
          </div>
        </div>
      </div>
    </craft-field-wrapper>
  </div>
</template>

<script lang="ts">
import Vue, { PropType } from 'vue';
import SizesVisualization from '@vue/SizesVisualization.vue';
import AspectRatioChooser from '@vue/AspectRatioChooser.vue';
import CraftFieldWrapper from '@vue/CraftFieldWrapper.vue';

const maxNormalizedWidth = 1000;

export default Vue.extend({
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
  data(): Record<string, unknown> {
    return {
      title: '',
    }
  },
  computed: {
    widthMultiplier():number {
      let largest = 0;
      largest = Math.max(...this.sizesDataList.map((sizesData:SizesData) => parseInt(sizesData.breakpointValue)));

      return largest > maxNormalizedWidth ? maxNormalizedWidth / largest : 1;
    }
  },
  methods: {}
});
</script>
