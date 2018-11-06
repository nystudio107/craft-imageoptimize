// webpack.settings.js - webpack settings config

// node modules
require('dotenv').config();

// Webpack settings exports
// noinspection WebpackConfigHighlighting
module.exports = {
    name: "Image Optimize",
    copyright: "nystudio107",
    paths: {
        src: {
            base: "./src/assetbundles/imageoptimize/src/",
            css: "./src/assetbundles/imageoptimize/src/css/",
            js: "./src/assetbundles/imageoptimize/src/js/"
        },
        dist: {
            base: "./src/assetbundles/imageoptimize/dist/",
            clean: [
                "./img",
                "./css",
                "./js"
            ]
        },
        templates: "./src/templates/"
    },
    urls: {
        publicPath: ""
    },
    vars: {
        cssName: "styles"
    },
    entries: {
        "imageoptimize": "ImageOptimize.js",
        "welcome": "Welcome.js",
    },
    copyWebpackConfig: [
    ],
    devServerConfig: {
        public: () => process.env.DEVSERVER_PUBLIC || "http://localhost:8080",
        host: () => process.env.DEVSERVER_HOST || "localhost",
        poll: () => process.env.DEVSERVER_POLL || false,
        port: () => process.env.DEVSERVER_PORT || 8080,
        https: () => process.env.DEVSERVER_HTTPS || false,
    },
    manifestConfig: {
        basePath: ""
    },
    purgeCssConfig: {
        paths: [
            "./src/templates/**/*.{twig,html}",
            "./src/assetbundles/imageoptimize/src/vue/**/*.{vue,html}"
        ],
        whitelist: [
            "./src/assetbundles/imageoptimize/src/css/components/**/*.{css,pcss}"
        ],
        whitelistPatterns: [],
        extensions: [
            "html",
            "js",
            "twig",
            "vue"
        ]
    },
    saveRemoteFileConfig: [
    ],
    createSymlinkConfig: [
    ],
};
