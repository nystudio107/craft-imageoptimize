// webpack.dev.js - developmental builds
const LEGACY_CONFIG = 'legacy';
const MODERN_CONFIG = 'modern';

// node modules
const path = require('path');

// webpack plugins
const merge = require('webpack-merge');
const webpack = require('webpack');

// config files
const pkg = require('./package.json');
const common = require('./webpack.common.js');

// Configure the webpack-dev-server
const configureDevServer = (buildType) => {
    return {
        contentBase: './web',
        host: '0.0.0.0',
        public: pkg.paths.dist.devPublic,
        https: false,
        hot: true,
        hotOnly: true,
        overlay: true,
        stats: 'errors-only',
        watchOptions: {
            poll: true
        },
        headers: {
            'Access-Control-Allow-Origin': '*'
        }
    };
};

// Development module exports
module.exports = [
    merge(
        common.legacyConfig,
        {
            output: {
                filename: path.join('./js', '[name]-legacy.[hash].js'),
                publicPath: pkg.paths.dist.devPublic + '/',
            },
            mode: 'development',
            devtool: 'inline-source-map',
            devServer: configureDevServer(LEGACY_CONFIG),
            plugins: [
                new webpack.HotModuleReplacementPlugin()
            ],
        }
    ),
    merge(
        common.modernConfig,
        {
            output: {
                filename: path.join('./js', '[name].[hash].js'),
                publicPath: pkg.paths.dist.devPublic + '/',
            },
            mode: 'development',
            devtool: 'inline-source-map',
            devServer: configureDevServer(MODERN_CONFIG),
            plugins: [
                new webpack.HotModuleReplacementPlugin()
            ],
        }
    ),
];
