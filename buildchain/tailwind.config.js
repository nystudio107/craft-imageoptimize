// module exports
module.exports = {
  purge: {
    content: [
      '../src/templates/**/*.{twig,html}',
      '../src/assetbundles/retour/src/vue/**/*.{vue,html}',
      './node_modules/vuetable-2/src/components/**/*.{vue,html}',
    ],
    layers: [
      'base',
      'components',
      'utilities',
    ],
    mode: 'layers',
    options: {
      whitelist: [
        '../src/assetbundles/retour/src/css/components/*.css',
      ],
    }
  },
  theme: {
  },
  corePlugins: {},
  plugins: [],
};
