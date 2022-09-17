<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;

class PodcastDataCache {
	private ?ICache $cache = null;
	private IClient $httpClient;

	public function __construct(
		ICacheFactory $cacheFactory,
		IClientService $httpClientService,
	) {
		if ($cacheFactory->isLocalCacheAvailable()) {
			$this->cache = $cacheFactory->createLocal('GPodderSync-Podcasts');
		}
		$this->httpClient = $httpClientService->newClient();
	}

	public function getCachedOrFetchPodcastData(string $url): PodcastData {
		if ($this->cache == null) {
			return $this->fetchPodcastData($url);
		}
		$oldData = $this->tryGetCachedPodcastData($url);
		if ($oldData) {
			return $oldData;
		}
		$newData = $this->fetchPodcastData($url);
		$this->trySetCachedPodcastData($url, $newData);
		return $newData;
	}

	public function fetchPodcastData(string $url): PodcastData {
		$resp = $this->httpClient->get($url);
		$statusCode = $resp->getStatusCode();
		if ($statusCode < 200 || $statusCode >= 300) {
			throw new \ErrorException("Podcast RSS URL returned non-2xx status code: $statusCode");
		}
		$body = $resp->getBody();
		return PodcastData::parseRssXml($body);
	}

	public function tryGetCachedPodcastData(string $url): ?PodcastData {
		$oldData = $this->cache->get($url);
		if (!$oldData) {
			return null;
		}
		return PodcastData::fromArray($oldData);
	}

	public function trySetCachedPodcastData(string $url, PodcastData $data) {
		$this->cache->set($url, $data->toArray());
	}
}
