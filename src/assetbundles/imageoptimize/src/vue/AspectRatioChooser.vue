<template>
  <div>
    <aspect-ratio-box v-for="aspectRatio in aspectRatios"
                      :selected="(ratioX === aspectRatio.ratioX) && (ratioY === aspectRatio.ratioY)"
                      :ratio-x="aspectRatio.ratioX"
                      :ratio-y="aspectRatio.ratioY"
                      :use-aspect-ratio="aspectRatio.useAspectRatio"
                      :containerSize="80"
                      :key="aspectRatio.ratioX + ':' + aspectRatio.ratioY"
                      v-on="$listeners"
    />
  </div>
</template>

<script lang="ts">
import AspectRatioBox from './AspectRatioBox.vue';

export default {
  components: {
    'aspect-ratio-box': AspectRatioBox,
  },
  props: {
    ratioX: Number,
    ratioY: Number,
    aspectRatios: {
      type: Array,
      default: [
        {
          ratioX: 4.01,
          ratioY: 3,
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
  data () {
    return {
    }
  },
  methods: {
  }
}
</script>
