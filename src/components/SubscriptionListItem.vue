<template>
	<ListItem :title="getTitle()"
		:details="getDetails()">
		<template #icon>
			<Avatar :size="44"
				:url="getImageSrc()"
				:display-name="getAvatarName()" />
		</template>
		<template #subtitle>
			<span v-if="isLoading"><em>(Loading RSS data...)</em></span>
			<span v-else>{{ getSubtitle() }}</span>
		</template>
		<template #actions>
			<ActionLink :href="getHomepageLink()"
				target="_blank"
				icon="icon-external">
				Podcast's homepage
			</ActionLink>
			<ActionLink :href="getRssLink()"
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
		getTitle() {
			return this.podcastData?.title ?? this.sub.url ?? ''
		},
		getDetails() {
			if (this.sub.listenedSeconds <= 0) {
				return '(no time listened)'
			}
			const seconds = this.sub.listenedSeconds
			const hours = Math.floor(seconds / 3600)
			const modMinutes = Math.floor(seconds / 60) % 60
			if (hours === 0) {
				const modSeconds = seconds % 60
				return `(${modMinutes}min ${modSeconds}s listened)`
			}
			return `(${hours}h ${modMinutes}min listened)`
		},
		getImageSrc() {
			return this.podcastData?.imageBlob ?? this.podcastData?.imageUrl ?? ''
		},
		getAvatarName() {
			return this.podcastData?.author ?? ''
		},
		getSubtitle() {
			return this.podcastData?.description ?? ''
		},
		getHomepageLink() {
			return this.podcastData?.link ?? ''
		},
		getRssLink() {
			return this.sub.url ?? ''
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
