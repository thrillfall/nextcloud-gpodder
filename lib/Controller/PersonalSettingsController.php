<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use OCA\GPodderSync\Core\PodcastData\PodcastDataReader;
use OCA\GPodderSync\Core\PodcastData\PodcastMetricsReader;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class PersonalSettingsController extends Controller {

	private string $userId;
	private PodcastMetricsReader $metricsReader;
	private PodcastDataReader $dataReader;

	public function __construct(
		string $AppName,
		IRequest $request,
		?string $UserId,
		PodcastMetricsReader $metricsReader,
		PodcastDataReader $dataReader
	) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId ?? '';
		$this->metricsReader = $metricsReader;
		$this->dataReader = $dataReader;
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

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $url
	 * @return JsonResponse
	 */
	public function podcastData(string $url = ''): JsonResponse {
		if ($url === '') {
			return new JSONResponse([
				'message' => "Missing query parameter 'url'.",
				'data' => null,
			], Http::STATUS_BAD_REQUEST);
		}
		return new JsonResponse([
			'data' => $this->dataReader->getCachedOrFetchPodcastData($url, $this->userId),
		]);
	}
}
