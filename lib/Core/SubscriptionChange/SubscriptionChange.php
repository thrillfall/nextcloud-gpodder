<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\SubscriptionChange;

class SubscriptionChange {
	private string $url;
	private bool $isSubscribed;

	public function __construct(
		string $url,
		bool $isSubscribed
	) {
		$this->url = $url;
		$this->isSubscribed = $isSubscribed;
	}

	/**
	 * @return bool
	 */
	public function isSubscribed(): bool {
		return $this->isSubscribed;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string {
		return $this->url;
	}

	public function __toString() : String {
		return $this->url;
	}
}
