<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

class EpisodeActionReader {

    const EPISODEACTION_IDENTIFIER = 'EpisodeAction{';

	/**
	 * @param string $episodeActionsString
	 * @return EpisodeAction[]
	 */
	public function fromString(string $episodeActionsString): array {
		
		$episodeActions = [];

        $episodeActionStrings = explode(self::EPISODEACTION_IDENTIFIER, $episodeActionsString);
        array_shift($episodeActionStrings);
        
        foreach($episodeActionStrings as $episodeActionString) {

            preg_match(
                '/EpisodeAction{(podcast=\')(?<podcast>.*?)(\', episode=\')(?<episode>.*?)(\', action=)(?<action>.*?)(, timestamp=)(?<timestamp>.*?)(, started=)(?<started>.*?)(, position=)(?<position>.*?)(, total=)(?<total>.*?)}]*/',
                self::EPISODEACTION_IDENTIFIER . $episodeActionString,
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
