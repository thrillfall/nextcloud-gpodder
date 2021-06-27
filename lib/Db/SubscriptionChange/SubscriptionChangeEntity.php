<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Db\SubscriptionChange;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class SubscriptionChangeEntity extends Entity implements JsonSerializable {

	protected $url;
	protected $subscribed;
	protected $updated;
	protected $userId;

	public function __construct() {
		$this->addType('id','integer');
		$this->addType('subscribed','boolean');
	}

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'url' => $this->url,
			'subscribed' => $this->subscribed,
			'updated' => $this->updated,
		];
	}
}
