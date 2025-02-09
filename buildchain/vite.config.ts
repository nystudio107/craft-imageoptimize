import createVuePlugin from '@vitejs/plugin-vue2';
import {defineConfig} from 'vite';
import checker from 'vite-plugin-checker';
import tailwindcss from "@tailwindcss/vite";
import {visualizer} from 'rollup-plugin-visualizer';
import viteCompressionPlugin from 'vite-plugin-compression';
import {viteExternalsPlugin} from 'vite-plugin-externals';
import viteRestartPlugin from 'vite-plugin-restart';
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
    tailwindcss(),
    checker({
      eslint: {
        lintCommand: 'eslint "./src/**/*.{js,ts}"',
        useFlatConfig: true,
        dev: {
          overrideConfig: {
            cache: true,
          }
        }
      },
      stylelint: {
        lintCommand: 'stylelint ./src/**/*.{css} --allow-empty-input --fix',
        dev: {
          overrideConfig: {
            allowEmptyInput: true,
            cache: true,
            fix: false
          }
        }
      },
      typescript: true,
      vueTsc: true,
    }),
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
    // Allow cross-origin requests -- https://github.com/vitejs/vite/security/advisories/GHSA-vg6x-rcgg-rjx6
    allowedHosts: true,
    cors: {
      origin: /https?:\/\/([A-Za-z0-9\-\.]+)?(localhost|\.local|\.test|\.site)(?::\d+)?$/
    },
    fs: {
      strict: false
    },
    headers: {
      "Access-Control-Allow-Private-Network": "true",
    },
    host: '0.0.0.0',
    origin: 'http://localhost:' + process.env.DEV_PORT,
    port: parseInt(process.env.DEV_PORT),
    strictPort: true,
  }
}));
