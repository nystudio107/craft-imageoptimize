module.exports = {
    title: 'ImageOptimize Documentation',
    description: 'Documentation for the ImageOptimize plugin',
    base: '/docs/image-optimize/',
    lang: 'en-US',
    head: [
        [
            'script',
            {},
            "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');ga('create', 'UA-69117511-1', 'auto');ga('require', 'displayfeatures');ga('send', 'pageview');"
        ],
    ],
    themeConfig: {
        repo: 'nystudio107/craft-imageoptimize',
        docsDir: 'docs/docs',
        docsBranch: 'v1',
        algolia: {
            apiKey: '77fb94de306ac61504c501db4bb13a4e',
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
