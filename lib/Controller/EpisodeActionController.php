<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use DateTime;
use GuzzleHttp\Psr7\Response;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeAction;
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
		return $this->episodeActionSaver->saveEpisodeActions($data, $this->userId);
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
		$episodeActions = $this->episodeActionRepository->findAll($since, $this->userId);
		$untypedEpisodeActionData = [];

		foreach ($episodeActions as $episodeAction) {
			$untypedEpisodeActionData[] = $episodeAction->toArray();
		}

		return new JSONResponse([
			"actions" => $untypedEpisodeActionData,
			"timestamp" => time()
		]);
	}
}
