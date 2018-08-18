// webpack.prod.js - production builds

// node modules
const glob = require("glob-all");
const path = require('path');
// webpack plugins
const merge = require('webpack-merge');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const PurgecssPlugin = require("purgecss-webpack-plugin");
const whitelister = require('purgecss-whitelister')
// config files
const pkg = require('./package.json');
const common = require('./webpack.common.js');

// Custom PurgeCSS extractor for Tailwind that allows special characters in
// class names.
//
// https://github.com/FullHuman/purgecss#extractor
class TailwindExtractor {
    static extract(content) {
        return content.match(/[A-Za-z0-9-_:\/]+/g) || [];
    }
}

// Configure the PurgeCSS paths
const configurePurgeCssPaths = () => {
    let paths = [];

    for (const [key, value] of Object.entries(pkg.purgeCss.paths)) {
        paths.push(path.join(__dirname, value));
    }

    return paths;
};

// Production module exports
module.exports = [
    merge(
        common.legacyConfig,
        {
            mode: 'production',
            devtool: 'source-map',
            optimization: {
                splitChunks: {
                },
                minimizer: [
                    new UglifyJsPlugin({
                        cache: true,
                        parallel: true,
                        sourceMap: true
                    }),
                    new OptimizeCSSAssetsPlugin({
                        cssProcessorOptions: {
                            map: {
                                inline: false,
                                annotation: true,
                            },
                            safe: true,
                            discardComments: true
                        },
                    })
                ]
            },
            plugins: [
                new PurgecssPlugin({
                    paths: glob.sync(configurePurgeCssPaths()),
                    whitelist: whitelister(pkg.purgeCss.whitelist),
                    whitelistPatterns: pkg.purgeCss.whitelistPatterns,
                    extractors: [{
                            extractor: TailwindExtractor,
                            extensions: pkg.purgeCss.extensions
                        }]
                })
            ]
        }
    ),
    merge(
        common.modernConfig,
        {
            mode: 'production',
            devtool: 'source-map',
            optimization: {
                splitChunks: {
                },
                minimizer: [
                    new UglifyJsPlugin({
                        cache: true,
                        parallel: true,
                        sourceMap: true
                    }),
                ]
            },
        }
    ),
];
