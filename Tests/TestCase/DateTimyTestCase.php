<?php
declare(strict_types=1);


namespace Neunerlei\TinyTimy\Tests\TestCase;


use Neunerlei\TinyTimy\DateTimy;
use PHPUnit\Framework\TestCase;

class DateTimyTestCase extends TestCase {
    /**
     * @var
     */
	private static $formatBackup;

    protected function setUp(): void
    {
        self::$formatBackup = (new class extends DateTimy{
           public function run(): array
           {
              return DateTimy::$formats;
           }
        })->run();
    }

    protected function tearDown(): void
    {
        (new class extends DateTimy{
            public function run($formats)
            {
                DateTimy::$clientTimezone = NULL;
                DateTimy::$serverTimezone = 'UTC';
                DateTimy::$formats = $formats;
            }
        })->run(self::$formatBackup);
    }
}
