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

	private SubscriptionChangeSaver $subscriptionChangeSaver;
	private SubscriptionChangeRepository $subscriptionChangeRepository;
	private string $userId;

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
		$this->userId = $UserId ?? '';
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param array $add
	 * @param array $remove
	 * @return JSONResponse
	 */
	public function create(array $add, array $remove): JSONResponse {
		$this->subscriptionChangeSaver->saveSubscriptionChanges($add, $remove, $this->userId);

		return new JSONResponse(["timestamp" => time()]);
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int|null $since
	 * @return JSONResponse
	 */
	public function list(int $since = 0): JSONResponse {
		$sinceDatetime = (new DateTime)->setTimestamp($since);
		return new JSONResponse([
			"add" => $this->extractUrlList($this->subscriptionChangeRepository->findAllSubscribed($sinceDatetime, $this->userId)),
			"remove" => $this->extractUrlList($this->subscriptionChangeRepository->findAllUnSubscribed($sinceDatetime, $this->userId)),
			"timestamp" => time()
		]);
	}

	/**
	 * @param array $allSubscribed
	 * @return mixed
	 */
	private function extractUrlList(array $allSubscribed): array {
		return array_map(static function (SubscriptionChangeEntity $subscription) {
			return $subscription->getUrl();
		}, $allSubscribed);
	}
}
