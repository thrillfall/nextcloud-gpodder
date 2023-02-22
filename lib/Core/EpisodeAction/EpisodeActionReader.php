<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

use InvalidArgumentException;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionData;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\ICache;
use OCP\ICacheFactory;

class EpisodeActionReader {
	private array $requiredProperties = ['podcast', 'episode', 'action', 'timestamp'];
    private EpisodeActionRepository $episodeActionRepository;
    private ?ICache $cache = null;
    private IClient $httpClient;

    public function __construct(
        IClientService $httpClientService,
        EpisodeActionRepository $episodeActionRepository,
		ICacheFactory $cacheFactory
    ) {
        if ($cacheFactory->isLocalCacheAvailable()) {
            $this->cache = $cacheFactory->createLocal('GPodderSync-Actions');
        }
        $this->httpClient = $httpClientService->newClient();
        $this->episodeActionRepository = $episodeActionRepository;
    }

    public function getCachedOrFetchActionExtraData(string $url, string $userId): ?EpisodeActionExtraData {
        // Set cache to null for debugging only
//        $this->cache = null;

        if ($this->cache == null) {
            return $this->fetchActionExtraData($url, $userId);
        }
        $oldData = $this->tryGetCachedActionExtraData($url);
        if ($oldData) {
            return $oldData;
        }
        $newData = $this->fetchActionExtraData($url, $userId);
        $this->trySetCachedActionExtraData($url, $newData);
        return $newData;
    }

    public function tryGetCachedActionExtraData(string $url): ?EpisodeActionExtraData {
        $oldData = $this->cache->get($url);
        if (!$oldData) {
            return null;
        }
        return EpisodeActionExtraData::fromArray($oldData);
    }

    public function trySetCachedActionExtraData(string $url, EpisodeActionExtraData $data): bool {
        return $this->cache->set($url, $data->toArray());
    }

    /**
	 * @param array $episodeActionsArray []
	 * @return EpisodeAction[]
	 * @throws InvalidArgumentException
	 */
	public function fromArray(array $episodeActionsArray): array {
		$episodeActions = [];

		foreach ($episodeActionsArray as $episodeAction) {
			if ($this->hasRequiredProperties($episodeAction) === false) {
				throw new InvalidArgumentException(sprintf('Client sent incomplete or invalid data: %s', json_encode($episodeAction, JSON_THROW_ON_ERROR)));
			}
			$episodeActions[] = new EpisodeAction(
				$episodeAction["podcast"],
				$episodeAction["episode"],
				strtoupper($episodeAction["action"]),
				$episodeAction["timestamp"],
				$episodeAction["started"] ?? -1,
				$episodeAction["position"] ?? -1,
				$episodeAction["total"] ?? -1,
				$episodeAction["guid"] ?? null,
				null
			);
		}

		return $episodeActions;
	}

    /**
     * @param string $userId
     *
     * @return EpisodeActionData[]
     */
    public function actions(string $userId, $sort = '', $order = 'DESC'): array {
        $episodeActions = $this->episodeActionRepository->findAll(0, $userId, $sort, $order);
        $episodeActionDataList = [];

        foreach ($episodeActions as $episodeAction) {
            $episodeActionData = new EpisodeActionData(
                $episodeAction->getPodcast(),
                $episodeAction->getEpisode(),
                $episodeAction->getAction(),
                $episodeAction->getPosition(),
                $episodeAction->getStarted(),
                $episodeAction->getTotal()
            );
            $episodeActionDataList[] = $episodeActionData;
        }

        return $episodeActionDataList;
    }

	/**
	 * @param array $episodeAction
	 * @return bool
	 */
	private function hasRequiredProperties(array $episodeAction): bool {
		return (count(array_intersect($this->requiredProperties, array_keys($episodeAction))) === count($this->requiredProperties));
	}

    public function fetchActionExtraData(string $episodeUrl, string $userId): ?EpisodeActionExtraData {
        if (!$this->userHasAction($episodeUrl, $userId)) {
            return null;
        }

        $episodeAction = $this->episodeActionRepository->findByEpisodeIdentifier($episodeUrl, $userId);

        $resp = $this->fetchUrl($episodeAction->getPodcast());
        $data = EpisodeActionExtraData::parseRssXml($resp->getBody(), $episodeUrl);

        return $data;
    }

    private function userHasAction(string $url, string $userId): bool {
        $episodeAction = $this->episodeActionRepository->findByEpisodeIdentifier($url, $userId);
        return $episodeAction !== null;
    }

    private function fetchUrl(string $url): IResponse {
        $resp = $this->httpClient->get($url);
        $statusCode = $resp->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \ErrorException("Web request returned non-2xx status code: $statusCode");
        }
        return $resp;
    }
}
