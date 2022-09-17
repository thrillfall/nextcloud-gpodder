<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\SubscriptionChange;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class SubscriptionChangeMapper extends \OCP\AppFramework\Db\QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'gpodder_subscriptions', SubscriptionChangeEntity::class);
	}

	public function findAll(string $userId): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			);

		return $this->findEntities($qb);
	}

	public function findByUrl(string $url, string $userId): ?SubscriptionChangeEntity {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('url', $qb->createNamedParameter($url))
			)
			->andWhere(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
		} catch (MultipleObjectsReturnedException $e) {
		}
		return null;
	}

	public function remove(SubscriptionChangeEntity $subscriptionChangeEntity) {
		$this->delete($subscriptionChangeEntity);
	}

	public function findAllSubscriptionState(bool $subscribed, \DateTime $sinceTimestamp, string $userId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('url')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('subscribed', $qb->createNamedParameter($subscribed, IQueryBuilder::PARAM_BOOL))
			)->andWhere(
				$qb->expr()->gt('updated', $qb->createNamedParameter($sinceTimestamp, IQueryBuilder::PARAM_DATE))
			)
			->andWhere(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			);

		return $this->findEntities($qb);
	}


}
