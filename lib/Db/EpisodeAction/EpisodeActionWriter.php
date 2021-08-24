<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\EpisodeAction;

class EpisodeActionWriter {

	/**
	 * @var EpisodeActionMapper
	 */
	private EpisodeActionMapper $episodeActionMapper;

	public function __construct(EpisodeActionMapper $episodeActionMapper) {
		$this->episodeActionMapper = $episodeActionMapper;
	}

	public function save(EpisodeActionEntity $episodeActionEntity): EpisodeActionEntity {
		return $this->episodeActionMapper->insert($episodeActionEntity);
	}

	public function update(EpisodeActionEntity $episodeActionEntity) {
		return $this->episodeActionMapper->update($episodeActionEntity);

	}

	public function purge() {
		foreach ($this->episodeActionMapper->findAll() as $entity) {
			$this->episodeActionMapper->delete($entity);
		}
	}
}
