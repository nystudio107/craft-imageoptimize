import {defineConfig} from 'vite'
import SitemapPlugin from 'rollup-plugin-sitemap'
import VitePressConfig from './.vitepress/config'
import {SidebarGroup, SidebarItem} from "vitepress/types/default-theme";

const docsSiteBaseUrl = 'https://nystudio107.com'
const docsBaseUrl = new URL(VitePressConfig.base!, docsSiteBaseUrl).href.replace(/\/$/, '') + '/'
const siteMapRoutes = VitePressConfig.themeConfig?.sidebar?.map((group: SidebarGroup) => {
  return group.items.map((items: SidebarItem) => ({
    path: items.link.replace(/^\/+/, ''),
    name: items.text
  }));
}).reduce((prev: SidebarItem[], curr: SidebarItem[]) => {
  return prev.concat(curr)
});

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    SitemapPlugin({
      baseUrl: docsBaseUrl,
      contentBase: './docs/.vitepress/dist',
      routes: siteMapRoutes,
    })
  ],
  server: {
    host: '0.0.0.0',
    port: 3002,
    strictPort: true,
  }
});
