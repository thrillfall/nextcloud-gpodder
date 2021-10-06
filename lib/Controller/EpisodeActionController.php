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

	protected $request;

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
		$this->request = $request;
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function create(): JSONResponse {

		$episodeActionsArray = $this->filterEpisodesFromRequestParams($this->request->getParams());
		
		$this->episodeActionSaver->saveEpisodeActions($episodeActionsArray, $this->userId);

		return new JSONResponse(["timestamp" => time()]);
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

	/**
	 * @param array $requestParams
	 * 
	 * @return array $episodeActionsArray
	 */
	public function filterEpisodesFromRequestParams(array $data): array {
		return array_filter($data, "is_numeric", ARRAY_FILTER_USE_KEY);
	}
}
