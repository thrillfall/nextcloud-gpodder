<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

use DateTime;

use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;

use Psr\Log\LoggerInterface;

class PodcastMetricsReader {

	private SubscriptionChangeRepository $subscriptionChangeRepository;
	private EpisodeActionRepository $episodeActionRepository;

	public function __construct(
		SubscriptionChangeRepository $subscriptionChangeRepository,
		EpisodeActionRepository $episodeActionRepository
	) {
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
		$this->episodeActionRepository = $episodeActionRepository;
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

	private function createMetricsForUrl(string $url): PodcastMetrics {
		return new PodcastMetrics(
			$url,
			0,
			new PodcastActionCounts()
		);
	}

}
