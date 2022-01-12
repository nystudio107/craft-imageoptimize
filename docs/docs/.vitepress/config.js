module.exports = {
    title: 'ImageOptimize Plugin Documentation',
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
        repo: 'nystudio107/craft-imageoptimize',
        docsDir: 'docs/docs',
        docsBranch: 'develop',
        algolia: {
            appId: 'HVVF81UL1B',
            apiKey: '84793c9eb47412ec6c79ad038c19086e',
            indexName: 'image-optimize'
        },
        editLinks: true,
        editLinkText: 'Edit this page on GitHub',
        lastUpdated: 'Last Updated',
        sidebar: [
            {text: 'ImageOptimize Plugin', link: '/'},
            {text: 'ImageOptimize Overview', link: '/overview.html'},
            {text: 'Configuring ImageOptimize', link: '/configuring.html'},
            {text: 'Using ImageOptimize', link: '/using.html'},
            {text: 'Advanced Usage', link: '/advanced.html'},
        ],
    },
};
