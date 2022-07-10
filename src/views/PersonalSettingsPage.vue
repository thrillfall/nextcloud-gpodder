<template>
	<div class="gpoddersync_settings">
		<SettingsSection :title="t('gpoddersync', 'Synced subscriptions')"
			:description="t('gpoddersync', 'Podcast subscriptions that has so far been synchronized with this Nextcloud account.')">
			<span>Hello <span class="red_text">world</span> :)</span>
		</SettingsSection>
	</div>
</template>

<script>
import { SettingsSection } from '@nextcloud/vue'
export default {
	name: 'PersonalSettingsPage',
	components: {
		SettingsSection,
	},
}
</script>

<style lang="scss" scoped>
.red_text {
	color: red;
}
</style>
