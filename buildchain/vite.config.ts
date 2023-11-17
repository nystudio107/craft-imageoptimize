import createVuePlugin from '@vitejs/plugin-vue2';
import {defineConfig} from 'vite';
import {visualizer} from 'rollup-plugin-visualizer';
import viteEslintPlugin from 'vite-plugin-eslint';
import viteCompressionPlugin from 'vite-plugin-compression';
import {viteExternalsPlugin} from 'vite-plugin-externals';
import viteRestartPlugin from 'vite-plugin-restart';
import viteStylelintPlugin from 'vite-plugin-stylelint';
import * as path from 'path';

// https://vitejs.dev/config/
export default defineConfig(({command}) => ({
  base: command === 'serve' ? '' : '/dist/',
  build: {
    emptyOutDir: true,
    manifest: 'manifest.json',
    outDir: '../src/web/assets/dist',
    rollupOptions: {
      input: {
        'imageoptimize': 'src/js/ImageOptimize.js',
        'field': 'src/js/OptimizedImagesField.js',
        'welcome': 'src/js/Welcome.js',
      },
    },
    sourcemap: true
  },
  plugins: [
    viteRestartPlugin({
      reload: [
        '../src/templates/**/*',
      ],
    }),
    createVuePlugin(),
    viteExternalsPlugin({
      'vue': 'Vue',
    }),
    viteCompressionPlugin({
      filter: /\.(js|mjs|json|css|map)$/i
    }),
    visualizer({
      filename: '../src/web/assets/dist/stats.html',
      template: 'treemap',
      sourcemap: true,
    }),
    viteEslintPlugin({
      cache: false,
      fix: true,
    }),
    viteStylelintPlugin({
      fix: true,
      lintInWorker: true
    })
  ],
  optimizeDeps: {
    include: ['vue-confetti'],
  },
  resolve: {
    alias: [
      {find: '@', replacement: path.resolve(__dirname, './src')},
      {find: 'vue', replacement: 'vue/dist/vue.esm.js'},
    ],
    preserveSymlinks: true,
  },
  server: {
    fs: {
      strict: false
    },
    host: '0.0.0.0',
    origin: 'http://localhost:' + process.env.DEV_PORT,
    port: parseInt(process.env.DEV_PORT),
    strictPort: true,
  }
}));
