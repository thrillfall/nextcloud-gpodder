<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\EpisodeAction;

use DateTime;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeAction;

class EpisodeActionRepository {

	private EpisodeActionMapper $episodeActionMapper;

	public function __construct(EpisodeActionMapper $episodeActionMapper) {
		$this->episodeActionMapper = $episodeActionMapper;
	}

	/**
	 * @param int $sinceEpoch
	 * @param string $userId
	 *
	 * @return EpisodeAction[]
	 */
	public function findAll(int $sinceEpoch, string $userId) : array {
		$episodeActions = [];
		foreach ($this->episodeActionMapper->findAll($sinceEpoch, $userId) as $entity) {
			$episodeActions[] = $this->mapEntityToEpisodeAction($entity);
		}
		return $episodeActions;
	}

	public function findByEpisodeIdentifier(string $identifier, string $userId): ?EpisodeAction {
		$episodeActionEntity = $this->episodeActionMapper->findByEpisodeIdentifier($identifier, $userId);

		if ($episodeActionEntity === null) {
			return null;
		}

		return $this->mapEntityToEpisodeAction(
			$episodeActionEntity
		);
	}

	private function mapEntityToEpisodeAction(EpisodeActionEntity $episodeActionEntity): EpisodeAction
	{
		return new EpisodeAction(
			$episodeActionEntity->getPodcast(),
			$episodeActionEntity->getEpisode(),
			$episodeActionEntity->getAction(),
			DateTime::createFromFormat(
				"U",
				(string)$episodeActionEntity->getTimestampEpoch())
				->format("Y-m-d\TH:i:s"),
			$episodeActionEntity->getStarted(),
			$episodeActionEntity->getPosition(),
			$episodeActionEntity->getTotal(),
			$episodeActionEntity->getGuid(),
			$episodeActionEntity->getId(),
		);
	}

}
