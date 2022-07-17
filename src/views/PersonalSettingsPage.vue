<template>
	<div class="gpoddersync_settings">
		<SettingsSection :title="t('gpoddersync', 'Synced subscriptions')"
			:description="t('gpoddersync', 'Podcast subscriptions that has so far been synchronized with this Nextcloud account.')">
			<span>Hello <span class="red_text">world</span> :)</span>
			<ul>
				<ListItem v-for="sub in subscriptions"
					:key="sub.url"
					:title="sub.podcast?.title ?? sub.url">
					<template #icon>
						<Avatar :size="44"
							:url="sub.podcast?.image"
							:display-name="sub.podcast?.author" />
					</template>
					<template #subtitle>
						{{ sub.podcast?.description }}
					</template>
				</ListItem>
			</ul>
		</SettingsSection>
	</div>
</template>

<script>
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'PersonalSettingsPage',
	components: {
		Avatar,
		ListItem,
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
}
</script>

<style lang="scss" scoped>
.red_text {
	color: red;
}
</style>
