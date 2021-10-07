<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\EpisodeAction;

use OCA\GPodderSync\Core\EpisodeAction\EpisodeAction;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionReader;
use OCA\GPodderSync\Core\EpisodeAction\EpisodeActionSaver;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionEntity;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionRepository;
use OCA\GPodderSync\Db\EpisodeAction\EpisodeActionWriter;
use OCP\DB\Exception;
use Test\TestCase;

class EpisodeActionSaverTest extends TestCase {

	public function testSaveEpisodeActions(): void {
		$episodeAction1 = new EpisodeAction('podcast1', 'episode1', 'PLAY', '2021-10-07T13:27:14', 15, 120, 500, 'podcast1guid', null);
		$episodeAction2 = new EpisodeAction('podcast1', 'episode2', 'PLAY', '2021-10-07T13:27:14', -1, -1, -1, 'podcast1guid', null);
		$repository = $this->createMock(EpisodeActionRepository::class);
		$writer = $this->createMock(EpisodeActionWriter::class);
		$writer->expects($this->exactly(2))->method('save')->withConsecutive(
			[$this->isInstanceOf(EpisodeActionEntity::class)], [$this->isInstanceOf(EpisodeActionEntity::class)]
		);
		$reader = $this->createMock(EpisodeActionReader::class);
		$reader->method('fromArray')->willReturn([$episodeAction1, $episodeAction2]);
		$saver = new EpisodeActionSaver($repository, $writer, $reader);
		$actions = [];
		$userId = 'paul';
		$result = $saver->saveEpisodeActions($actions, $userId);
		$this->assertCount(2, $result);
		$this->assertInstanceOf(EpisodeActionEntity::class, $result[0]);
		$this->assertInstanceOf(EpisodeActionEntity::class, $result[1]);
	}

	public function testUpdateEpisodeActions(): void {
		$userId = 'paul';
		$updateGuid = 'podcast2guid';
		$episodeAction1 = new EpisodeAction('podcast1', 'episode1', 'PLAY', '2021-10-07T13:27:14', 15, 120, 500, 'podcast1guid', null);
		$episodeAction2 = new EpisodeAction('podcast1', 'episode2', 'PLAY', '2021-10-07T13:27:14', 120, 200, 500, $updateGuid, null);
		$existingEpisodeAction = new EpisodeAction('podcast1', 'episode2', 'PLAY', '2021-10-07T13:27:14', 120, 200, 500, $updateGuid, 1234);
		$repository = $this->createMock(EpisodeActionRepository::class);
		$repository->expects($this->once())->method('findByEpisodeIdentifier')->with($updateGuid, $userId)->willReturn($existingEpisodeAction);
		$writer = $this->createMock(EpisodeActionWriter::class);
		$mockException = $this->createMock(Exception::class);
		$mockException->method('getReason')->willReturn(Exception::REASON_UNIQUE_CONSTRAINT_VIOLATION);
		$writer->method('save')
			->willReturnOnConsecutiveCalls(
				$this->createMock(EpisodeActionEntity::class),
				$this->throwException($mockException)
			);
		$writer->expects($this->once())->method('update')->with($this->isInstanceOf(EpisodeActionEntity::class))->willReturn($this->createMock(EpisodeActionEntity::class));
		$reader = $this->createMock(EpisodeActionReader::class);
		$reader->method('fromArray')->willReturn([$episodeAction1, $episodeAction2]);
		$saver = new EpisodeActionSaver($repository, $writer, $reader);
		$actions = [];
		$result = $saver->saveEpisodeActions($actions, $userId);
		$this->assertCount(2, $result);
		$this->assertInstanceOf(EpisodeActionEntity::class, $result[0]);
		$this->assertInstanceOf(EpisodeActionEntity::class, $result[1]);
	}

	public function testUpdateEpisodeActionsFailure(): void {
		$userId = 'paul';
		$updateGuid = 'podcast2guid';
		$episodeAction2 = new EpisodeAction('podcast1', 'episode2', 'PLAY', '2021-10-07T13:27:14', 120, 200, 500, $updateGuid, null);
		$existingEpisodeAction = new EpisodeAction('podcast1', 'episode2', 'PLAY', '2021-10-07T13:27:14', 120, 200, 500, $updateGuid, 1234);
		$repository = $this->createMock(EpisodeActionRepository::class);
		$repository->expects($this->once())->method('findByEpisodeIdentifier')->with($updateGuid, $userId)->willReturn($existingEpisodeAction);
		$writer = $this->createMock(EpisodeActionWriter::class);
		$mockException = $this->createMock(Exception::class);
		$mockException->method('getReason')->willReturn(Exception::REASON_UNIQUE_CONSTRAINT_VIOLATION);
		$writer->method('save')->willThrowException($mockException);
		$writer->expects($this->once())->method('update')->with($this->isInstanceOf(EpisodeActionEntity::class))->willThrowException($mockException);
		$reader = $this->createMock(EpisodeActionReader::class);
		$reader->method('fromArray')->willReturn([$episodeAction2]);
		$saver = new EpisodeActionSaver($repository, $writer, $reader);
		$actions = [];
		$result = $saver->saveEpisodeActions($actions, $userId);
		$this->assertCount(0, $result);
	}
}
