<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\EpisodeAction;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class EpisodeActionEntity extends Entity implements JsonSerializable {

	protected $podcast;
	protected $episode;
	protected $action;
	protected $position;
	protected $started;
	protected $total;
	protected $timestamp;
	protected $timestampEpoch;
	protected $guid;
	protected $userId;

	public function __construct() {
		$this->addType('id','integer');
	}

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'podcast' => $this->podcast,
			'episode' => $this->episode,
			'guid' => $this->guid,
			'action' => $this->action,
			'position' => $this->position,
			'started' => $this->started,
			'total' => $this->total,
			'timestamp' => $this->timestampEpoch,
		];
	}

	public function getTimestampEpoch() : int
	{
		return (int) $this->timestampEpoch;
	}

	public function getStarted() : int {
		return (int) $this->started;
	}
}
