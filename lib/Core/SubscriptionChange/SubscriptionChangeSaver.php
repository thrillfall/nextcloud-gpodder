<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\SubscriptionChange;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeWriter;

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

	public function saveSubscriptionChanges(string $urlsSubscribed, string $urlsUnsubscribed, string $userId) : void {
		$subscriptionChanges = $this->subscriptionChangeRequestParser->createSubscriptionChangeList($urlsSubscribed, $urlsUnsubscribed);
		foreach ($subscriptionChanges as $urlChangedSubscriptionStatus) {
			$subscriptionChangeEntity = new SubscriptionChangeEntity();
			$subscriptionChangeEntity->setUrl($urlChangedSubscriptionStatus->getUrl());
			$subscriptionChangeEntity->setSubscribed($urlChangedSubscriptionStatus->isSubscribed());
			$subscriptionChangeEntity->setUpdated((new \DateTime())->format("Y-m-d\TH:i:s"));
			$subscriptionChangeEntity->setUserId($userId);

			try {
				$this->subscriptionChangeWriter->create($subscriptionChangeEntity);
			} catch (UniqueConstraintViolationException $ex) {
				$idEpisodeActionEntityToUpdate = $this->subscriptionChangeRepository->findByUrl($subscriptionChangeEntity->getUrl(), $userId)->getId();
				$subscriptionChangeEntity->setId($idEpisodeActionEntityToUpdate);
				$this->subscriptionChangeWriter->update($subscriptionChangeEntity);
			}
		}
	}


}
