import Theme from 'vitepress/theme'
import {h} from 'vue'
import './custom.css'

import SidebarBottom from './SidebarBottom.vue';

export default {
    ...Theme,
    Layout() {
        return h(Theme.Layout, null, {
                'sidebar-bottom': () => h(SidebarBottom)
            }
        )
    }
}
