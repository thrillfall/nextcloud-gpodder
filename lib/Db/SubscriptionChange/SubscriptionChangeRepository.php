<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\SubscriptionChange;

class SubscriptionChangeRepository {

	/**
	 * @var SubscriptionChangeMapper
	 */
	private SubscriptionChangeMapper $subscriptionChangeMapper;

	public function __construct(SubscriptionChangeMapper $subscriptionChangeMapper) {
		$this->subscriptionChangeMapper = $subscriptionChangeMapper;
	}

	public function findAll() : array {
		return $this->subscriptionChangeMapper->findAll();
	}

	public function findByUrl(string $episode, string $userId): SubscriptionChangeEntity {
		return $this->subscriptionChangeMapper->findByUrl($episode, $userId);
	}

	public function findAllSubscribed(\DateTime $sinceTimestamp, string $userId) {
		return $this->subscriptionChangeMapper->findAllSubscriptionState(true, $sinceTimestamp, $userId);
	}

	public function findAllUnSubscribed(\DateTime $sinceTimestamp, string $userId) {
		return $this->subscriptionChangeMapper->findAllSubscriptionState(false, $sinceTimestamp, $userId);
	}
}
