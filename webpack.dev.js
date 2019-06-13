// node modules
const merge = require('webpack-merge');
const path = require('path');
const webpack = require('webpack');

// webpack plugins
const DashboardPlugin = require('webpack-dashboard/plugin');

// config files
const common = require('./webpack.common.js');
const pkg = require('./package.json');
const settings = require('./webpack.settings.js');

// Configure the webpack-dev-server
const configureDevServer = (buildType) => {
    return {
        public: settings.devServerConfig.public(),
        contentBase: path.resolve(__dirname, settings.paths.templates),
        host: settings.devServerConfig.host(),
        port: settings.devServerConfig.port(),
        https: !!parseInt(settings.devServerConfig.https()),
        disableHostCheck: true,
        hot: true,
        overlay: true,
        watchContentBase: true,
        watchOptions: {
            poll: !!parseInt(settings.devServerConfig.poll()),
            ignored: /node_modules/,
        },
        headers: {
            'Access-Control-Allow-Origin': '*'
        },
    };
};

// Configure Image loader
const configureImageLoader = () => {
    return {
        test: /\.(png|jpe?g|gif|svg|webp)$/i,
        use: [
            {
                loader: 'file-loader',
                options: {
                    name: 'img/[name].[ext]'
                }
            }
        ]
    };
};

// Configure optimization
const configureOptimization = () => {
    return {
        splitChunks: {
            cacheGroups: {
                vendor: {
                    test: /node_modules/,
                    chunks: "initial",
                    name: "vendor",
                    priority: 10,
                    enforce: true
                }
            }
        },
    };
};

// Configure the Postcss loader
const configurePostcssLoader = () => {
    return {
        test: /\.(pcss|css)$/,
        use: [
            {
                loader: 'style-loader',
            },
            {
                loader: 'vue-style-loader',
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
};

// Development module exports
module.exports = [
    merge(
        common.legacyConfig,
        {
            output: {
                filename: path.join('./js', '[name].js'),
                publicPath: settings.devServerConfig.public() + '/',
            },
            resolve: {
                alias: {
                    'vue$': 'vue/dist/vue.js'
                }
            },
            mode: 'development',
            devtool: 'inline-source-map',
            optimization: configureOptimization(),
            devServer: configureDevServer(),
            module: {
                rules: [
                    configurePostcssLoader(),
                    configureImageLoader(),
                ],
            },
            plugins: [
                new webpack.HotModuleReplacementPlugin(),
                new DashboardPlugin(),
            ],
        }
    ),
];
