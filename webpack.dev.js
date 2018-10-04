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

// Postcss loader
const configurePostcssLoader = (buildType) => {
    // Don't generate CSS for the legacy config in development
    if (buildType === LEGACY_CONFIG) {
        return {
            test: /\.(pcss|css)$/,
            loader: 'ignore-loader'
        };
    }
    if (buildType === MODERN_CONFIG) {
        return {
            test: /\.(pcss|css)$/,
            use: [
                {
                    loader: 'style-loader',
                },
                {
                    loader: 'css-loader',
                    options: {
                        importLoaders: 2,
                        sourceMap: true
                    }
                },
                {
                    loader: 'resolve-url-loader'
                },
                {
                    loader: 'postcss-loader',
                    options: {
                        sourceMap: true
                    }
                }
            ]
        };
    }
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
            module: {
                rules: [
                    configurePostcssLoader(LEGACY_CONFIG),
                ],
            },
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
            module: {
                rules: [
                    configurePostcssLoader(MODERN_CONFIG),
                ],
            },
            plugins: [
                new webpack.HotModuleReplacementPlugin()
            ],
        }
    ),
];
