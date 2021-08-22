<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use DateTime;
use GuzzleHttp\Psr7\Response;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionSaver;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class EpisodeActionController extends Controller {

	/**
	 * @var EpisodeActionRepository
	 */
	private EpisodeActionRepository $episodeActionRepository;

	private $userId;
	private EpisodeActionSaver $episodeActionSaver;

	public function __construct(
		string $AppName,
		IRequest $request,
		$UserId,
		EpisodeActionRepository $episodeActionRepository,
		EpisodeActionSaver $episodeActionSaver
	) {
		parent::__construct($AppName, $request);
		$this->episodeActionRepository = $episodeActionRepository;
		$this->userId = $UserId;
		$this->episodeActionSaver = $episodeActionSaver;
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return Response
	 */
	public function create($data) {
		return $this->episodeActionSaver->saveEpisodeAction($data, $this->userId);
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $since
	 * @return JSONResponse
	 */
	public function list(int $since): JSONResponse {
		$sinceDatetime = $this->createDateTimeFromTimestamp($since);
		return new JSONResponse([
			"actions" => $this->episodeActionRepository->findAll($sinceDatetime, $this->userId),
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


}
