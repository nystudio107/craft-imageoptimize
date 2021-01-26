<template>
  <div class="inline-block p-2 cursor-pointer"
       @click="handleClick"
  >
    <svg
      :width="containerSize"
      :height="containerSize"
      preserveAspectRatio="xMidYMid meet"
      xmlns="http://www.w3.org/2000/svg"
    >
      <rect x="0"
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

      <rect x="1"
            y="1"
            :width="width"
            :height="height"
            :stroke="strokeColor"
            :stroke-width="2"
            :fill="fillColor"
      />
      <text :x="width / 2"
            :y="height / 2"
            :fill="strokeColor"
            text-anchor="middle"
            alignment-baseline="central"
            :font-size="containerSize / 5">
        {{ ratioX }}:{{ ratioY }}
      </text>

    </svg>
  </div>
</template>

<script lang="ts">
export default {
  props: {
    selected: {
      type: Boolean,
      default: false,
    },
    ratioX: Number,
    ratioY: Number,
    containerSize: {
      type: Number,
      default: 100,
    },
  },
  computed: {
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
      let w:number = this.containerSize / 2;
      if (this.aspectRatio > 1.0) {
        w = (this.containerSize / 2) * this.aspectRatio;
      }

      return w;
    },
    height() {
      let h:number = this.containerSize / 2;
      if (this.aspectRatio < 1.0) {
        h = (this.containerSize / 2) / this.aspectRatio;
      }

      console.log(h);
      return h;
    }
  },
  data() {
    return {
      id: null,
    }
  },
  mounted () {
    this.id = this._uid
  },
  methods: {
    handleClick() {
      this.$emit('aspectRatioSelected', { ratioX: this.ratioX, ratioY: this.ratioY } );
    }
  }
}
</script>
