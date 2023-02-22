<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

use DateTime;
use JsonSerializable;
use SimpleXMLElement;

class EpisodeActionData implements JsonSerializable {
	private ?string $podcastUrl;
	private ?string $episodeUrl;
	private ?string $action;
	private int $position;
	private int $started;
	private int $total;
	private int $timestampEpoch;

	public function __construct(
		?string $podcastUrl,
		?string $episodeUrl,
		?string $action,
		int $position = 0,
		int $started = 0,
		int $total = 0
	) {
		$this->podcastUrl = $podcastUrl;
		$this->episodeUrl = $episodeUrl;
		$this->action = $action;
		$this->position = $position;
		$this->started = $started;
		$this->total = $total;
	}

	/**
	 * @return string|null
	 */
	public function getEpisodeUrl(): ?string {
		return $this->episodeUrl;
	}

	/**
	 * @return string
	 */
	public function __toString() : string {
		return $this->episodeUrl ?? '/no episodeUrl/';
	}

	/**
	 * @return array<string,mixed>
	 */
	public function toArray(): array {
		return
		[
			'podcastUrl' => $this->podcastUrl,
			'episodeUrl' => $this->episodeUrl,
			'action' => $this->action,
			'position' => $this->position,
			'started' => $this->started,
			'total' => $this->total,
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	public function jsonSerialize(): array {
		return $this->toArray();
	}

	/**
	 * @return EpisodeActionData
	 */
	public static function fromArray(array $data): EpisodeActionData {
		return new EpisodeActionData(
			$data['podcastUrl'],
			$data['episodeUrl'],
			$data['action'],
			$data['position'],
			$data['started'],
			$data['episodeUrl'],
			$data['total'],
		);
	}

    /**
     * @return string|null
     */
    public function getPodcastUrl(): ?string
    {
        return $this->podcastUrl;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getStarted(): int
    {
        return $this->started;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}

