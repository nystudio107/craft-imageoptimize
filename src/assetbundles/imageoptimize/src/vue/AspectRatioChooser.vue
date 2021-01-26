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
          ratioX: 16,
          ratioY: 9,
        },
        {
          ratioX: 8,
          ratioY: 5,
        },
        {
          ratioX: 4,
          ratioY: 3,
        },
        {
          ratioX: 5,
          ratioY: 4,
        },
        {
          ratioX: 1,
          ratioY: 1,
        },
        {
          ratioX: 4,
          ratioY: 5,
        },
        {
          ratioX: 3,
          ratioY: 4,
        },
        {
          ratioX: 5,
          ratioY: 8,
        },
        {
          ratioX: 9,
          ratioY: 16,
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
