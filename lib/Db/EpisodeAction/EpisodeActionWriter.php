<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\EpisodeAction;

use OCP\DB\Exception;

class EpisodeActionWriter {

	/**
	 * @var EpisodeActionMapper
	 */
	private EpisodeActionMapper $episodeActionMapper;

	public function __construct(EpisodeActionMapper $episodeActionMapper) {
		$this->episodeActionMapper = $episodeActionMapper;
	}

	/**
	 * @throws Exception
	 */
	public function save(EpisodeActionEntity $episodeActionEntity): EpisodeActionEntity {
		return $this->episodeActionMapper->insert($episodeActionEntity);
	}

	/**
	 * @throws Exception
	 */
	public function update(EpisodeActionEntity $episodeActionEntity) {
		return $this->episodeActionMapper->update($episodeActionEntity);

	}
}
