<template>
	<ListItem :title="podcastData?.title ?? sub.url"
		:details="formatSubscriptionDetails(sub)">
		<template #icon>
			<Avatar :size="44"
				:url="podcastData?.imageBlob ?? podcastData?.imageUrl"
				:display-name="podcastData?.author" />
		</template>
		<template #subtitle>
			<span v-if="isLoading"><em>(Loading RSS data...)</em></span>
			<span v-else>{{ podcastData?.description }}</span>
		</template>
		<template #actions>
			<ActionLink :href="podcastData?.link"
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
</template>

<script>
import ActionLink from '@nextcloud/vue/dist/Components/ActionLink'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
import ListItem from '@nextcloud/vue/dist/Components/ListItem'

import Rss from 'vue-material-design-icons/Rss.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'SubscriptionListItem',
	components: {
		ActionLink,
		Avatar,
		ListItem,
		Rss,
	},
	props: {
		sub: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			podcastData: null,
			isLoading: true,
		}
	},
	async mounted() {
		try {
			const resp = await axios.get(generateUrl('/apps/gpoddersync/personal_settings/podcast_data?url={url}', {
				url: this.sub.url,
			}))
			this.podcastData = resp.data?.data
		} catch (e) {
			console.error(e)
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
