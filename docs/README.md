# `image-optimize` docs

This buildchain is a self-contained build system for the `image-optimize` documentation.

## Overview

The buildchain uses [VitePress](https://vitepress.dev/) via a Docker container to facilitate writing the docs as [markdown](https://vitepress.dev/guide/markdown), linting them via [textlint](https://textlint.github.io/), building them as HTML files with bundled assets, and publishing them automatically via a [GitHub action](https://docs.github.com/en/actions).

It also uses a [Rollup](https://rollupjs.org/) [sitemap plugin](https://github.com/aminnairi/rollup-plugin-sitemap) to generate a `sitemap.xml` for the generated docs.

The markdown sources for the docs and assets are in the `docs/docs/` directory.

The built distribution docs are created via the `build-and-deploy-docs.yaml`

## Prerequisites

To run the buildchain for development purposes:

- You must have [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or the equivalent) installed

## Commands

This buildchain uses `make` as an interface to the buildchain. The following commands are available from the `buildchain/` directory:

- `make image-build` - Build the Docker image & run `npm install`. This command must be run once before using the Docker container.
- `make dev` - Start Vite HMR dev server while writing/editing the docs. Click on the link displayed in the terminal to open the docs up
- `make lint` - Run `textlint` on the docs, reporting on any errors and warnings
- `make fix` - Run `textlint` on the docs, automatically fixing any errors, and reporting any warnings
- `make clean` - Remove `node_modules/` and `package-lock.json` to start clean (need to run `make image-build` after doing this, see below)
- `make npm XXX` - Run an `npm` command inside the container, e.g.: `make npm run lint` or `make npm install`
- `make ssh` - Open up a shell session into the buildchain Docker container
- `make build` - Do a local distribution build of the docs; normally not needed since they are built & deployed via GitHub action

## Docs versioning

Each major version of the plugin corresponds to a major version of Craft.

Each major version of the plugin has separate documentation that needs to be updated when changes span plugin versions.

The latest version of the docs that correspond to the latest version of the plugin is always the root of the docs tree, with older versions appearing in sub-directories:

```
│ index.html
├── v4
│   └── index.html
├── v3
│   └── index.html
```

The docs are entirely separate, but linked to eachother via a version menu, configured in the `docs/docs/.vitepress/config.ts` file.

## Algolia Docsearch

The docs uses [Algolia Docsearch](https://docsearch.algolia.com/) to index them, and allow for easy searching via a search field with auto-complete.

Algolia Docsearch is configured in the `docs/docs/.vitepress/config.ts` file.

## textlint

The buildchain uses [textlint](https://textlint.github.io/) to automatically fix errors on build, and also issue writing style warnings.

`textlint` automatically uses any rules added to the `docs/package.json` file, which can be overridden or customized via the `docs/.textlintrc.js` file.

See the [textlint docs](https://textlint.github.io/docs/getting-started.html) for details.

## Overriding environment variables

The `Makefile` contains sane defaults for most things, but you can override them via environment variables if you need to.

For instance, if you want to change the `DOCS_DEST` environment variable to change where `make build` builds the docs locally, you can set it before running any buildchain `make` commands:
```bash
env DOCS_DEST=../path/to/some/dir make build
```
...or use any other method for [setting environment variables](https://www.twilio.com/blog/how-to-set-environment-variables.html). This environment variable needs to be set in the shell where you run the buildchain's various `make` commands from, so setting it in your project's `.env` file won't work.
