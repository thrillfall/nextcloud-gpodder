import { generateFilePath } from '@nextcloud/router'

import Vue from 'vue'
import PersonalSettingsPage from './views/PersonalSettingsPage.vue'

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath(appName, '', 'js/')

Vue.mixin({ methods: { t, n } })

// https://nextcloud-vue-components.netlify.app/#/Introduction
Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA

export default new Vue({
	el: '#personal_settings',
	render: h => h(PersonalSettingsPage),
})
