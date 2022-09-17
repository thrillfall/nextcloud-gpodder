<template>
	<div class="gpoddersync_settings">
		<SettingsSection :title="t('gpoddersync', 'Synced subscriptions')"
			:description="t('gpoddersync', 'Podcast subscriptions that has so far been synchronized with this Nextcloud account.')">
			<div v-if="subscriptions.length > 0">
				<ul>
					<SubscriptionListItem v-for="sub in subscriptions"
						:key="sub.url"
						:sub="sub" />
				</ul>
			</div>
			<div v-if="subscriptions.length === 0 && !isLoading">
				<EmptyContent>
					No subscriptions
					<template #icon>
						<Podcast />
					</template>
					<template #desc>
						Start syncing podcasts from your favorite podcast client, such as
						<a class="link" href="https://antennapod.org/" target="_blank">Antennapod</a>,
						and then refresh this page to see them pop up here.
					</template>
				</EmptyContent>
			</div>
		</SettingsSection>
	</div>
</template>

<script>
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'
import SubscriptionListItem from '../components/SubscriptionListItem.vue'

import Podcast from 'vue-material-design-icons/Podcast'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'PersonalSettingsPage',
	components: {
		EmptyContent,
		Podcast,
		SettingsSection,
		SubscriptionListItem,
	},
	data() {
		return {
			subscriptions: [],
			isLoading: true,
		}
	},
	async mounted() {
		try {
			const resp = await axios.get(generateUrl('/apps/gpoddersync/personal_settings/metrics'))
			if (!Array.isArray(resp.data.subscriptions)) {
				throw new Error('expected subscriptions array in metrics response')
			}
			this.subscriptions = resp.data.subscriptions
		} catch (e) {
			console.error(e)
			showError(t('gpoddersync', 'Could not fetch podcast synchronization stats'))
		} finally {
			this.isLoading = false
		}
	},
	methods: {
		formatSubscriptionDetails(sub) {
			if (sub.listenedSeconds <= 0) {
				return '(no time listened)'
			}
			const hours = Math.floor(sub.listenedSeconds / 3600)
			const modMinutes = Math.floor(sub.listenedSeconds / 60) % 60
			if (hours === 0) {
				const modSeconds = sub.listenedSeconds % 60
				return `(${modMinutes}min ${modSeconds}s listened)`
			}
			return `(${hours}h ${modMinutes}min listened)`
		},
	},
}
</script>

<style lang="scss" scoped>
a.link {
	text-decoration: underline;
	color: var(--color-primary-element-light);
}
</style>
