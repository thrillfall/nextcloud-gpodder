<?php
namespace OCA\GPodderSync\Settings;

use DateTime;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;

class GPodderSyncPersonal implements ISettings {
	private IL10N $l;
	private IConfig $config;
	private SubscriptionChangeRepository $subscriptionChangeRepository;
	private EpisodeActionRepository $episodeActionRepository;
	private string $userId;

	public function __construct(
		IConfig $config,
		IL10N $l,
		$UserId,
		SubscriptionChangeRepository $subscriptionChangeRepository,
		EpisodeActionRepository $episodeActionRepository,
	) {
		$this->config = $config;
		$this->l = $l;
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
		$this->episodeActionRepository = $episodeActionRepository;
		$this->userId = $UserId ?? '';
	}

	public function getForm(): TemplateResponse {
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
		$params = array(
			'subscriptions' => $subscriptions,
			'subStats' => $subStats,
		);
		return new TemplateResponse('gpoddersync', 'settings/personal', $params);
	}

	public function getSection(): string {
		return 'gpoddersync';
	}

	public function getPriority(): int {
		return 198;
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
