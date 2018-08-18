// Dashboard main
const main = async () => {
    // Async load the vue module
    const Vue = await import(/* webpackChunkName: "vue" */ 'vue');
    // Create our vue instance
    const vm = new Vue.default({
        el: "#cp-nav-content",
        delimiters: ["${", "}"],
        components: {
            'confetti': () => import(/* webpackChunkName: "confetti" */ '../vue/Confetti.vue')
        },
        data: {
        },
        methods: {
        },
        mounted() {
        }
    });
};
// Execute async function
main().then({});
