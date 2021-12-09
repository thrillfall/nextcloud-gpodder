<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\SubscriptionChange;

class SubscriptionChangesReader {

	/**
	 * @param array $urls
	 * @param bool $subscribed
	 *
	 * @return SubscriptionChange[]
	 */
	public static function mapToSubscriptionsChanges(array $urls, bool $subscribed): array {
		$subscriptionChanges = [];
        $urls = array_filter($urls, function(string $url) {return filter_var($url, FILTER_VALIDATE_URL) !== false; });
		foreach ($urls as $url) {
			$subscriptionChanges[] = new SubscriptionChange($url, $subscribed);
		}

		return $subscriptionChanges;
	}

}
