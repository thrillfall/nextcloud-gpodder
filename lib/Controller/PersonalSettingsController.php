<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use OCA\GPodderSync\Core\PodcastData\PodcastMetrics;
use OCA\GPodderSync\Core\PodcastData\PodcastMetricsReader;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class PersonalSettingsController extends Controller {

	private string $userId;
	private PodcastMetricsReader $metricsReader;

	public function __construct(
		string $AppName,
		IRequest $request,
		string $UserId,
		PodcastMetricsReader $metricsReader,
	) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId ?? '';
		$this->metricsReader = $metricsReader;
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function metrics(): JSONResponse {
		$metrics = $this->metricsReader->metrics($this->userId);
		return new JSONResponse([
			'subscriptions' => $metrics,
		]);
	}
}
