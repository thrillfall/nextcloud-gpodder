<template>
	<ListItem :title="isLoading ? action.episodeUrl : getEpisodeName()"
            :details="getDetails()">
    <template #icon>
      <!--
      <img v-if="getImageSrc() !== ''" :alt="getEpisodeName()" :src="getImageSrc()" />
      <Avatar v-if="getImageSrc() === ''"
              :size="44"
              :url="getImageSrc()"
              :display-name="getEpisodeName()" />
      -->
      <Avatar :size="44"
              :url="getImageSrc()"
              :display-name="getEpisodeName()" />
    </template>
    <template #subtitle>
      <span v-if="isLoading"><em>(Loading RSS data...)</em></span>
      <span v-else>{{ getPodcastName() }}</span>
      <Modal
          v-if="modalPlayer"
          @close="closeModalPlayer"
          size="small"
          title="Play media"
          :outTransition="true">
        <div class="modal__content">
          <h2 v-if="isLoading">Playing episode"</h2>
          <h2 v-else>Playing "{{ getEpisodeName() }}"</h2>
          <audio controls autoplay>
            <source :src="action.episodeUrl">
            Your browser does not support the audio element.
          </audio>
        </div>
      </Modal>
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
      <ActionButton @click="showModalPlayer"
                  target="_blank"
                  icon="icon-play">
        Play episode media
      </ActionButton>
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
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import Modal from '@nextcloud/vue/dist/Components/Modal'

import Rss from 'vue-material-design-icons/Rss.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'ActionListItem',
	components: {
		ActionLink,
    ActionButton,
		Avatar,
		ListItem,
		Rss,
    Modal,
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
      modalPlayer: false,
		}
	},
	async mounted() {
		try {
			const resp = await axios.get(generateUrl('/apps/gpoddersync/personal_settings/action_extra_data?episodeUrl={url}', {
				url: this.action.episodeUrl,
			}))
			this.actionExtraData = resp.data?.data
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
    getTimeString(seconds) {
      const hours = Math.floor(seconds / 3600)
      const modMinutes = Math.floor(seconds / 60) % 60
      if (hours === 0) {
        const modSeconds = seconds % 60
        return `${modMinutes}min ${modSeconds}s`
      }
      return `${hours}h ${modMinutes}min`
    },
		getDetails() {
      if (this.action.position === -1 || this.action.total === -1) {
        return '';
      }

      if (this.action.position === this.action.total) {
        return `(done, ${this.getTimeString(this.action.total)})`;
      }

      const percent = Math.round(this.action.position / this.action.total * 100);

			return `(${percent}% of ${this.getTimeString(this.action.total)} listened)`;
		},
    getImageSrc() {
      return this.actionExtraData?.episodeImage ?? ''
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
    showModalPlayer() {
      this.modalPlayer = true
    },
    closeModalPlayer() {
      this.modalPlayer = false
    }
  },
}
</script>

<style lang="scss" scoped>
.modal__content {
  margin: 50px;
  text-align: center;
}
</style>
