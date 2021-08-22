<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\SubscriptionChange;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeWriter;
use OCP\DB\Exception;

class SubscriptionChangeSaver
{
	private SubscriptionChangeRepository $subscriptionChangeRepository;
	private SubscriptionChangeWriter $subscriptionChangeWriter;
	private SubscriptionChangeRequestParser $subscriptionChangeRequestParser;

	public function __construct(
		SubscriptionChangeRequestParser $subscriptionChangeRequestParser,
		SubscriptionChangeRepository $subscriptionChangeRepository,
		SubscriptionChangeWriter $subscriptionChangeWriter
	)
	{
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
		$this->subscriptionChangeWriter = $subscriptionChangeWriter;
		$this->subscriptionChangeRequestParser = $subscriptionChangeRequestParser;
	}

	public function saveSubscriptionChanges(array $urlsSubscribed, array $urlsUnsubscribed, string $userId): void
	{
		$subscriptionChanges = $this->subscriptionChangeRequestParser->createSubscriptionChangeList($urlsSubscribed, $urlsUnsubscribed);
		foreach ($subscriptionChanges as $urlChangedSubscriptionStatus) {
			$subscriptionChangeEntity = new SubscriptionChangeEntity();
			$subscriptionChangeEntity->setUrl($urlChangedSubscriptionStatus->getUrl());
			$subscriptionChangeEntity->setSubscribed($urlChangedSubscriptionStatus->isSubscribed());
			$subscriptionChangeEntity->setUpdated((new \DateTime())->format("Y-m-d\TH:i:s"));
			$subscriptionChangeEntity->setUserId($userId);

			try {
				$this->subscriptionChangeWriter->create($subscriptionChangeEntity);
			} catch (UniqueConstraintViolationException $uniqueConstraintViolationException) {
				$this->updateSubscription($subscriptionChangeEntity, $userId);
			} catch (Exception $exception) {
				if ($exception->getReason() === Exception::REASON_UNIQUE_CONSTRAINT_VIOLATION) {
					$this->updateSubscription($subscriptionChangeEntity, $userId);
				}
			}
		}
	}

	/**
	 * @param SubscriptionChangeEntity $subscriptionChangeEntity
	 * @param string $userId
	 *
	 * @return void
	 */
	private function updateSubscription(SubscriptionChangeEntity $subscriptionChangeEntity, string $userId): void
	{
		$idEpisodeActionEntityToUpdate = $this->subscriptionChangeRepository->findByUrl($subscriptionChangeEntity->getUrl(), $userId)->getId();
		$subscriptionChangeEntity->setId($idEpisodeActionEntityToUpdate);
		$this->subscriptionChangeWriter->update($subscriptionChangeEntity);
	}


}
