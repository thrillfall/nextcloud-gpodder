<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

use DateTime;
use Exception;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\ICache;
use OCP\ICacheFactory;
use Psr\Log\LoggerInterface;

class PodcastDataReader {
	private ?ICache $cache = null;
	private IClient $httpClient;
	private SubscriptionChangeRepository $subscriptionChangeRepository;
	private LoggerInterface $logger;

	private const ARD_AUDIOTHEK_HOST = 'api.ardaudiothek.de';
	private const ARD_PROGRAMSET_REGEX = '#https?://api\.ardaudiothek\.de/programsets/(?P<id>[^/?]+)#i';

	public function __construct(
		ICacheFactory $cacheFactory,
		IClientService $httpClientService,
		SubscriptionChangeRepository $subscriptionChangeRepository,
		LoggerInterface $logger
	) {
		if ($cacheFactory->isLocalCacheAvailable()) {
			$this->cache = $cacheFactory->createLocal('GPodderSync-Podcasts');
		}
		$this->httpClient = $httpClientService->newClient();
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
		$this->logger = $logger;
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
		$data = $this->fetchPodcastDataForUrl($url);
		$blob = $this->tryFetchImageBlob($data);
		if ($blob) {
			$data->setImageBlob($blob);
		}
		return $data;
	}

	private function fetchPodcastDataForUrl(string $url): PodcastData {
		if ($this->isArdAudiothekUrl($url)) {
			return $this->fetchArdAudiothekData($url);
		}
		$resp = $this->fetchUrl($url);
		return PodcastData::parseRssXml($resp->getBody());
	}

	private function isArdAudiothekUrl(string $url): bool {
		return (bool)preg_match(self::ARD_PROGRAMSET_REGEX, $url);
	}

	private function fetchArdAudiothekData(string $url): PodcastData {
		$programId = $this->extractArdProgramId($url);
		if ($programId === null) {
			throw new \InvalidArgumentException('Could not extract ARD Audiothek program id from URL');
		}
		$resp = $this->fetchUrl("https://" . self::ARD_AUDIOTHEK_HOST . "/programsets/$programId");
		$body = $resp->getBody();
		$decoded = json_decode($body, true);
		if (!is_array($decoded)) {
			$this->logger->warning('Invalid JSON returned from ARD Audiothek.', ['responseBody' => $body]);
			throw new \RuntimeException('Invalid JSON returned from ARD Audiothek');
		}
		$programSet = $decoded['data']['programSet'] ?? null;
		if (!is_array($programSet)) {
			$this->logger->warning('programSet missing in ARD Audiothek response.', ['responseBody' => $body]);
			throw new \RuntimeException('programSet missing in ARD Audiothek response');
		}
		return new PodcastData(
			$programSet['title'] ?? null,
			$programSet['publicationService']['title'] ?? null,
			$programSet['sharingUrl'] ?? $url,
			$programSet['synopsis'] ?? ($programSet['description'] ?? null),
			$this->resolveArdImageUrl($programSet['image'] ?? null),
			(new DateTime())->getTimestamp()
		);
	}

	private function resolveArdImageUrl($image): ?string {
		if (!is_array($image)) {
			return null;
		}
		return $image['url'] ?? ($image['url1X1'] ?? null);
	}

	private function extractArdProgramId(string $url): ?string {
		if (preg_match(self::ARD_PROGRAMSET_REGEX, $url, $matches)) {
			return $matches['id'];
		}
		return null;
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
		$resp = $this->httpClient->get($url, ['headers' => ['User-Agent' => 'nextcloud-gpodder (+https://nextcloud.com; like iTMS)']]);
		$statusCode = $resp->getStatusCode();
		if ($statusCode < 200 || $statusCode >= 300) {
			$resp = $this->httpClient->get($url, ['headers' => ['User-Agent' => 'nextcloud-gpodder (+https://nextcloud.com)']]);
			$statusCode = $resp->getStatusCode();
			if ($statusCode < 200 || $statusCode >= 300) {
				throw new \ErrorException("Web request returned non-2xx status code: $statusCode");
			}
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
