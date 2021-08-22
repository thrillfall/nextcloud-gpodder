<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

class EpisodeActionReader {
	/**
	 * Reads and parses an EpisodeActions string and returns an EpisodeAction array
	 *
	 * @param string $episodeActionString
	 * @return array
	 */
	public function fromString(string $episodeActionString): array {
		
		$episodeActions = array();

		$seek = 0;

        while (strpos($episodeActionString, 'EpisodeAction{', $seek) >= $seek) {
            if (($seek = strpos($episodeActionString, 'EpisodeAction{', $seek)) === false) {
                continue;
            }

            preg_match(
                '/EpisodeAction{(podcast=\')(?<podcast>.*?)(\', episode=\')(?<episode>.*?)(\', action=)(?<action>.*?)(, timestamp=)(?<timestamp>.*?)(, started=)(?<started>.*?)(, position=)(?<position>.*?)(, total=)(?<total>.*?)}]*/',
                substr($episodeActionString, $seek),
                $matches
            );

            // change for next iteration
            $seek++;

            array_push($episodeActions, new EpisodeAction(
                $matches["podcast"],
                $matches["episode"],
                $matches["action"],
                $matches["timestamp"],
                (int)$matches["started"],
                (int)$matches["position"],
                (int)$matches["total"],
            ));
        }

		return $episodeActions;
	}

}
