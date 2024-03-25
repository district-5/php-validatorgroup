<?php

namespace District5Tests\ValidatorGroupTests;

use District5\ValidatorGroup\Handler\JSON;
use District5Tests\ValidatorGroupMocks\SimpleGroupMock;
use PHPUnit\Framework\TestCase;

/**
 * Class GroupWithJSONTest
 * @package District5\ValidatorGroupTests
 */
class GroupTest extends TestCase
{
    public function testFilterUnmodified()
    {
        $obj = [
            'testInt01' => 3,
            'testString01' => "District5"
        ];

        $handler = new JSON($obj);

        $group = new SimpleGroupMock();

        $this->assertTrue($group->isValid($handler));
        $this->assertEquals("District5", $group->getValue('testString01'));
    }

    public function testFilterModified()
    {
        $obj = [
            'testInt01' => 3,
            'testString01' => "District5 "
        ];

        $handler = new JSON($obj);

        $group = new SimpleGroupMock();

        $this->assertTrue($group->isValid($handler));
        $this->assertEquals("District5", $group->getValue('testString01'));
    }
}
