// webpack.dev.js - developmental builds

// webpack plugins
const merge = require('webpack-merge');
// config files
const pkg = require('./package.json');
const common = require('./webpack.common.js');

// Development module exports
module.exports = [
    merge(
        common.legacyConfig,
        {
            mode: 'development',
            devtool: 'inline-source-map'
        }
    ),
    merge(
        common.modernConfig,
        {
            mode: 'development',
            devtool: 'inline-source-map'
        }
    ),
];
