<template>
	<div class="gpoddersync_settings">
		<SettingsSection :title="t('gpoddersync', 'Synced subscriptions')"
			:description="t('gpoddersync', 'Podcast subscriptions synchronized to this Nextcloud account so far.')">
			<div v-if="subscriptions.length > 0">
				<div class="sorting-container">
					<label for="gpoddersync_sorting">Sort by:</label>
					<Multiselect id="gpoddersync_sorting"
						v-model="sortBy"
						:options="sortingOptions"
						track-by="label"
						label="label"
						:allow-empty="false"
						@change="updateSorting" />
				</div>
				<ul>
					<SubscriptionListItem v-for="sub in subscriptions"
						:key="sub.url"
						:sub="sub"
						@removed="handleSubscriptionRemoved" />
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
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'
import SubscriptionListItem from '../components/SubscriptionListItem.vue'

import Podcast from 'vue-material-design-icons/Podcast'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'

const sortingOptions = [
	{ label: 'Listened time (desc)', compare: (a, b) => a?.listenedSeconds < b?.listenedSeconds },
	{ label: 'Listened time (asc)', compare: (a, b) => a?.listenedSeconds > b?.listenedSeconds },
]

export default {
	name: 'PersonalSettingsPage',
	components: {
		EmptyContent,
		Multiselect,
		Podcast,
		SettingsSection,
		SubscriptionListItem,
	},
	data() {
		return {
			subscriptions: [],
			isLoading: true,
			sortBy: sortingOptions[0],
			sortingOptions,
		}
	},
	async mounted() {
		try {
			const resp = await axios.get(generateUrl('/apps/gpoddersync/personal_settings/metrics'))
			if (!Array.isArray(resp.data.subscriptions)) {
				throw new Error('expected subscriptions array in metrics response')
			}
			this.subscriptions = resp.data.subscriptions
			this.subscriptions.sort(this.sortBy.compare)
		} catch (e) {
			console.error(e)
			showError(t('gpoddersync', 'Could not fetch podcast synchronization stats'))
		} finally {
			this.isLoading = false
		}
	},
	methods: {
		updateSorting(sorting) {
			this.subscriptions.sort(sorting.compare)
		},
		handleSubscriptionRemoved(url) {
			this.subscriptions = this.subscriptions.filter((sub) => sub.url !== url)
		},
	},
}
</script>

<style lang="scss" scoped>
a.link {
	text-decoration: underline;
	color: var(--color-primary-element);
}
</style>
