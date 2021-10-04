<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

class EpisodeActionReader
{

	const EPISODEACTION_IDENTIFIER = 'EpisodeAction{';

	/**
	 * @param string $episodeActionsString
	 * @return EpisodeAction[]
	 */
	public function fromString(string $episodeActionsString): array
	{


		$patterns = [
			'/EpisodeAction{(podcast=\')(?<podcast>.*?)(\', episode=\')(?<episode>.*?)(\', guid=\')(?<guid>.*?)(\', action=)(?<action>.*?)(, timestamp=)(?<timestamp>.*?)(, started=)(?<started>.*?)(, position=)(?<position>.*?)(, total=)(?<total>.*?)}]*/',
			'/EpisodeAction{(podcast=\')(?<podcast>.*?)(\', episode=\')(?<episode>.*?)(\', action=)(?<action>.*?)(, timestamp=)(?<timestamp>.*?)(, started=)(?<started>.*?)(, position=)(?<position>.*?)(, total=)(?<total>.*?)}]*/',
		];

		$episodeActions = [];

		$episodeActionStrings = explode(self::EPISODEACTION_IDENTIFIER, $episodeActionsString);
		array_shift($episodeActionStrings);

		foreach ($episodeActionStrings as $episodeActionString) {
			foreach ($patterns as $pattern) {
				preg_match(
					$pattern,
					self::EPISODEACTION_IDENTIFIER . $episodeActionString,
					$matches
				);

				if ($matches["action"] !== null) {
					$episodeActions[] = new EpisodeAction(
						$matches["podcast"],
						$matches["episode"],
						$matches["action"],
						$matches["timestamp"],
						(int)$matches["started"],
						(int)$matches["position"],
						(int)$matches["total"],
						$matches["guid"] ?? null,
						null,
					);
					break;
				}
			}

		}
		return $episodeActions;
	}

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
