<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\EpisodeAction;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeAction;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use Safe\DateTime;

class EpisodeActionMapper extends \OCP\AppFramework\Db\QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'gpodder_episode_action', EpisodeActionEntity::class);
	}

	public function findAll(int $sinceTimestamp, string $userId): array
	{
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->gt('timestamp_epoch', $qb->createNamedParameter($sinceTimestamp, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))

			);

		return $this->findEntities($qb);

	}

	public function findByEpisodeIdentifier(string $episodeIdentifier, string $userId) : ?EpisodeActionEntity
	{
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->orX(
					$qb->expr()->eq('episode', $qb->createNamedParameter($episodeIdentifier)),
					$qb->expr()->eq('guid', $qb->createNamedParameter($episodeIdentifier)))
			)
			->andWhere(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			);

		try {
			/** @var EpisodeActionEntity $episodeActionEntity */
			$episodeActionEntity = $this->findEntity($qb);

			return $episodeActionEntity;
		} catch (DoesNotExistException $e) {
		} catch (MultipleObjectsReturnedException $e) {
		}

		return null;
	}


}
