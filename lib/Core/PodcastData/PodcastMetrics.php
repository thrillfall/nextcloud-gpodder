<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

use JsonSerializable;

class PodcastMetrics implements JsonSerializable {
	private string $url;
	private int $listenedSeconds;
	private PodcastActionCounts $actionCounts;

	public function __construct(
		string $url,
		int $listenedSeconds = 0,
		?PodcastActionCounts $actionCounts = null,
	) {
		$this->url = $url;
		$this->actionCounts = $actionCounts ?? new PodcastActionCounts;
		$this->listenedSeconds = $listenedSeconds;
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
	 * @return array<string,mixed>
	 */
	public function toArray(): array {
		return
		[
			'url' => $this->url,
			'listenedSeconds' => $this->listenedSeconds,
			'actionCounts' => $this->actionCounts->toArray(),
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	public function jsonSerialize(): mixed {
		return $this->toArray();
	}
}
