<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

class PodcastActionCounts {
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

	public function toArray(): array {
		return
		[
			'delete' => $this->delete,
			'download' => $this->download,
			'flattr' => $this->flattr,
			'new' => $this->new,
			'play' => $this->play,
		];
	}
}
