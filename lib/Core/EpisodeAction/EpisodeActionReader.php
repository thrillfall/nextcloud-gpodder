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
				$episodeAction["action"],
				$episodeAction["timestamp"],
				$episodeAction["started"],
				$episodeAction["position"],
				$episodeAction["total"],
				$episodeAction["guid"] ?? null,
			);
		}

		return $episodeActions;
	}
}
