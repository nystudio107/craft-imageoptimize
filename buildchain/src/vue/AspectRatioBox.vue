<template>
  <div
    class="inline-block p-2 cursor-pointer"
    @click="handleClick()"
  >
    <svg
      :height="containerSize"
      :width="containerSize"
      preserveAspectRatio="xMidYMid meet"
      xmlns="http://www.w3.org/2000/svg"
    >
      <rect
        :fill="fillColor"
        :height="containerSize"
        :stroke="strokeColor"
        :width="containerSize"
        fill-opacity="0.0"
        stroke-dasharray="5, 5"
        stroke-opacity="0.5"
        stroke-width="4"
        x="0"
        y="0"
      />

      <image-preview-box
        :fill-color="fillColor"
        :height="height"
        :saw-tooth-size="5"
        :sawtooth="!useAspectRatio"
        :show-arrow="false"
        :show-image="false"
        :stroke-color="strokeColor"
        :stroke-width="2"
        :width="width"
        :x="0"
        :y="1"
      />

      <text
        :fill="strokeColor"
        :font-size="containerSize / 5"
        :x="width / 2"
        :y="height / 2"
        alignment-baseline="central"
        text-anchor="middle"
      >
        {{ displayText }}
      </text>

    </svg>
  </div>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import ImagePreviewBox from "@/vue/ImagePreviewBox.vue";
import {nanoid} from "nanoid";

export default defineComponent({
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
  data() {
    return {
      id: '',
    }
  },
  computed: {
    displayText(): string {
      if (this.useAspectRatio) {
        return `${this.ratioX}:${this.ratioY}`;
      } else {
        return `none`;
      }
    },
    strokeColor(): string {
      if (this.selected) {
        return 'rgb(163, 193, 226)';
      }

      return '#AAA';
    },
    fillColor(): string {
      if (this.selected) {
        return 'rgb(221, 231, 242)';
      }

      return '#DDD';
    },
    aspectRatio(): number {
      return this.ratioX / this.ratioY;
    },
    width() {
      if (!this.useAspectRatio) {
        return this.containerSize - 2;
      }
      let w: number = this.containerSize / 2;
      if (this.aspectRatio > 1.0) {
        w = (this.containerSize / 2) * this.aspectRatio;
      }

      return w;
    },
    height() {
      if (!this.useAspectRatio) {
        return this.containerSize / 1.5;
      }
      let h: number = this.containerSize / 2;
      if (this.aspectRatio < 1.0) {
        h = (this.containerSize / 2) / this.aspectRatio;
      }

      return h;
    }
  },
  mounted() {
    this.id = nanoid();
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
