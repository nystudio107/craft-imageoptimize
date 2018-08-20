// webpack.common.js - common webpack config

const CSS_CONFIG = 'css';
const LEGACY_CONFIG = 'legacy';
const MODERN_CONFIG = 'modern';

// node modules
const path = require('path');
const merge = require('webpack-merge');
// webpack plugins
const CleanWebpackPlugin = require('clean-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin')

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
const configurePostcssLoader = (buildType) => {
    if (buildType === LEGACY_CONFIG) {
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
    }
    if (buildType === MODERN_CONFIG) {
        return {
            test: /\.pcss$/,
            loader: 'ignore-loader'
        };
    }
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

// HtmlWebpackPlugin twig manifest macros
const configureHtmlWebpack = (buildType) => {
    if (buildType === LEGACY_CONFIG) {
        return {
            inject: false,
            hash: false,
            template: path.resolve(__dirname, pkg.paths.manifest.template.twigLegacy),
            filename: path.resolve(__dirname, pkg.paths.manifest.filename.twigLegacy),
        };
    }
    if (buildType === MODERN_CONFIG) {
        return {
            inject: false,
            hash: false,
            template: path.resolve(__dirname, pkg.paths.manifest.template.twigModern),
            filename: path.resolve(__dirname, pkg.paths.manifest.filename.twigModern),
        };
    }
    if (buildType === CSS_CONFIG) {
        return {
            inject: false,
            hash: false,
            template: path.resolve(__dirname, pkg.paths.manifest.template.twigCss),
            filename: path.resolve(__dirname, pkg.paths.manifest.filename.twigCss),
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
    ]
};

// Legacy webpack config
const legacyConfig = {
    output: {
        filename: path.join('./js', '[name]-legacy.[chunkhash].js'),
    },
    module: {
        rules: [
            configureBabelLoader(Object.values(pkg.babelConfig.legacyBrowsers)),
            configureImageLoader(),
            configurePostcssLoader(LEGACY_CONFIG),
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
            filename: path.join('./css', '[name].[chunkhash].css'),
            chunkFilename: "[id].css"
        }),
        new CopyWebpackPlugin(
            pkg.paths.copyFiles
        ),
        new ManifestPlugin(
            configureManifest('manifest-legacy.json')
        ),
        new HtmlWebpackPlugin(
            configureHtmlWebpack(LEGACY_CONFIG)
        ),
        new HtmlWebpackPlugin(
            configureHtmlWebpack(CSS_CONFIG)
        ),
    ]
};

// Modern webpack config
const modernConfig = {
    output: {
        filename: path.join('./js', '[name].[chunkhash].js'),
    },
    module: {
        rules: [
            configureBabelLoader(Object.values(pkg.babelConfig.modernBrowsers)),
            configurePostcssLoader(MODERN_CONFIG),
        ],
    },
    plugins: [
        new ManifestPlugin(
            configureManifest('manifest.json')
        ),
        new HtmlWebpackPlugin(
            configureHtmlWebpack(MODERN_CONFIG)
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
