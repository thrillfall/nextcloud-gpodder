<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\EpisodeAction;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class EpisodeActionMapper extends \OCP\AppFramework\Db\QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'gpoddersync_episode_action', EpisodeActionEntity::class);
	}

	public function findAll(\DateTime $sinceTimestamp, string $userId): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->gt('timestamp', $qb->createNamedParameter($sinceTimestamp, IQueryBuilder::PARAM_DATE))
			)
		->andWhere(
			$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))

		);

		return $this->findEntities($qb);
	}

	public function findByEpisode(string $episode,  string $userId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('episode', $qb->createNamedParameter($episode))
			)
		->andWhere(
			$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
		);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
		} catch (MultipleObjectsReturnedException $e) {
		}
	}
}
