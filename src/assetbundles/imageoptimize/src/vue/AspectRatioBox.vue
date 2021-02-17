<template>
  <div
    class="inline-block p-2 cursor-pointer"
    @click="handleClick"
  >
    <svg
      :width="containerSize"
      :height="containerSize"
      preserveAspectRatio="xMidYMid meet"
      xmlns="http://www.w3.org/2000/svg"
    >
      <rect
        x="0"
        y="0"
        :width="containerSize"
        :height="containerSize"
        :stroke="strokeColor"
        stroke-width="4"
        :fill="fillColor"
        stroke-opacity="0.5"
        fill-opacity="0.0"
        stroke-dasharray="5, 5"
      />

      <image-preview-box
        :x="0"
        :y="1"
        :width="width"
        :height="height"
        :stroke-width="2"
        :sawtooth="!useAspectRatio"
        :saw-tooth-size="5"
        :show-arrow="false"
        :show-image="false"
        :stroke-color="strokeColor"
        :fill-color="fillColor"
      />

      <text
        :x="width / 2"
        :y="height / 2"
        :fill="strokeColor"
        text-anchor="middle"
        alignment-baseline="central"
        :font-size="containerSize / 5"
      >
        {{ displayText }}
      </text>

    </svg>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import ImagePreviewBox from "@vue/ImagePreviewBox.vue";

export default Vue.extend({
  components: {
    'image-preview-box': ImagePreviewBox,
  },
  props: {
    selected: {
      type: Boolean,
      default: false,
    },
    ratioX: {
      type: Number,
      default: 1,
    },
    ratioY: {
      type: Number,
      default: 1,
    },
    useAspectRatio: {
      type: Boolean,
      default: true,
    },
    containerSize: {
      type: Number,
      default: 100,
    },
  },
  data(): Record<string, unknown> {
    return {
      id: null,
    }
  },
  computed: {
    displayText():string {
      if (this.useAspectRatio) {
        return `${this.ratioX}:${this.ratioY}`;
      } else {
        return `none`;
      }
    },
    strokeColor() {
      if (this.selected) {
        return 'rgb(163, 193, 226)';
      }

      return '#AAA';
    },
    fillColor() {
    if (this.selected) {
      return 'rgb(221, 231, 242)';
    }

    return '#DDD';
    },
    aspectRatio() {
      return this.ratioX / this.ratioY;
    },
    width() {
      if (!this.useAspectRatio) {
        return this.containerSize - 2;
      }
      let w:number = this.containerSize / 2;
      if (this.aspectRatio > 1.0) {
        w = (this.containerSize / 2) * this.aspectRatio;
      }

      return w;
    },
    height() {
      if (!this.useAspectRatio) {
        return this.containerSize / 1.5;
      }
      let h:number = this.containerSize / 2;
      if (this.aspectRatio < 1.0) {
        h = (this.containerSize / 2) / this.aspectRatio;
      }

      return h;
    }
  },
  mounted () {
    this.id = this._uid;
  },
  methods: {
    handleClick() {
      this.$emit('update:ratio-x', this.ratioX);
      this.$emit('update:ratio-y', this.ratioY);
      this.$emit('update:use-aspect-ratio', this.useAspectRatio);
    }
  }
});
</script>
