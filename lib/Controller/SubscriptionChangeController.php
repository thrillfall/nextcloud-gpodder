<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use DateTime;
use OCA\GPodderSync\Core\SubscriptionChange\SubscriptionChangeSaver;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeEntity;
use OCA\GPodderSync\Db\SubscriptionChange\SubscriptionChangeRepository;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class SubscriptionChangeController extends Controller {

	private string $AppName;
	/**
	 * @var SubscriptionChangeSaver
	 */
	private SubscriptionChangeSaver $subscriptionChangeSaver;
	/**
	 * @var SubscriptionChangeRepository
	 */
	private SubscriptionChangeRepository $subscriptionChangeRepository;
	private $userId;

	public function __construct(
		string $AppName,
		IRequest $request,
		$UserId,
		SubscriptionChangeSaver $subscriptionChangeSaver,
		SubscriptionChangeRepository $subscriptionChangeRepository

	) {
		parent::__construct($AppName, $request);
		$this->subscriptionChangeSaver = $subscriptionChangeSaver;
		$this->subscriptionChangeRepository = $subscriptionChangeRepository;
		$this->userId = $UserId;
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return void
	 */
	public function create($add, $remove) {
		return $this->subscriptionChangeSaver->saveSubscriptionChanges($add, $remove, $this->userId);
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $since
	 * @return JSONResponse
	 * @throws \Exception
	 */
	public function list(int $since = null): JSONResponse {
		$sinceDatetime = $this->createDateTimeFromTimestamp($since);
		return new JSONResponse([
			"add" => $this->extractUrlList($this->subscriptionChangeRepository->findAllSubscribed($sinceDatetime, $this->userId)),
			"remove" => $this->extractUrlList($this->subscriptionChangeRepository->findAllUnSubscribed($sinceDatetime, $this->userId)),
			"timestamp" => time()
		]);
	}

	/**
	 * @param int|null $since
	 *
	 * @return DateTime
	 */
	private function createDateTimeFromTimestamp(?int $since): DateTime {
		return ($since !== null)
			? (new \DateTime)->setTimestamp($since)
			: (new \DateTime('-1 week'));
	}

	/**
	 * @param array $allSubscribed
	 *
	 * @return mixed
	 */
	private function extractUrlList(array $allSubscribed): array {
		return array_map(function (SubscriptionChangeEntity $subscription) {
			return $subscription->getUrl();
		}, $allSubscribed);
	}
}
