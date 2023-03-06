import Vue from 'vue';
import ConfettiParty from '@/vue/ConfettiParty.vue';

new Vue({
  el: "#cp-nav-content",
  components: {
    ConfettiParty,
  },
  data: {},
  methods: {},
});

// Accept HMR as per: https://vitejs.dev/guide/api-hmr.html
if (import.meta.hot) {
  import.meta.hot.accept(() => {
    console.log("HMR")
  });
}
