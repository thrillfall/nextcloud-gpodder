<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

class EpisodeActionReader {
	public function fromString(string $episodeActionString): EpisodeAction {
		file_put_contents('actionreader.log', var_export($episodeActionString, true), FILE_APPEND);
		preg_match(
			'/\[EpisodeAction{(podcast=\')(?<podcast>.*?)(\', episode=\')(?<episode>.*?)(\', action=)(?<action>.*?)(, timestamp=)(?<timestamp>.*?)(, started=)(?<started>.*?)(, position=)(?<position>.*?)(, total=)(?<total>.*?)}]*/',
			$episodeActionString,
			$matches
		);

		file_put_contents('actionreader.log', var_export($matches, true), FILE_APPEND);

		return new EpisodeAction(
			$matches["podcast"],
			$matches["episode"],
			$matches["action"],
			$matches["timestamp"],
			(int)$matches["started"],
			(int)$matches["position"],
			(int)$matches["total"],
		);
	}

}
