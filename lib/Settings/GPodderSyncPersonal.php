<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;

class GPodderSyncPersonal implements ISettings {

	public function getForm(): TemplateResponse {
		return new TemplateResponse('gpoddersync', 'settings/personal', []);
	}

	public function getSection(): string {
		return 'gpoddersync';
	}

	public function getPriority(): int {
		return 198;
	}
}
