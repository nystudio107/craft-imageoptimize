import {defineConfig} from 'vitepress'

export default defineConfig({
  title: 'ImageOptimize Plugin',
  description: 'Documentation for the ImageOptimize plugin',
  base: '/docs/image-optimize/',
  lang: 'en-US',
  head: [
    ['meta', {content: 'https://github.com/nystudio107', property: 'og:see_also',}],
    ['meta', {content: 'https://twitter.com/nystudio107', property: 'og:see_also',}],
    ['meta', {content: 'https://youtube.com/nystudio107', property: 'og:see_also',}],
    ['meta', {content: 'https://www.facebook.com/newyorkstudio107', property: 'og:see_also',}],
  ],
  themeConfig: {
    socialLinks: [
      {icon: 'github', link: 'https://github.com/nystudio107'},
      {icon: 'twitter', link: 'https://twitter.com/nystudio107'},
    ],
    logo: '/img/plugin-logo.svg',
    editLink: {
      pattern: 'https://github.com/nystudio107/craft-imageoptimize/edit/develop/docs/docs/:path',
      text: 'Edit this page on GitHub'
    },
    algolia: {
      appId: 'HVVF81UL1B',
      apiKey: '84793c9eb47412ec6c79ad038c19086e',
      indexName: 'image-optimize'
    },
    lastUpdatedText: 'Last Updated',
    sidebar: [
      {
        text: 'Topics',
        items: [
          {text: 'ImageOptimize Plugin', link: '/'},
          {text: 'ImageOptimize Overview', link: '/overview.html'},
          {text: 'Configuring ImageOptimize', link: '/configuring.html'},
          {text: 'Using ImageOptimize', link: '/using.html'},
          {text: 'Advanced Usage', link: '/advanced.html'},
        ],
      }
    ],
    nav: [
      {text: 'Home', link: 'https://nystudio107.com/plugins/imageoptimize'},
      {text: 'Store', link: 'https://plugins.craftcms.com/image-optimize'},
      {text: 'Changelog', link: 'https://nystudio107.com/plugins/imageoptimize/changelog'},
      {text: 'Issues', link: 'https://github.com/nystudio107/craft-imageoptimize/issues'},
    ],
  },
});
