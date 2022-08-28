<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

class PodcastMetrics {
	private string $url;
	private int $listenedSeconds;
	private PodcastActionCounts $actionCounts;
	private ?PodcastData $podcastData;

	public function __construct(
		string $url,
		int $listenedSeconds = 0,
		?PodcastActionCounts $actionCounts = null,
		?PodcastData $podcastData = null,
	) {
		$this->url = $url;
		$this->actionCounts = $actionCounts ?? new PodcastActionCounts;
		$this->listenedSeconds = $listenedSeconds;
		$this->podcastData = $podcastData;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * @return PodcastActionCounts
	 */
	public function getActionCounts(): PodcastActionCounts {
		return $this->actionCounts;
	}

	/**
	 * @return int
	 */
	public function getListenedSeconds(): int {
		return $this->listenedSeconds;
	}

	/**
	 * @param int $seconds
	 */
	public function addListenedSeconds(int $seconds): void {
		$this->listenedSeconds += $seconds;
	}

	/**
	 * @return PodcastData|null
	 */
	public function getPodcastData(): ?PodcastData {
		return $this->podcastData;
	}

	public function toArray(): array {
		return
		[
			'url' => $this->url,
			'listenedSeconds' => $this->listenedSeconds,
			'actionCounts' => $this->actionCounts->toArray(),
			'podcastData' => $this->podcastData->toArray(),
		];
	}
}
