<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

use DateTime;

use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;

use Psr\Log\LoggerInterface;

class PodcastMetricsReader {

	private LoggerInterface $logger;
	private SubscriptionChangeRepository $subscriptionChangeRepository;
	private EpisodeActionRepository $episodeActionRepository;
	private PodcastDataCache $cache;

	public function __construct(
		LoggerInterface $logger,
		SubscriptionChangeRepository $subscriptionChangeRepository,
		EpisodeActionRepository $episodeActionRepository,
		PodcastDataCache $cache,
	) {
		$this->logger = $logger;
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
		$this->episodeActionRepository = $episodeActionRepository;
		$this->cache = $cache;
	}

	/**
	 * @param string $userId
	 *
	 * @return PodcastMetrics[]
	 */
	public function metrics(string $userId): array {
		$episodeActions = $this->episodeActionRepository->findAll(0, $userId);

		$metricsPerUrl = array();
		foreach ($episodeActions as $ep) {
			$url = $ep->getPodcast();
			/** @var PodcastMetrics */
			$metrics = $metricsPerUrl[$url] ?? $this->createMetricsForUrl($url);

			$actionLower = strtolower($ep->getAction());
			$metrics->getActionCounts()->incrementAction($actionLower);

			if ($actionLower == 'play') {
				$seconds = $ep->getPosition();
				if ($seconds && $seconds != -1) {
					$metrics->addListenedSeconds($seconds);
				}
			}

			$metricsPerUrl[$url] = $metrics;
		}

		$sinceDatetime = (new DateTime)->setTimestamp(0);
		$subscriptionChanges = $this->subscriptionChangeRepository->findAllSubscribed($sinceDatetime, $userId);
		/** @var PodcastMetrics[] */
		$subscriptions = array_map(function (SubscriptionChangeEntity $sub) use ($metricsPerUrl) {
			$url = $sub->getUrl();
			$metrics = $metricsPerUrl[$url] ?? $this->createMetricsForUrl($url);
			return $metrics;
		}, $subscriptionChanges);

		return $subscriptions;
	}

	private function tryGetParsedPodcastData(string $url): ?PodcastData {
		try {
			return $this->cache->getCachedOrFetchPodcastData($url);
		} catch (\Exception $e) {
			$this->logger->error("Failed to get podcast data.", [
				'exception' => $e,
				'podcastUrl' => $url,
			]);
			return null;
		}
	}

	private function createMetricsForUrl(string $url): PodcastMetrics {
		return new PodcastMetrics(
			url: $url,
			listenedSeconds: 0,
			actionCounts: new PodcastActionCounts(),
			podcastData: $this->tryGetParsedPodcastData($url),
		);
	}
}
