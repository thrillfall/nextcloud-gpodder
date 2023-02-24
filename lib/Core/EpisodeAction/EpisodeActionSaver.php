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
        EpisodeActionWriter     $episodeActionWriter,
        EpisodeActionReader     $episodeActionReader
    )
    {
        $this->episodeActionRepository = $episodeActionRepository;
        $this->episodeActionWriter = $episodeActionWriter;
        $this->episodeActionReader = $episodeActionReader;
    }

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
                    $episodeActionEntities[] = $this->updateEpisodeAction($episodeActionEntity, $userId);
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

    private function updateEpisodeAction(
        EpisodeActionEntity $episodeActionEntity,
        string              $userId
    ): EpisodeActionEntity
    {
        $episodeActionToUpdate = $this->findEpisodeActionToUpdate($episodeActionEntity, $userId);

        $episodeActionEntity->setId($episodeActionToUpdate->getId());

        $this->ensureGuidDoesNotGetNulledWithOldData($episodeActionToUpdate, $episodeActionEntity);

        try {
            return $this->episodeActionWriter->update($episodeActionEntity);
        } catch (Exception $exception) {
            if ($exception->getReason() === Exception::REASON_UNIQUE_CONSTRAINT_VIOLATION) {
                $this->deleteConflictingEpisodeAction($episodeActionEntity, $userId);
            }
        }
        return $this->episodeActionWriter->update($episodeActionEntity);

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

    private function findEpisodeActionToUpdate(EpisodeActionEntity $episodeActionEntity, string $userId): ?EpisodeAction
    {
        $episodeAction = null;
        if ($episodeActionEntity->getGuid() !== null) {
            $episodeAction = $this->episodeActionRepository->findByGuid(
                $episodeActionEntity->getGuid(),
                $userId
            );
        }

        if ($episodeAction === null) {
            $episodeAction = $this->episodeActionRepository->findByEpisodeUrl(
                $episodeActionEntity->getEpisode(),
                $userId
            );
        }

        return $episodeAction;
    }

    /**
     * @param EpisodeActionEntity $episodeActionEntity
     * @param string $userId
     * @return void
     */
    private function deleteConflictingEpisodeAction(EpisodeActionEntity $episodeActionEntity, string $userId): void
    {
        $collidingEpisodeActionId = $this->episodeActionRepository->findByEpisodeUrl($episodeActionEntity->getGuid(), $userId)->getId();
        if ($collidingEpisodeActionId !== $episodeActionEntity->getId()) {
            $this->episodeActionRepository->deleteEpisodeActionByEpisodeUrl($episodeActionEntity->getGuid(), $userId);
        }
    }
}
