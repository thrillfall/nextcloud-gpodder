<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

use DateTime;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCP\DB\Exception;

class EpisodeActionSaver
{

	private EpisodeActionRepository $episodeActionRepository;
	private EpisodeActionWriter $episodeActionWriter;
	private EpisodeActionReader $episodeActionReader;

	private const DATETIME_FORMAT = 'Y-m-d\TH:i:s';

	public function __construct(
		EpisodeActionRepository $episodeActionRepository,
		EpisodeActionWriter $episodeActionWriter,
		EpisodeActionReader $episodeActionReader
	)
	{
		$this->episodeActionRepository = $episodeActionRepository;
		$this->episodeActionWriter = $episodeActionWriter;
		$this->episodeActionReader = $episodeActionReader;
	}

	/**
	 * @param array $episodeActionsArray
	 * @param string $userId
	 * @return EpisodeActionEntity[]
	 */
	public function saveEpisodeActions(array $episodeActionsArray, string $userId): array
	{
		$episodeActions = $this->episodeActionReader->fromArray($episodeActionsArray);

		$episodeActionEntities = [];

        foreach ($episodeActions as $episodeAction) {
			$episodeActionEntity = $this->hydrateEpisodeActionEntity($episodeAction, $userId);

			try {
                $episodeActionEntities[] = $this->episodeActionWriter->save($episodeActionEntity);
            } catch (Exception $exception) {
                if ($exception->getReason() === Exception::REASON_UNIQUE_CONSTRAINT_VIOLATION) {
					try {
						$episodeActionEntities[] = $this->updateEpisodeAction($episodeActionEntity, $userId);
					} catch (Exception $exception) {}
				}
            }
        }
		return $episodeActionEntities;
	}

	private function convertTimestampToUnixEpoch(string $timestamp): string
	{
		return DateTime::createFromFormat(self::DATETIME_FORMAT, $timestamp)
			->format("U");
	}

	/**
	 * @throws Exception
	 */
	private function updateEpisodeAction(
		EpisodeActionEntity $episodeActionEntity,
		string $userId
	): EpisodeActionEntity
	{
		$identifier = $episodeActionEntity->getGuid() ?? $episodeActionEntity->getEpisode();
		$episodeActionToUpdate = $this->episodeActionRepository->findByEpisodeIdentifier(
			$identifier,
			$userId
		);

		if ($episodeActionToUpdate === null && $episodeActionEntity->getGuid() !== null) {
			$episodeActionToUpdate = $this->getOldEpisodeActionByEpisodeUrl($episodeActionEntity->getEpisode(), $userId);
		}

		$episodeActionEntity->setId($episodeActionToUpdate->getId());

		$this->ensureGuidDoesNotGetNulledWithOldData($episodeActionToUpdate, $episodeActionEntity);

		return $this->episodeActionWriter->update($episodeActionEntity);
	}

	private function getOldEpisodeActionByEpisodeUrl(string $episodeUrl, string $userId): ?EpisodeAction
	{
		return $this->episodeActionRepository->findByEpisodeIdentifier(
			$episodeUrl,
			$userId
		);
	}

	private function ensureGuidDoesNotGetNulledWithOldData(EpisodeAction $episodeActionToUpdate, EpisodeActionEntity $episodeActionEntity): void
	{
		$existingGuid = $episodeActionToUpdate->getGuid();
		if ($existingGuid !== null && $episodeActionEntity->getGuid() === null) {
			$episodeActionEntity->setGuid($existingGuid);
		}
	}

	private function hydrateEpisodeActionEntity(EpisodeAction $episodeAction, string $userId): EpisodeActionEntity
	{
		$episodeActionEntity = new EpisodeActionEntity();
		$episodeActionEntity->setPodcast($episodeAction->getPodcast());
		$episodeActionEntity->setEpisode($episodeAction->getEpisode());
		$episodeActionEntity->setGuid($episodeAction->getGuid());
		$episodeActionEntity->setAction($episodeAction->getAction());
		$episodeActionEntity->setPosition($episodeAction->getPosition());
		$episodeActionEntity->setStarted($episodeAction->getStarted());
		$episodeActionEntity->setTotal($episodeAction->getTotal());
		$episodeActionEntity->setTimestampEpoch($this->convertTimestampToUnixEpoch($episodeAction->getTimestamp()));
		$episodeActionEntity->setUserId($userId);

		return $episodeActionEntity;
	}
}
