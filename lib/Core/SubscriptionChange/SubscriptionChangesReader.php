<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\SubscriptionChange;

class SubscriptionChangesReader {

	/**
	 * @param string $raw
	 * @param bool $subscribed
	 * @return array|SubscriptionChange[]
	 */
	public function fromString(string $raw, bool $subscribed): array {
		$urls = str_replace(["[", "]", " "], "", $raw);
		$urlList = explode(",", $urls);

		if ($urlList[0] === "") {
			return [];
		}
		$subscriptionChanges = [];
		foreach ($urlList as $url) {
			$subscriptionChanges[] = new SubscriptionChange($url, $subscribed);
		}

		return $subscriptionChanges;
	}

	/**
	 * @param array $raw
	 * @param bool $subscribed
	 * @return array|SubscriptionChange[]
	 */
	public function fromArray(array $raw, bool $subscribed): array {
		$subscriptionChanges = [];
		foreach ($raw as $url) {
			$subscriptionChanges[] = $this->fromString($url, $subscribed)[0];
		}
		return $subscriptionChanges;
	}
}
