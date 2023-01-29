import Theme from 'vitepress/theme'
import {h, watch} from 'vue'
import './custom.css'

import NYSLogo from './NYSLogo.vue';

// Could also come from .env
const GA_ID = 'UA-69117511-1';

export default {
  ...Theme,
  Layout() {
    return h(Theme.Layout, null, {
        'aside-bottom': () => h(NYSLogo)
      }
    )
  },
  enhanceApp: (ctx) => {
    // Google analytics integration
    if (import.meta.env.PROD && GA_ID && typeof window !== 'undefined') {
      (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r
        i[r] = i[r] || function () {
          (i[r].q = i[r].q || []).push(arguments)
        }
        i[r].l = 1 * new Date()
        a = s.createElement(o)
        m = s.getElementsByTagName(o)[0]
        a.async = 1
        a.src = g
        m.parentNode.insertBefore(a, m)
      })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga')
      ga('create', GA_ID, 'auto')
      ga('set', 'anonymizeIp', true)
      // Send a page view any time the route changes
      watch(ctx.router.route, (newValue, oldValue) => {
        ga('set', 'page', newValue.path)
        ga('send', 'pageview')
      })
    }
  }
}
