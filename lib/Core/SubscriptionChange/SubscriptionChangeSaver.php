<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\SubscriptionChange;

use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeWriter;
use OCP\DB\Exception;

class SubscriptionChangeSaver {

	/**
	 * @var SubscriptionChangesReader
	 */
	private SubscriptionChangesReader $subscriptionChangeReader;
	/**
	 * @var SubscriptionChangeRepository
	 */
	private SubscriptionChangeRepository $subscriptionChangeRepository;
	/**
	 * @var SubscriptionChangeWriter
	 */
	private SubscriptionChangeWriter $subscriptionChangeWriter;
	/**
	 * @var SubscriptionChangeRequestParser
	 */
	private SubscriptionChangeRequestParser $subscriptionChangeRequestParser;

	public function __construct(
		SubscriptionChangeRequestParser $subscriptionChangeRequestParser,
		SubscriptionChangeRepository $subscriptionChangeRepository,
		SubscriptionChangeWriter $subscriptionChangeWriter
	) {
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
		$this->subscriptionChangeWriter = $subscriptionChangeWriter;
		$this->subscriptionChangeRequestParser = $subscriptionChangeRequestParser;
	}

	/**
	 * @param string|array $urlsSubscribed
	 * @param string|array $urlsUnsubscribed
	 * @param string $userId
	 */
	public function saveSubscriptionChanges($urlsSubscribed, $urlsUnsubscribed, string $userId): void {
		$subscriptionChanges = $this->subscriptionChangeRequestParser->createSubscriptionChangeList($urlsSubscribed, $urlsUnsubscribed);
		foreach ($subscriptionChanges as $urlChangedSubscriptionStatus) {
			$subscriptionChangeEntity = new SubscriptionChangeEntity();
			$subscriptionChangeEntity->setUrl($urlChangedSubscriptionStatus->getUrl());
			$subscriptionChangeEntity->setSubscribed($urlChangedSubscriptionStatus->isSubscribed());
			$subscriptionChangeEntity->setUpdated((new \DateTime())->format("Y-m-d\TH:i:s"));
			$subscriptionChangeEntity->setUserId($userId);

			try {
				$this->subscriptionChangeWriter->create($subscriptionChangeEntity);
			} catch (\Exception $exception) {
				if ($exception->getReason() === Exception::REASON_UNIQUE_CONSTRAINT_VIOLATION) {
					$idEpisodeActionEntityToUpdate = $this->subscriptionChangeRepository->findByUrl($subscriptionChangeEntity->getUrl(), $userId)->getId();
					$subscriptionChangeEntity->setId($idEpisodeActionEntityToUpdate);
					$this->subscriptionChangeWriter->update($subscriptionChangeEntity);
				}
			}
		}
	}


}
