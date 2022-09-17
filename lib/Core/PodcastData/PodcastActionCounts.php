<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

use JsonSerializable;

class PodcastActionCounts implements JsonSerializable {
	private int $delete = 0;
	private int $download = 0;
	private int $flattr = 0;
	private int $new = 0;
	private int $play = 0;

	/**
	 * @param string $action
	 */
	public function incrementAction(string $action): void {
		switch ($action) {
			case 'delete': $this->delete++; break;
			case 'download': $this->download++; break;
			case 'flattr': $this->flattr++; break;
			case 'new': $this->new++; break;
			case 'play': $this->play++; break;
		}
	}

	/**
	 * @return array<string,int>
	 */
	public function toArray(): array {
		return [
			'delete' => $this->delete,
			'download' => $this->download,
			'flattr' => $this->flattr,
			'new' => $this->new,
			'play' => $this->play,
		];
	}

	/**
	 * @return array<string,int>
	 */
	public function jsonSerialize(): mixed {
		return $this->toArray();
	}
}
