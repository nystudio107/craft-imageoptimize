// webpack.prod.js - production builds

const LEGACY_CONFIG = 'legacy';
const MODERN_CONFIG = 'modern';

// node modules
const webpack = require('webpack');
const glob = require("glob-all");
const path = require('path');
const git = require('git-rev-sync');
const moment = require('moment');
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

// Configure PurgeCSS
const configurePurgeCss = () => {
    let paths = [];
    // Configure whitelist paths
    for (const [key, value] of Object.entries(pkg.purgeCss.paths)) {
        paths.push(path.join(__dirname, value));
    }

    return {
        paths: glob.sync(paths),
        whitelist: whitelister(pkg.purgeCss.whitelist),
        whitelistPatterns: pkg.purgeCss.whitelistPatterns,
        extractors: [{
            extractor: TailwindExtractor,
            extensions: pkg.purgeCss.extensions
        }]
    };
};

// Configure optimization
const configureOptimization = (buildType) => {
    if (buildType === LEGACY_CONFIG) {
        return {
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
        };
    }
    if (buildType === MODERN_CONFIG) {
        return {
            splitChunks: {},
            minimizer: [
                new UglifyJsPlugin({
                    cache: true,
                    parallel: true,
                    sourceMap: true
                }),
            ]
        };
    }
};

// Production module exports
module.exports = [
    merge(
        common.legacyConfig,
        {
            mode: 'production',
            devtool: 'source-map',
            optimization: configureOptimization(LEGACY_CONFIG),
            plugins: [
                new PurgecssPlugin(
                    configurePurgeCss()
                ),
                new webpack.BannerPlugin(
                    configureBanner()
                ),
            ]
        }
    ),
    merge(
        common.modernConfig,
        {
            mode: 'production',
            devtool: 'source-map',
            optimization: configureOptimization(MODERN_CONFIG),
            plugins: [
                new webpack.BannerPlugin(
                    configureBanner()
                ),
            ]
        }
    ),
];
