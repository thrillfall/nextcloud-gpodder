<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use DateTime;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCP\Settings\ISettings;

class EpisodeActionController extends Controller {

	private IL10N $l;
	private SubscriptionChangeRepository $subscriptionChangeRepository;
	private EpisodeActionRepository $episodeActionRepository;
	private string $userId;

	public function __construct(
		string $AppName,
		IRequest $request,
		$UserId,
		IL10N $l,
		SubscriptionChangeRepository $subscriptionChangeRepository,
		EpisodeActionRepository $episodeActionRepository,
	) {
		parent::__construct($AppName, $request);
		$this->l = $l;
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
		$this->episodeActionRepository = $episodeActionRepository;
		$this->userId = $UserId ?? '';
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
		$subscriptions = $this->extractUrlList($this->subscriptionChangeRepository->findAllSubscribed($sinceDatetime, $this->userId));
		$episodeActions = $this->episodeActionRepository->findAll(0, $this->userId);
		$subStats = array();
		foreach ($episodeActions as $action) {
			$pod = $action->getPodcast();
			$sub = $subStats[$pod] ?? array();
			$sub['started']++;
			$subStats[$pod] = $sub;
		}
		return new JSONResponse([
			'subscriptions' => $subscriptions,
			'subStats' => $subStats,
		]);
	}

	/**
	 * @param array $allSubscribed
	 * @return mixed
	 */
	private function extractUrlList(array $allSubscribed): array {
		return array_map(static function (SubscriptionChangeEntity $subscription) {
			return $subscription->getUrl();
		}, $allSubscribed);
	}
}
