<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use DateTime;

use OCA\GPodderSync\Service\PodcastCacheService;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCP\Settings\ISettings;

use Psr\Log\LoggerInterface;

class PersonalSettingsController extends Controller {

	private LoggerInterface $logger;
	private string $userId;
	private SubscriptionChangeRepository $subscriptionChangeRepository;
	private EpisodeActionRepository $episodeActionRepository;
	private PodcastCacheService $podcastCacheService;

	public function __construct(
		string $AppName,
		IRequest $request,
		LoggerInterface $logger,
		string $UserId,
		SubscriptionChangeRepository $subscriptionChangeRepository,
		EpisodeActionRepository $episodeActionRepository,
		PodcastCacheService $podcastCacheService,
	) {
		parent::__construct($AppName, $request);
		$this->logger = $logger;
		$this->userId = $UserId ?? '';
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
		$this->episodeActionRepository = $episodeActionRepository;
		$this->podcastCacheService = $podcastCacheService;
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function metrics(): JSONResponse {
		$sinceDatetime = (new DateTime)->setTimestamp(0);
		$subscriptionChanges = $this->subscriptionChangeRepository->findAllSubscribed($sinceDatetime, $this->userId);
		$episodeActions = $this->episodeActionRepository->findAll(0, $this->userId);

		$subStats = array();
		foreach ($episodeActions as $ep) {
			$url = $ep->getPodcast();
			$stats = $subStats[$url] ?? $this->defaultSubscriptionData();
			$actionCounts = $stats['actionCounts'];
			$actionLower = strtolower($ep->getAction());
			if (array_key_exists($actionLower, $actionCounts)) {
				$actionCounts[$actionLower]++;
			}
			$stats['actionCounts'] = $actionCounts;
			if ($actionLower == 'play') {
				$seconds = $ep->getPosition();
				if ($seconds && $seconds != -1) {
					$stats['listenedSeconds'] += $seconds;
				}
			}
			$subStats[$url] = $stats;
		}

		$subscriptions = array_map(function (SubscriptionChangeEntity $sub) use ($subStats) {
			$url = $sub->getUrl();
			$stats = $subStats[$url] ?? $this->defaultSubscriptionData();
			$sub = [
				'url' => $url ?? '',
				'listenedSeconds' => $stats['listenedSeconds'],
				'actionCounts' => $stats['actionCounts'],
			];
			try {
				$podcast = $this->podcastCacheService->getCachedOrFetchPodcastData($url);
				$sub['podcast'] = $podcast;
			} catch (Exception $e) {
				$sub['podcast'] = null;
				$this->logger->error("Failed to get podcast data.", [
					'exception' => $e,
					'podcastUrl' => $url,
				]);
			}
			return $sub;
		}, $subscriptionChanges);

		return new JSONResponse([
			'subscriptions' => $subscriptions,
		]);
	}

	private function defaultSubscriptionData(): array {
		return [
			'listenedSeconds' => 0,
			'actionCounts' => [
				'download' => 0,
				'delete' => 0,
				'play' => 0,
				'new' => 0,
				'flattr' => 0,
			],
		];
	}
}
