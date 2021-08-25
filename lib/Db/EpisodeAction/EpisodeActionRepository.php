<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\EpisodeAction;

class EpisodeActionRepository {
	/**
	 * @var EpisodeActionMapper
	 */
	private EpisodeActionMapper $episodeActionMapper;

	public function __construct(EpisodeActionMapper $episodeActionMapper) {
		$this->episodeActionMapper = $episodeActionMapper;
	}

	public function findAll(\DateTime $sinceTimestamp, string $userId) : array {
		return $this->episodeActionMapper->findAll($sinceTimestamp, $userId);
	}

	public function findByEpisodeIdentifier(string $identifier, string $userId): ?EpisodeActionEntity {
		return $this->episodeActionMapper->findByEpisodeIdentifier($identifier, $userId);
	}

}
