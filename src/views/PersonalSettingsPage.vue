<template>
	<div class="gpoddersync_settings">
    <SettingsSection :title="t('gpoddersync', 'Last actions')"
      :description="t('gpoddersync', 'A list of last actions.')">
      <div v-if="actions.length > 0" class="actions">
        <div class="sorting-container">
          <label for="gpoddersync_action_filtering">Action:</label>
          <Multiselect id="gpoddersync_action_filtering"
                       v-model="actionFilter"
                       :options="actionFilteringOptions"
                       track-by="label"
                       label="label"
                       :allow-empty="false"
                       @change="updateActionFiltering" />
        </div>
        <ul>
          <ActionListItem v-for="action in actions.slice(0, maxActions)"
                                :key="action.episode"
                                :action="action" />
        </ul>
        <Actions>
          <ActionButton
              :disabled="actions.length < maxActions"
              @click="loadMoreActions">
            <template #icon>
              <PageNext />
            </template>
            {{ t('gpoddersync', 'Load more') }}
          </ActionButton>
        </Actions>
      </div>
    </SettingsSection>
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
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'
import SubscriptionListItem from '../components/SubscriptionListItem.vue'
import ActionListItem from '../components/ActionListItem.vue'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'

import Podcast from 'vue-material-design-icons/Podcast'
import PageNext from 'vue-material-design-icons/PageNext.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'

const sortingOptions = [
	{ label: 'Listened time (desc)', compare: (a, b) => a?.listenedSeconds < b?.listenedSeconds },
	{ label: 'Listened time (asc)', compare: (a, b) => a?.listenedSeconds > b?.listenedSeconds },
]

const actionFilteringOptions = [
	{ label: 'Play', action: 'PLAY' },
	{ label: 'Download', action: 'DOWNLOAD' },
]

export default {
	name: 'PersonalSettingsPage',
	components: {
		EmptyContent,
		Multiselect,
		Podcast,
    PageNext,
		SettingsSection,
		SubscriptionListItem,
    ActionListItem,
    ActionButton,
	},
	data() {
		return {
			subscriptions: [],
			allActions: [],
			actions: [],
      maxActions: 10,
			isLoading: true,
			sortBy: sortingOptions[0],
			sortingOptions,
      actionFilter: actionFilteringOptions[0],
      actionFilteringOptions,
		}
	},
	async mounted() {
		try {
			const resp = await axios.get(generateUrl('/apps/gpoddersync/personal_settings/metrics'))
      if (!Array.isArray(resp.data.actions)) {
        throw new Error('expected actions array in metrics response')
      }
      this.allActions = resp.data.actions;
      this.updateActionFiltering(this.actionFilter);
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
    updateActionFiltering(filtering) {
      this.actions = this.allActions.filter(obj => obj.action === filtering.action)
    },
    loadMoreActions() {
      this.maxActions += 10;
    },
	},
}
</script>

<style lang="scss" scoped>
a.link {
	text-decoration: underline;
	color: var(--color-primary-element);
}
.actions actions li {
  list-style: none;
}
</style>
