<template>
  <div>
    <aspect-ratio-box
      v-for="aspectRatio in aspectRatios"
      :key="aspectRatio.ratioX + ':' + aspectRatio.ratioY"
      :container-size="80"
      :ratio-x="aspectRatio.ratioX"
      :ratio-y="aspectRatio.ratioY"
      :selected="(ratioX === aspectRatio.ratioX) && (ratioY === aspectRatio.ratioY)"
      :use-aspect-ratio="aspectRatio.useAspectRatio"
      v-on="$listeners"
    />
  </div>
</template>

<script lang="ts">
import {defineComponent, PropType} from 'vue';
import AspectRatioBox from '@/vue/AspectRatioBox.vue';

export default defineComponent({
  components: {
    'aspect-ratio-box': AspectRatioBox,
  },
  props: {
    ratioX: {
      type: Number,
      default: 1,
    },
    ratioY: {
      type: Number,
      default: 1,
    },
    aspectRatios: {
      type: Array as PropType<AspectRatioObj[]>,
      default: <AspectRatioObj[]>[
        {
          ratioX: 16.01,
          ratioY: 9,
          useAspectRatio: false,
        },
        {
          ratioX: 16,
          ratioY: 9,
          useAspectRatio: true,
        },
        {
          ratioX: 8,
          ratioY: 5,
          useAspectRatio: true,
        },
        {
          ratioX: 4,
          ratioY: 3,
          useAspectRatio: true,
        },
        {
          ratioX: 5,
          ratioY: 4,
          useAspectRatio: true,
        },
        {
          ratioX: 1,
          ratioY: 1,
          useAspectRatio: true,
        },
        {
          ratioX: 4,
          ratioY: 5,
          useAspectRatio: true,
        },
        {
          ratioX: 3,
          ratioY: 4,
          useAspectRatio: true,
        },
        {
          ratioX: 5,
          ratioY: 8,
          useAspectRatio: true,
        },
        {
          ratioX: 9,
          ratioY: 16,
          useAspectRatio: true,
        },
      ]
    },
  },
  data() {
    return {}
  },
  methods: {}
});
</script>
