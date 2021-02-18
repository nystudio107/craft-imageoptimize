// app.settings.js

// node modules
require('dotenv').config();
const path = require('path');

// settings
module.exports = {
    alias: {
        '@css': path.resolve('../src/assetbundles/imageoptimize/src/css'),
        '@img': path.resolve('../src/assetbundles/imageoptimize/src/img'),
        '@js': path.resolve('../src/assetbundles/imageoptimize/src/js'),
        '@vue': path.resolve('../src/assetbundles/imageoptimize/src/vue'),
    },
    copyright: '©2020 nystudio107.com',
    entry: {
        'imageoptimize': '@js/ImageOptimize.js',
        'field': '@js/OptimizedImagesField.js',
        'welcome': '@js/Welcome.js',
    },
    extensions: ['.ts', '.js', '.vue', '.json'],
    name: 'imageoptimize',
    paths: {
        dist: path.resolve('../src/assetbundles/imageoptimize/dist/'),
    },
    urls: {
        publicPath: () => process.env.PUBLIC_PATH || '',
    },
};
