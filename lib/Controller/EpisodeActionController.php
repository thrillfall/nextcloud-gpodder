<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Controller;

use DateTime;
use GuzzleHttp\Psr7\Response;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionReader;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\DB\Exception;
use OCP\IRequest;

class EpisodeActionController extends Controller {

	/**
	 * @var EpisodeActionRepository
	 */
	private EpisodeActionRepository $episodeActionRepository;
	/**
	 * @var EpisodeActionWriter
	 */
	private EpisodeActionWriter $episodeActionWriter;
	/**
	 * @var EpisodeActionReader
	 */
	private EpisodeActionReader $episodeActionReader;
	private $userId;

	public function __construct(
		string $AppName,
		IRequest $request,
		$UserId,
		EpisodeActionRepository $episodeActionRepository,
		EpisodeActionWriter $episodeActionWriter,
		EpisodeActionReader $episodeActionReader
	) {
		parent::__construct($AppName, $request);
		$this->episodeActionRepository = $episodeActionRepository;
		$this->episodeActionWriter = $episodeActionWriter;
		$this->episodeActionReader = $episodeActionReader;
		$this->userId = $UserId;
	}

	/**
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return Response
	 */
	public function create($data) {
		$episodeAction = $this->episodeActionReader->fromString($data);
		$episodeActionEntity = new EpisodeActionEntity();
		$episodeActionEntity->setPodcast($episodeAction->getPodcast());
		$episodeActionEntity->setEpisode($episodeAction->getEpisode());
		$episodeActionEntity->setAction($episodeAction->getAction());
		$episodeActionEntity->setPosition($episodeAction->getPosition());
		$episodeActionEntity->setStarted($episodeAction->getStarted());
		$episodeActionEntity->setTotal($episodeAction->getTotal());
		$episodeActionEntity->setTimestamp($episodeAction->getTimestamp());
		$episodeActionEntity->setUserId($this->userId);

		try {
			return $this->episodeActionWriter->save($episodeActionEntity);
		} catch (\Exception $exception) {
			if ($exception->getReason() === Exception::REASON_UNIQUE_CONSTRAINT_VIOLATION) {
				$idEpisodeActionEntityToUpdate = $this->episodeActionRepository->findByEpisode($episodeAction->getEpisode(), $this->userId)->getId();
				$episodeActionEntity->setId($idEpisodeActionEntityToUpdate);
				return $this->episodeActionWriter->update($episodeActionEntity);
			}
		}
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
		return ($since)
			? (new \DateTime)->setTimestamp($since)
			: (new \DateTime('-1 week'));
	}
}
