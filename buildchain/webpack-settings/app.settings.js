// app.settings.js

// node modules
require('dotenv').config();

// settings
module.exports = {
    alias: {
    },
    copyright: '©2020 nystudio107.com',
    entry: {
        'imageoptimize': '../src/assetbundles/imageoptimize/src/js/ImageOptimize.js',
        'welcome': '../src/assetbundles/imageoptimize/src/js/Welcome.js',
    },
    extensions: ['.ts', '.js', '.vue', '.json'],
    name: 'imageoptimize',
    paths: {
        dist: '../../src/assetbundles/imageoptimize/dist/',
    },
    urls: {
        publicPath: () => process.env.PUBLIC_PATH || '',
    },
};
