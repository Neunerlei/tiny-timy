<?php
/**
 * Copyright 2020 Martin Neundorfer (Neunerlei)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Last modified: 2020.03.12 at 16:21
 */

declare(strict_types=1);

namespace Neunerlei\TinyTimy\Tests;


use Neunerlei\TinyTimy\DateTimy;
use Neunerlei\TinyTimy\Tests\Assets\DummyDatetimyResetter;
use PHPUnit\Framework\TestCase;

class DateTimyTest extends TestCase {
	public static function setUpBeforeClass(): void {
		DummyDatetimyResetter::reset();
	}
	
	protected function tearDown(): void {
		DummyDatetimyResetter::reset();
	}
	
	public function _testMakeDataProvider() {
		$ts = time();
		$dateTime = new \DateTime("@" . $ts);
		$dateTimeBerlin = new \DateTime("@" . $ts, new \DateTimeZone("Europe/Berlin"));
		$tomorrowDateTime = new \DateTime("tomorrow midnight");
		$tomorrowDateTimeBerlin = new \DateTime("tomorrow midnight", new \DateTimeZone("Europe/Berlin"));
		$yesterday = new DateTimy("yesterday noon");
		return [
			[$tomorrowDateTime->getTimestamp(), "tomorrow midnight"],
			[$tomorrowDateTimeBerlin->getTimestamp(), "tomorrow midnight", "Europe/Berlin"],
			[$tomorrowDateTimeBerlin->getTimestamp(), "tomorrow midnight", new \DateTimeZone("Europe/Berlin")],
			[$dateTime->getTimestamp(), $dateTime],
			[$dateTimeBerlin->getTimestamp(), $dateTime, "Europe/Berlin"],
			[$dateTimeBerlin->getTimestamp(), $dateTime, new \DateTimeZone("Europe/Berlin")],
			[$ts, $ts],
			[$dateTimeBerlin->getTimestamp(), $ts, "Europe/Berlin"],
			[$dateTimeBerlin->getTimestamp(), $ts, new \DateTimeZone("Europe/Berlin")],
			[$yesterday->getTimestamp(), $yesterday],
			[$yesterday->getTimestamp(), $yesterday->formatDateAndTime(), NULL, DateTimy::FORMAT_TYPE_DATE_AND_TIME],
		];
	}
	
	/**
	 * @param             $a
	 * @param             $b
	 * @param null        $timezone
	 * @param null        $format
	 *
	 * @dataProvider _testMakeDataProvider
	 */
	public function testInstantiation($a, $b, $timezone = NULL, $format = NULL) {
		$i = new DateTimy($b, $timezone, $format);
		$this->assertInstanceOf(DateTimy::class, $i);
		$this->assertEquals($a, $i->getTimestamp());
		$this->assertEquals("UTC", $i->getTimezone()->getName());
	}
	
	public function testStaticCreate() {
		$yesterday = new DateTimy("yesterday noon");
		$i = DateTimy::createFromFormat(DateTimy::FORMAT_TYPE_SQL, $yesterday->formatSql());
		$this->assertEquals($yesterday, $i);
		$i = \DateTimeImmutable::createFromMutable($yesterday);
		$i = DateTimy::createFromImmutable($i);
		$this->assertEquals($yesterday, $i);
	}
	
	public function testTimezoneConfiguration() {
		// Validate that server default is correct
		$this->assertEquals("UTC", (new DateTimy())->getTimezone()->getName());
		$this->assertEquals("UTC", DateTimy::getServerTimezone()->getName());
		
		// Validate that client default is correct
		$defaultClientTimezone = date_default_timezone_get();
		$this->assertEquals($defaultClientTimezone, (new DateTimy())->toClientTimezone()->getTimezone()->getName());
		$this->assertEquals($defaultClientTimezone, DateTimy::getClientTimeZone()->getName());
		
		// Update client timezone
		DateTimy::configureTimezone("Europe/Berlin");
		$this->assertEquals("Europe/Berlin", (new DateTimy())->toClientTimezone()->getTimezone()->getName());
		$this->assertEquals("Europe/Berlin", DateTimy::getClientTimeZone()->getName());
		
		// Validate that server is still the same
		$this->assertEquals("UTC", (new DateTimy())->getTimezone()->getName());
		$this->assertEquals("UTC", DateTimy::getServerTimezone()->getName());
		
		// Update server timezone
		DateTimy::configureTimezone("Europe/London", TRUE);
		$this->assertEquals("Europe/London", (new DateTimy())->getTimezone()->getName());
		$this->assertEquals("Europe/London", DateTimy::getServerTimezone()->getName());
		
		// Validate that client is still the same
		$this->assertEquals("Europe/Berlin", (new DateTimy())->toClientTimezone()->getTimezone()->getName());
		$this->assertEquals("Europe/Berlin", DateTimy::getClientTimeZone()->getName());
		
		// Update server timezone by object
		DateTimy::configureTimezone(new \DateTimeZone("Europe/Berlin"), TRUE);
		$this->assertEquals("Europe/Berlin", (new DateTimy())->getTimezone()->getName());
		$this->assertEquals("Europe/Berlin", DateTimy::getServerTimezone()->getName());
	}
	
	public function testMutableToClientToServer() {
		$i = new DateTimy();
		$iClient = clone $i;
		$iClient->setTimezone(DateTimy::getClientTimeZone());
		$this->assertEquals($i, $i->toServerTimezone());
		$this->assertSame($i, $i->toServerTimezone());
		$this->assertSame($i->toServerTimezone(), $i->toServerTimezone());
		$this->assertEquals($iClient, $i->toClientTimezone());
		$this->assertSame($iClient, $iClient->toClientTimezone());
		$this->assertSame($i->toClientTimezone(), $i->toClientTimezone());
	}
	
	public function testToClientToServer() {
		$ts = time();
		$expectedClient = new DateTimy($ts, "Europe/Moscow");
		$expectedServer = new DateTimy($ts, "Europe/London");
		
		DateTimy::configureTimezone("Europe/Moscow");
		DateTimy::configureTimezone("Europe/London", TRUE);
		
		$i = new DateTimy($ts);
		$this->assertEquals($expectedServer, $i);
		$this->assertEquals($expectedServer->getTimestamp(), $i->getTimestamp());
		$i->toClientTimezone();
		$this->assertEquals($expectedClient, $i);
		$this->assertEquals($expectedClient->getTimestamp(), $i->getTimestamp());
		$i->toServerTimezone();
		$this->assertEquals($expectedServer, $i);
		$this->assertEquals($expectedServer->getTimestamp(), $i->getTimestamp());
		
		// Test if multiple conversions are ignored
		$i->toServerTimezone();
		$i->toServerTimezone();
		$i->toServerTimezone();
		$i->toServerTimezone();
		$this->assertEquals($expectedServer->getTimestamp(), $i->getTimestamp());
	}
	
	public function _testDefaultFormatsDataProvider() {
		return [
			["2020.03.13 00:00", "formatDateAndTime"],
			["2020.03.13", "formatDate"],
			["00:00", "formatTime"],
			["2020-03-13 00:00:00", "formatSql"],
			["2020-03-13", "formatSqlDate"],
			["Fri Mar 13 2020 00:00:00 +0000", "formatJs"],
			["Fri, 13 Mar 2020 00:00:00 UTC", "formatRss"],
		];
	}
	
	/**
	 * @param $a
	 * @param $method
	 *
	 * @dataProvider _testDefaultFormatsDataProvider
	 */
	public function testDefaultFormats($a, $method) {
		$this->assertEquals($a, (new DateTimy(1584057600))->$method());
	}
	
	public function testFormatting() {
		// Reconfigure default formats
		$this->assertEquals("Y.m.d H:i", DateTimy::getFormat(DateTimy::FORMAT_TYPE_DATE_AND_TIME));
		$this->assertEquals("2020.03.13 00:00", (new DateTimy(1584057600))->formatDateAndTime());
		$this->assertEquals("2020.03.13 00:00", (new DateTimy(1584057600))->format(DateTimy::FORMAT_TYPE_DATE_AND_TIME));
		
		DateTimy::configureFormat(DateTimy::FORMAT_TYPE_DATE_AND_TIME, "Y M d H:i:s");
		
		$this->assertEquals("Y M d H:i:s", DateTimy::getFormat(DateTimy::FORMAT_TYPE_DATE_AND_TIME));
		$this->assertEquals("2020 Mar 13 00:00:00", (new DateTimy(1584057600))->formatDateAndTime());
		$this->assertEquals("2020 Mar 13 00:00:00", (new DateTimy(1584057600))->format(DateTimy::FORMAT_TYPE_DATE_AND_TIME));
		
		// Configure custom formats
		DateTimy::configureFormat("customFormat", "H-i-s");
		
		$this->assertEquals("H-i-s", DateTimy::getFormat("customFormat"));
		$this->assertEquals("00-00-00", (new DateTimy(1584057600))->formatCustomFormat());
		$this->assertEquals("00-00-00", (new DateTimy(1584057600))->format("customFormat"));
	}
	
	public function testSetTimezoneByString() {
		$i = new DateTimy();
		$i->setTimezone("Europe/London");
		$this->assertEquals("Europe/London", $i->getTimezone()->getName());
	}
	
	public function testToString() {
		$i = new DateTimy();
		$this->assertEquals($i->formatDateAndTime(), (string)$i);
	}
}