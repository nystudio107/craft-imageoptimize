// webpack.common.js - common webpack config
const LEGACY_CONFIG = 'legacy';
const MODERN_CONFIG = 'modern';

// node modules
const path = require('path');
const merge = require('webpack-merge');

// webpack plugins
const CopyWebpackPlugin = require('copy-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const WebpackNotifierPlugin = require('webpack-notifier');

// config files
const pkg = require('./package.json');

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
                        '@babel/preset-env', {
                        modules: false,
                        useBuiltIns: 'entry',
                        targets: {
                            browsers: browserList,
                        },
                    }
                    ],
                ],
                plugins: [
                    '@babel/syntax-dynamic-import',
                    [
                        "@babel/transform-runtime", {
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

// Manifest
const configureManifest = (fileName) => {
    return {
        fileName: fileName,
        basePath: pkg.paths.manifest.basePath,
        map: (file) => {
            file.name = file.name.replace(/(\.[a-f0-9]{32})(\..*)$/, '$2');
            return file;
        },
    };
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
    plugins: [
        new WebpackNotifierPlugin({title: 'Webpack', excludeWarnings: true, alwaysNotify: true}),
        new VueLoaderPlugin(),
    ]
};

// Legacy webpack config
const legacyConfig = {
    module: {
        rules: [
            configureBabelLoader(Object.values(pkg.babelConfig.legacyBrowsers)),
            configureImageLoader(),
        ],
    },
    plugins: [
        new CopyWebpackPlugin(
            pkg.paths.copyFiles
        ),
        new ManifestPlugin(
            configureManifest('manifest-legacy.json')
        ),
    ]
};

// Modern webpack config
const modernConfig = {
    module: {
        rules: [
            configureBabelLoader(Object.values(pkg.babelConfig.modernBrowsers)),
        ],
    },
    plugins: [
        new ManifestPlugin(
            configureManifest('manifest.json')
        ),
    ]
};

// Common module exports
module.exports = {
    'legacyConfig': merge(
        legacyConfig,
        baseConfig,
    ),
    'modernConfig': merge(
        modernConfig,
        baseConfig,
    ),
};
