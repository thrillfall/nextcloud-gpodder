<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

use Exception;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\ICache;
use OCP\ICacheFactory;

class PodcastDataReader {
	private ?ICache $cache = null;
	private IClient $httpClient;
	private SubscriptionChangeRepository $subscriptionChangeRepository;

	public function __construct(
		ICacheFactory $cacheFactory,
		IClientService $httpClientService,
		SubscriptionChangeRepository $subscriptionChangeRepository
	) {
		if ($cacheFactory->isLocalCacheAvailable()) {
			$this->cache = $cacheFactory->createLocal('GPodderSync-Podcasts');
		}
		$this->httpClient = $httpClientService->newClient();
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
	}

	public function getCachedOrFetchPodcastData(string $url, string $userId): ?PodcastData {
		if ($this->cache == null) {
			return $this->fetchPodcastData($url, $userId);
		}
		$oldData = $this->tryGetCachedPodcastData($url);
		if ($oldData) {
			return $oldData;
		}
		$newData = $this->fetchPodcastData($url, $userId);
		$this->trySetCachedPodcastData($url, $newData);
		return $newData;
	}

	private function userHasPodcast(string $url, string $userId): bool {
		$subscriptionChanges = $this->subscriptionChangeRepository->findByUrl($url, $userId);
		return $subscriptionChanges !== null;
	}

	public function fetchPodcastData(string $url, string $userId): ?PodcastData {
		if (!$this->userHasPodcast($url, $userId)) {
			return null;
		}
		$resp = $this->fetchUrl($url);
		$data = PodcastData::parseRssXml($resp->getBody());
		$blob = $this->tryFetchImageBlob($data);
		if ($blob) {
			$data->setImageBlob($blob);
		}
		return $data;
	}

	private function tryFetchImageBlob(PodcastData $data): ?string {
		if (!$data->getImageUrl()) {
			return null;
		}
		try {
			$resp = $this->fetchUrl($data->getImageUrl());
			$contentType = $resp->getHeader('Content-Type');
			$body = $resp->getBody();
			$bodyBase64 = base64_encode($body);
			return "data:$contentType;base64,$bodyBase64";
		} catch (Exception $e) {
			return null;
		}
	}

	private function fetchUrl(string $url): IResponse {
		$resp = $this->httpClient->get($url);
		$statusCode = $resp->getStatusCode();
		if ($statusCode < 200 || $statusCode >= 300) {
			throw new \ErrorException("Web request returned non-2xx status code: $statusCode");
		}
		return $resp;
	}

	public function tryGetCachedPodcastData(string $url): ?PodcastData {
		$oldData = $this->cache->get($url);
		if (!$oldData) {
			return null;
		}
		return PodcastData::fromArray($oldData);
	}

	public function trySetCachedPodcastData(string $url, PodcastData $data): bool {
		return $this->cache->set($url, $data->toArray());
	}
}
