<?php

declare(strict_types=1);

namespace Wowstack\Dbc\Tests;

use PHPUnit\Framework\TestCase;
use Wowstack\Dbc\DBC;
use Wowstack\Dbc\DBCIterator;
use Wowstack\Dbc\DBCRecord;
use Wowstack\Dbc\Mapping;

/**
 * Verifies iteration over DBC records works.
 */
class DBCIteratorTest extends TestCase
{
    /**
     * Checks that DBC provide a valid iterator.
     *
     * @dataProvider constructProvider
     *
     * @param string $yaml
     * @param string $dbc
     */
    public function testItConstructs(string $yaml, string $dbc)
    {
        $DBC = new DBC($dbc, Mapping::fromYAML($yaml));
        $DBCIterator = $DBC->getIterator();
        $this->assertInstanceOf(DBCIterator::class, $DBCIterator);
    }

    /**
     * @dataProvider iterateProvider
     *
     * @param string $yaml
     * @param string $dbc
     */
    public function testItIterates(string $yaml, string $dbc)
    {
        $DBC = new DBC($dbc, Mapping::fromYAML($yaml));
        $DBCIterator = $DBC->getIterator();

        $this->assertEquals(0, $DBCIterator->key());

        $targetPosition = rand(0, $DBC->getRecordCount());
        $DBCIterator->seek($targetPosition);
        $this->assertEquals($targetPosition, $DBCIterator->key());

        $DBCIterator->prev();
        $this->assertEquals($targetPosition - 1, $DBCIterator->key());

        $DBCIterator->rewind();
        $this->assertEquals(0, $DBCIterator->key());

        $DBCIterator->next();
        $this->assertEquals(1, $DBCIterator->key());
        $this->assertTrue($DBCIterator->valid());
        $this->assertInstanceOf(DBCRecord::class, $DBCIterator->current());
    }

    /**
     * @return array
     */
    public function constructProvider(): array
    {
        return [
            'AreaPOI mapping - patch 1.12.1' => [dirname(__FILE__).'/data/maps/AreaPOI.yaml', dirname(__FILE__).'/data/AreaPOI.dbc'],
        ];
    }

    /**
     * @return array
     */
    public function iterateProvider(): array
    {
        return [
            'AreaPOI mapping - patch 1.12.1' => [dirname(__FILE__).'/data/maps/AreaPOI.yaml', dirname(__FILE__).'/data/AreaPOI.dbc'],
        ];
    }
}
