<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Migration;

use OCP\IDBConnection;
use OCP\Migration\IOutput;
use Safe\DateTime;

class TimestampMigration implements \OCP\Migration\IRepairStep
{
	private IDBConnection $db;

	public function __construct(IDBConnection $db)
	{
		$this->db = $db;
	}

	/**
     * @inheritDoc
     */
    public function getName() : string
    {
        return "migrate timestamp values to integer to store unix epoch";
    }

    /**
     * @inheritDoc
     */
    public function run(IOutput $output)
    {
		$queryTimestamps = 'SELECT id, timestamp FROM `*PREFIX*gpodder_episode_action` WHERE timestamp_epoch = 0';
		$timestamps = $this->db->executeQuery($queryTimestamps)->fetchAll();

		foreach ($timestamps as $timestamp) {
			$timestampEpoch = (new DateTime($timestamp["timestamp"]))->format("U");
			$sql = 'UPDATE `*PREFIX*gpodder_episode_action` '
				. 'SET `timestamp_epoch` = ' . $timestampEpoch
				. 'WHERE `timestamp_epoch` = 0';

			$result = $this->db->executeUpdate($sql);

		}

		return $result;
    }

}
