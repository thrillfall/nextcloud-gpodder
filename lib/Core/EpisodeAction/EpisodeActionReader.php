<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

use InvalidArgumentException;

class EpisodeActionReader {
	private array $requiredProperties = ['podcast', 'episode', 'action', 'timestamp'];

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
	 * @param array $episodeAction
	 * @return bool
	 */
	private function hasRequiredProperties(array $episodeAction): bool {
		return (count(array_intersect($this->requiredProperties, array_keys($episodeAction))) === count($this->requiredProperties));
	}
}
