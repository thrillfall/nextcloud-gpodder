<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

class EpisodeActionReader {

    const EPISODEACTION_IDENTIFIER = 'EpisodeAction{';

	/**
	 * @param string $episodeActionString
	 * @return EpisodeAction[]
	 */
	public function fromString(string $episodeActionString): array {
		
		$episodeActions = [];

        $episodeActionStrings = explode(self::EPISODEACTION_IDENTIFIER, $episodeActionString);

        for($i = 1; $i < count($episodeActionStrings); $i++) {

            preg_match(
                '/EpisodeAction{(podcast=\')(?<podcast>.*?)(\', episode=\')(?<episode>.*?)(\', action=)(?<action>.*?)(, timestamp=)(?<timestamp>.*?)(, started=)(?<started>.*?)(, position=)(?<position>.*?)(, total=)(?<total>.*?)}]*/',
                self::EPISODEACTION_IDENTIFIER . $episodeActionStrings[$i],
                $matches
            );

            $episodeActions[] = new EpisodeAction(
                $matches["podcast"],
                $matches["episode"],
                $matches["action"],
                $matches["timestamp"],
                (int)$matches["started"],
                (int)$matches["position"],
                (int)$matches["total"],
            );
        }

		return $episodeActions;
	}

}
