<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Service;

use DateTime;
use SimpleXMLElement;

use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;

class PodcastCacheService {
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

	public function getCachedOrFetchPodcastData(string $url) {
		if ($this->cache == null) {
			return $this->fetchPodcastData($url);
		}
		$oldData = $this->cache->get($url);
		if ($oldData) {
			return $oldData;
		}
		$newData = $this->fetchPodcastData($url);
		$this->cache->set($url, $newData);
		return $newData;
	}

	public function fetchPodcastData(string $url) {
		$resp = $this->httpClient->get($url);
		$statusCode = $resp->getStatusCode();
		if ($statusCode < 200 || $statusCode >= 300) {
			throw new ErrorException("Podcast RSS URL returned non-2xx status code: $statusCode");
		}
		$body = $resp->getBody();
		$xml = new SimpleXMLElement($body);
		$channel = $xml->channel;
		return [
			'title' => (string)$channel->title,
			'author' => self::getXPathContent($xml, '/rss/channel/itunes:author'),
			'link' => (string)$channel->link,
			'description' => (string)$channel->description,
			'image' => 
				self::getXPathContent($xml, '/rss/channel/image/url')
				?? self::getXPathAttribute($xml, '/rss/channel/itunes:image/@href'),
			'fetchedAtUnix' => (new DateTime())->getTimestamp(),
		];
	}

	private static function getXPathContent(SimpleXMLElement $xml, string $xpath) {
		$match = $xml->xpath($xpath);
		if ($match) {
			return (string)$match[0];
		}
		return null;
	}

	private static function getXPathAttribute(SimpleXMLElement $xml, string $xpath) {
		$match = $xml->xpath($xpath);
		if ($match) {
			return (string)$match[0][0];
		}
		return null;
	}
}
