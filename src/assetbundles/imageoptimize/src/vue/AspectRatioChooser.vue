<template>
  <div>
    <div id="useAspectRatio-field" class="lightswitch-field field" aria-describedby="useAspectRatio-field-instructions">
      <div class="heading">
        <label id="" for="">Enforce Aspect Ratio</label>                                    </div>
      <div id="useAspectRatio-field-instructions" class="instructions"><p>Controls whether or not the aspect ratio should be enforced for this variant. When off, the vertical dimension retains the original image aspect ratio</p>
      </div>
      <div class="input ltr">
        <div id="useAspectRatio" class="lightswitch on" tabindex="0" role="switch" aria-checked="true" aria-labelledby="#lightswitchlabel1407378976">
          <div class="lightswitch-container" style="margin-left: 0px;">
            <div class="handle"></div>
          </div>
          <input type="hidden" name="types[useAspectRatio]" value="1">
        </div>
      </div>
    </div>
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
          ratioX: 4.5,
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
