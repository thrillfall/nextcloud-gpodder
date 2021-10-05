<?php
declare(strict_types=1);

namespace tests\Helper\Writer;

class TestWriter implements \OCP\Log\IWriter
{

    /**
     * @inheritDoc
     */
    public function write(string $app, $message, int $level)
    {
    }
}
