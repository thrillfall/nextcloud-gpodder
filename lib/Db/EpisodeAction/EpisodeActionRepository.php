<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\EpisodeAction;

use DateTime;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeAction;

class EpisodeActionRepository
{
    private EpisodeActionMapper $episodeActionMapper;

    public function __construct(EpisodeActionMapper $episodeActionMapper)
    {
        $this->episodeActionMapper = $episodeActionMapper;
    }

    /**
     * @param int $sinceEpoch
     * @param string $userId
     *
     * @return EpisodeAction[]
     */
    public function findAll(int $sinceEpoch, string $userId): array
    {
        $episodeActions = [];
        foreach (
            $this->episodeActionMapper->findAll($sinceEpoch, $userId)
            as $entity
        ) {
            $episodeActions[] = $this->mapEntityToEpisodeAction($entity);
        }
        return $episodeActions;
    }

    public function findByEpisodeUrl(
        string $episodeUrl,
        string $userId
    ): ?EpisodeAction {
        $episodeActionEntity = $this->episodeActionMapper->findByEpisodeUrl(
            $episodeUrl,
            $userId
        );

        if ($episodeActionEntity === null) {
            return null;
        }

        return $this->mapEntityToEpisodeAction($episodeActionEntity);
    }

    public function findByGuid(string $guid, string $userId): ?EpisodeAction
    {
        $episodeActionEntity = $this->episodeActionMapper->findByGuid(
            $guid,
            $userId
        );

        if ($episodeActionEntity === null) {
            return null;
        }

        return $this->mapEntityToEpisodeAction($episodeActionEntity);
    }

    public function deleteEpisodeActionByEpisodeUrl(
        string $episodeUrl,
        string $userId
    ): void {
        $episodeAction = $this->episodeActionMapper->findByEpisodeUrl(
            $episodeUrl,
            $userId
        );
        $this->episodeActionMapper->delete($episodeAction);
    }

    private function mapEntityToEpisodeAction(
        EpisodeActionEntity $episodeActionEntity
    ): EpisodeAction {
        return new EpisodeAction(
            $episodeActionEntity->getPodcast(),
            $episodeActionEntity->getEpisode(),
            $episodeActionEntity->getAction(),
            DateTime::createFromFormat(
                "U",
                (string) $episodeActionEntity->getTimestampEpoch()
            )->format("c"),
            $episodeActionEntity->getStarted(),
            $episodeActionEntity->getPosition(),
            $episodeActionEntity->getTotal(),
            $episodeActionEntity->getGuid(),
            $episodeActionEntity->getId()
        );
    }
}
