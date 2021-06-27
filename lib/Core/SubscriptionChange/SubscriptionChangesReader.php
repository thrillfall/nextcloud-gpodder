<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\SubscriptionChange;

class SubscriptionChangesReader {

	/**
	 * @param string $raw
	 *
	 * @return array|SubscriptionChange[]
	 */
	public function fromString(string $raw, bool $subscribed):? array {
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

}
