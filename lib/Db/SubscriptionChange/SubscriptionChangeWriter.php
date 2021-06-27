<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\SubscriptionChange;


class SubscriptionChangeWriter {

	/**
	 * @var SubscriptionChangeMapper
	 */
	private SubscriptionChangeMapper $subscriptionChangeMapper;

	public function __construct(SubscriptionChangeMapper $subscriptionChangeMapper) {
		$this->subscriptionChangeMapper = $subscriptionChangeMapper;
	}


	public function purge() {
		foreach ($this->subscriptionChangeMapper->findAll() as $entity) {
			$this->subscriptionChangeMapper->delete($entity);
		}
	}

	public function create(SubscriptionChangeEntity $subscriptionChangeEntity): SubscriptionChangeEntity{
		return $this->subscriptionChangeMapper->insert($subscriptionChangeEntity);
	}

	public function update(SubscriptionChangeEntity $subscriptionChangeEntity): SubscriptionChangeEntity{
		return $this->subscriptionChangeMapper->update($subscriptionChangeEntity);
	}
}
