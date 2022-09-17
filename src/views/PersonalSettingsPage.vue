<template>
	<div class="gpoddersync_settings">
		<SettingsSection :title="t('gpoddersync', 'Synced subscriptions')"
			:description="t('gpoddersync', 'Podcast subscriptions that has so far been synchronized with this Nextcloud account.')">
			<div v-if="subscriptions.length > 0">
				<ul>
					<ListItem v-for="sub in subscriptions"
						:key="sub.url"
						:title="sub.podcastData?.title ?? sub.url"
						:details="formatSubscriptionDetails(sub)">
						<template #icon>
							<Avatar :size="44"
								:url="sub.podcastData?.image"
								:display-name="sub.podcastData?.author" />
						</template>
						<template #subtitle>
							{{ sub.podcastData?.description }}
						</template>
						<template #actions>
							<ActionLink :href="sub.podcastData?.link"
								target="_blank"
								icon="icon-external">
								Podcast's homepage
							</ActionLink>
							<ActionLink :href="sub.url"
								target="_blank">
								<template #icon>
									<Rss />
								</template>
								RSS feed
							</ActionLink>
						</template>
					</ListItem>
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
import ActionLink from '@nextcloud/vue/dist/Components/ActionLink'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'

import Podcast from 'vue-material-design-icons/Podcast'
import Rss from 'vue-material-design-icons/Rss.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'PersonalSettingsPage',
	components: {
		ActionLink,
		Avatar,
		EmptyContent,
		ListItem,
		Podcast,
		Rss,
		SettingsSection,
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
