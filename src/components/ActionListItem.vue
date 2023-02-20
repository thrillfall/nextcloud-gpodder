<template>
	<ListItem :title="action.episodeUrl">
    <template #subtitle>
      <span v-if="isLoading"><em>(Loading RSS data...)</em></span>
      <span v-else>{{ action.podcastUrl }}</span>
    </template>
    <template #actions>
      <ActionLink :href="action.podcastUrl"
                  target="_blank"
                  icon="icon-external">
        Podcast's homepage
      </ActionLink>
      <ActionLink :href="action.episodeUrl"
                  target="_blank"
                  icon="icon-external">
        Download episode media
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
	name: 'ActionListItem',
	components: {
		ActionLink,
		Avatar,
		ListItem,
		Rss,
	},
	props: {
		action: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			actionData: null,
			isLoading: true,
		}
	},
	async mounted() {
		// try {
		// 	const resp = await axios.get(generateUrl('/apps/gpoddersync/personal_settings/podcast_data?url={url}', {
		// 		url: this.action.url,
		// 	}))
		// 	this.actionData = resp.data?.data
		// } catch (e) {
		// 	console.error(e)
		// } finally {
		// 	this.isLoading = false
		// }
    this.isLoading = false
	},
	methods: {
		getEpisode() {
			return this.action.episodeUrl ?? 'episodeUrl'
		},
		getDetails() {
			if (this.action.listenedSeconds <= 0) {
				return '(no time listened)'
			}
			const seconds = this.action.listenedSeconds
			const hours = Math.floor(seconds / 3600)
			const modMinutes = Math.floor(seconds / 60) % 60
			if (hours === 0) {
				const modSeconds = seconds % 60
				return `(${modMinutes}min ${modSeconds}s listened)`
			}
			return `(${hours}h ${modMinutes}min listened)`
		},
		getImageSrc() {
			return this.actionData?.imageBlob ?? this.actionData?.imageUrl ?? ''
		},
		getAvatarName() {
			return this.actionData?.author ?? ''
		},
		getSubepisode() {
			return this.actionData?.description ?? ''
		},
		getHomepageLink() {
			return this.actionData?.link ?? ''
		},
		getRssLink() {
			return this.action.url ?? ''
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
