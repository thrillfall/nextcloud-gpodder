<template>
	<ListItem :title="isLoading ? action.episodeUrl : getEpisodeName()">
    <template #subtitle>
      <span v-if="isLoading"><em>(Loading RSS data...)</em></span>
      <span v-else>{{ getPodcastName() }}</span>
    </template>
    <template #actions>
      <ActionLink :href="action.podcastUrl"
                  target="_blank"
                  icon="icon-external">
        Open RSS feed
      </ActionLink>
      <ActionLink :href="action.episodeUrl"
                  target="_blank"
                  icon="icon-external">
        Download episode media
      </ActionLink>
      <ActionLink v-if="!isLoading"
                  :href="getEpisodeLink()"
                  target="_blank"
                  icon="icon-external">
        Open episode link
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
			actionExtraData: null,
			isLoading: true,
		}
	},
	async mounted() {
		try {
			const resp = await axios.get(generateUrl('/apps/gpoddersync/personal_settings/action_extra_data?episodeUrl={url}', {
				url: this.action.episodeUrl,
			}))
			this.actionExtraData = resp.data?.data
      console.log("resp", resp);
      console.log("this.actionExtraData", this.actionExtraData);
		} catch (e) {
			console.error(e)
		} finally {
			this.isLoading = false
		}
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
		getPodcastName() {
			return this.actionExtraData?.podcastName ?? ''
		},
    getEpisodeName() {
      return this.actionExtraData?.episodeName ?? this.action.episodeUrl;
    },
    getEpisodeLink() {
      return this.actionExtraData?.episodeLink ?? ''
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
