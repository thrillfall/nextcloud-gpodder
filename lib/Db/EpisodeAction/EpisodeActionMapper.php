<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\EpisodeAction;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class EpisodeActionMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'gpodder_episode_action', EpisodeActionEntity::class);
	}

	/**
	 * @throws Exception
	 */
	public function findAll(int $sinceTimestamp, string $userId, $sort = '', $order = 'DESC'): array
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

        if ($sort !== '') {
            $qb->orderBy($sort, $order);
        }

		return $this->findEntities($qb);

	}

	/**
	 * @param string $episodeIdentifier
	 * @param string $userId
	 * @return EpisodeActionEntity|null
	 */
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
			return $this->findEntity($qb);
		} catch (DoesNotExistException|MultipleObjectsReturnedException|Exception $e) {
			return null;
		}
	}


}
