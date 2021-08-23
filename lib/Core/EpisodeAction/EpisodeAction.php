<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

class EpisodeAction {
	private string $podcast;
	private string $episode;
	private string $action;
	private string $timestamp;
	private int $started;
	private int $position;
	private int $total;
	private ?string $guid;

	public function __construct(
		string $podcast,
		string $episode,
		string $action,
		string $timestamp,
		int $started,
		int $position,
		int $total,
		?string $guid
	) {
		$this->podcast = $podcast;
		$this->episode = $episode;
		$this->action = $action;
		$this->timestamp = $timestamp;
		$this->started = $started;
		$this->position = $position;
		$this->total = $total;
		$this->guid = $guid;
	}

	/**
	 * @return string
	 */
	public function getPodcast(): string {
		return $this->podcast;
	}

	/**
	 * @return string
	 */
	public function getEpisode(): string {
		return $this->episode;
	}

	/**
	 * @return string
	 */
	public function getAction(): string {
		return $this->action;
	}

	/**
	 * @return string
	 */
	public function getTimestamp(): string {
		return $this->timestamp;
	}

	/**
	 * @return int
	 */
	public function getStarted(): int {
		return $this->started;
	}

	/**
	 * @return int
	 */
	public function getPosition(): int {
		return $this->position;
	}

	/**
	 * @return int
	 */
	public function getTotal(): int
	{
		return $this->total;
	}

	public function getGuid() : ?string
	{
		return $this->guid;
	}


}
