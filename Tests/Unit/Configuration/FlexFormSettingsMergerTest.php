<?php

namespace Subugoe\Find\Tests\Unit\Configuration;

use Subugoe\Find\Configuration\FlexFormSettingsMerger;
use TYPO3\CMS\Core\Tests\BaseTestCase;

class FlexFormSettingsMergerTest extends BaseTestCase
{
    /**
     * @var  FlexFormSettingsMerger
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = new FlexFormSettingsMerger();
    }

    /**
     * @dataProvider getSampleValues
     *
     * @param array $original
     * @param array $overlay
     * @param array $expected
     */
    public function testOverlaysSimpleValueWithFlexformKey(array $original, array $overlay, array $expected)
    {
        $this->fixture->initialize($original);
        $result = $this->fixture->process($overlay);

        $this->assertEquals($expected, $result);
    }

    private function getSampleValues()
    {
        return [
            [[], [], []],
        ];
    }
}
