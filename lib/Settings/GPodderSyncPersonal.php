<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\Settings\ISettings;

class GPodderSyncPersonal implements ISettings {

	public function getForm(): TemplateResponse {
        $response = new TemplateResponse('gpoddersync', 'settings/personal', []);

        // Try to set CSP to allow images and media from anywhere
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedImageDomain('*')
            ->addAllowedMediaDomain('*')
            ->addAllowedConnectDomain('*');
        $response->setContentSecurityPolicy($csp);

        return $response;
	}

	public function getSection(): string {
		return 'gpoddersync';
	}

	public function getPriority(): int {
		return 198;
	}
}
