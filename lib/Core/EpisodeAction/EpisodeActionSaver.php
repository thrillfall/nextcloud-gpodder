<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\EpisodeAction;

use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCP\DB\Exception;

class EpisodeActionSaver
{

	private EpisodeActionRepository $episodeActionRepository;
	private EpisodeActionWriter $episodeActionWriter;
	private EpisodeActionReader $episodeActionReader;

	public function __construct(
		EpisodeActionRepository $episodeActionRepository,
		EpisodeActionWriter $episodeActionWriter,
		EpisodeActionReader $episodeActionReader
	)
	{
		$this->episodeActionRepository = $episodeActionRepository;
		$this->episodeActionWriter = $episodeActionWriter;
		$this->episodeActionReader = $episodeActionReader;
	}

	/**
	 * @param $data
	 *
	 * @return EpisodeActionEntity
	 */
	public function saveEpisodeAction($data, string $userId): EpisodeActionEntity
	{
		$episodeAction = $this->episodeActionReader->fromString($data);
		$episodeActionEntity = new EpisodeActionEntity();
		$episodeActionEntity->setPodcast($episodeAction->getPodcast());
		$episodeActionEntity->setEpisode($episodeAction->getEpisode());
		$episodeActionEntity->setAction($episodeAction->getAction());
		$episodeActionEntity->setPosition($episodeAction->getPosition());
		$episodeActionEntity->setStarted($episodeAction->getStarted());
		$episodeActionEntity->setTotal($episodeAction->getTotal());
		$episodeActionEntity->setTimestamp($this->convertTimestampToDbDateTimeString($episodeAction->getTimestamp()));
		$episodeActionEntity->setUserId($userId);

		try {
			return $this->episodeActionWriter->save($episodeActionEntity);
		} catch (UniqueConstraintViolationException $uniqueConstraintViolationException) {
			return $this->updateEpisodeAction($episodeAction, $episodeActionEntity, $userId);
		} catch (Exception $exception) {
			if ($exception->getReason() === Exception::REASON_UNIQUE_CONSTRAINT_VIOLATION) {
				return $this->updateEpisodeAction($episodeAction, $episodeActionEntity, $userId);
			}
		}
	}

	/**
	 * @param string $timestamp
	 *
	 * @return string
	 */
	private function convertTimestampToDbDateTimeString(string $timestamp): string
	{
		return \DateTime::createFromFormat('D F d H:i:s T Y', $timestamp)
			->setTimezone(new DateTimeZone('UTC'))
			->format("Y-m-d\TH:i:s");
	}

	/**
	 * @param EpisodeAction $episodeAction
	 * @param EpisodeActionEntity $episodeActionEntity
	 *
	 * @return EpisodeActionEntity
	 */
	private function updateEpisodeAction(
		EpisodeAction $episodeAction,
		EpisodeActionEntity $episodeActionEntity,
		string $userId
	): EpisodeActionEntity
	{
		$idEpisodeActionEntityToUpdate = $this->episodeActionRepository->findByEpisode(
			$episodeAction->getEpisode(),
			$userId
		)->getId();
		$episodeActionEntity->setId($idEpisodeActionEntityToUpdate);

		return $this->episodeActionWriter->update($episodeActionEntity);
	}
}
