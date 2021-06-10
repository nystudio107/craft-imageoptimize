module.exports = {
    title: 'ImageOptimize Documentation',
    description: 'Documentation for the ImageOptimize plugin',
    base: '/docs/image-optimize/',
    lang: 'en-US',
    themeConfig: {
        repo: 'nystudio107/craft-imageoptimize',
        docsDir: 'docs/docs',
        docsBranch: 'v1',
        algolia: {
            apiKey: '',
            indexName: 'image-optimize'
        },
        editLinks: true,
        editLinkText: 'Edit this page on GitHub',
        lastUpdated: 'Last Updated',
        sidebar: [
            { text: 'ImageOptimize Plugin', link: '/' },
            { text: 'ImageOptimize Overview', link: '/overview' },
            { text: 'Configuring ImageOptimize', link: '/configuring' },
            { text: 'Using ImageOptimize', link: '/using' },
            { text: 'Advanced Usage', link: '/advanced' },
        ],
    },
};
