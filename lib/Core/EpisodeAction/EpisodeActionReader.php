<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

class EpisodeActionReader
{
	/**
	 * @param $episodeActionsArray[]
	 * @return EpisodeAction[]
	 */
	public function fromArray(array $episodeActionsArray): array
	{
		$episodeActions = [];

		foreach($episodeActionsArray as $episodeAction) {
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
}
