// webpack.common.js - common webpack config

// node modules
const webpack = require('webpack');
const path = require('path');
const git = require('git-rev-sync');
const moment = require('moment');
const merge = require('webpack-merge');
// webpack plugins
const CleanWebpackPlugin = require('clean-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const VueLoaderPlugin = require('vue-loader/lib/plugin');
// config files
const pkg = require('./package.json');

// File banner banner
const configureBanner = () => {
    return {
        banner: [
            '/*!',
            ' * @project        ' + pkg.copyright,
            ' * @name           ' + '[filebase]',
            ' * @author         ' + pkg.author,
            ' * @build          ' + moment().format('llll') + ' ET',
            ' * @release        ' + git.long() + ' [' + git.branch() + ']',
            ' * @copyright      Copyright (c) ' + moment().format('YYYY') + ' ' + pkg.copyright,
            ' *',
            ' */',
            ''
        ].join('\n'),
        raw: true
    };
};

// Vue loader
const configureVueLoader = () => {
    return {
        test: /\.vue$/,
        loader: 'vue-loader'
    };
};

// Babel loader
const configureBabelLoader = (browserList) => {
    return {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
            loader: 'babel-loader',
            options: {
                presets: [
                    [
                        'env', {
                        modules: false,
                        useBuiltIns: true,
                        targets: {
                            browsers: browserList,
                        },
                    }
                    ],
                ],
                plugins: [
                    'syntax-dynamic-import',
                    [
                        "transform-runtime", {
                        "polyfill": false,
                        "regenerator": true
                    }
                    ]
                ],
            },
        },
    };
};

// Image loader
const configureImageLoader = () => {
    return {
        test: /\.png|jpe?g|gif|svg$/,
        loader: 'file-loader',
        options: {
            name: 'images/[name].[hash].[ext]'
        }
    };
};

// Postcss loader
const configurePostcssLoader = (build) => {
    if (build) {
        return {
            test: /\.pcss$/,
            use: [
                MiniCssExtractPlugin.loader,
                {
                    loader: 'css-loader',
                    options: {
                        importLoaders: 1,
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
    } else {
        return {
            test: /\.pcss$/,
            loader: 'ignore-loader'
        };
    }
};

// Entries from package.json
const configureEntries = () => {
    let entries = {};

    for (const [key, value] of Object.entries(pkg.entries)) {
        entries[key] = path.resolve(__dirname, pkg.paths.src.js + value);
    }

    return entries;
};

// The base webpack config
const baseConfig = {
    name: pkg.name,
    entry: configureEntries(),
    output: {
        path: path.resolve(__dirname, pkg.paths.dist.base),
        publicPath: pkg.paths.dist.public
    },
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js'
        }
    },
    module: {
        rules: [
            configureVueLoader(),
        ],
    },
    optimization: {
        splitChunks: {},
    },
    plugins: [
        new VueLoaderPlugin(),
        new webpack.BannerPlugin(configureBanner()),
    ]
};

// Legacy webpack config
const legacyConfig = {
    output: {
        filename: path.join('./js', '[name]-legacy.js'),
    },
    module: {
        rules: [
            configureBabelLoader(Object.values(pkg.babelConfig.legacyBrowsers)),
            configureImageLoader(),
            configurePostcssLoader(true),
        ],
    },
    plugins: [
        new CleanWebpackPlugin(pkg.paths.dist.clean, {
            root: path.resolve(__dirname, pkg.paths.dist.base),
            verbose: true,
            dry: false
        }),
        new MiniCssExtractPlugin({
            path: path.resolve(__dirname, pkg.paths.dist.base),
            filename: path.join('./css', '[name].css'),
            chunkFilename: "[id].css"
        }),
        new CopyWebpackPlugin([]),
    ]
};

// Modern webpack config
const modernConfig = {
    output: {
        filename: path.join('./js', '[name].js'),
    },
    module: {
        rules: [
            configureBabelLoader(Object.values(pkg.babelConfig.modernBrowsers)),
            configurePostcssLoader(false),
        ],
    },
};

// Common module exports
module.exports = {
    'legacyConfig': merge(
        baseConfig,
        legacyConfig,
    ),
    'modernConfig': merge(
        baseConfig,
        modernConfig,
    ),
};
