<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GuzzleHttp\Psr7\Response;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionReader;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class VersionController extends Controller {

	public function __construct(
		string $AppName,
		IRequest $request,
		$UserId
	) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return Response
	 */
	public function index() {
		return new JSONResponse(["version" => "0.1"]);
	}


}
