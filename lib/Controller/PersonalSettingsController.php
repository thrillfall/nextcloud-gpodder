<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\StreamWrapper;
use OCA\GPodderSync\Core\PodcastData\PodcastDataReader;
use OCA\GPodderSync\Core\PodcastData\PodcastMetricsReader;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\AppFramework\OCS\OCSException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\IRequest;

class PersonalSettingsController extends Controller {

	private string $userId;
	private PodcastMetricsReader $metricsReader;
	private PodcastDataReader $dataReader;

	// TODO: Use httpClient via PodcastDataReader instead
	private IClient $httpClient;

	public function __construct(
		string $AppName,
		IRequest $request,
		string $UserId,
		PodcastMetricsReader $metricsReader,
		PodcastDataReader $dataReader,
		IClientService $httpClientService,
	) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId ?? '';
		$this->metricsReader = $metricsReader;
		$this->dataReader = $dataReader;
		$this->httpClient = $httpClientService->newClient();
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
			], statusCode: Http::STATUS_BAD_REQUEST);
		}
		return new JsonResponse([
			'data' => $this->dataReader->getCachedOrFetchPodcastData($url, $this->userId),
		]);
	}
}
