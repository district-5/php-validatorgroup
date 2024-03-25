<?php

namespace District5Tests\ValidatorGroupTests;

use District5\ValidatorGroup\Handler\JSON;
use District5Tests\ValidatorGroupMocks\SimpleGroupMock;
use PHPUnit\Framework\TestCase;

/**
 * Class GroupWithJSONTest
 * @package District5\ValidatorGroupTests
 */
class GroupWithJSONTest extends TestCase
{
    public function testValidWithJSONDecode()
    {
        $obj = [
            'testInt01' => 3,
            'testString01' => "District5"
        ];

        $jsonStr = json_encode($obj);
        $handler = new JSON($jsonStr);

        $group = new SimpleGroupMock();

        $this->assertTrue($group->isValid($handler));
    }

    public function testValidWithoutJSONDecode()
    {
        $obj = [
            'testInt01' => 3,
            'testString01' => "District5"
        ];

        $handler = new JSON($obj);

        $group = new SimpleGroupMock();

        $this->assertTrue($group->isValid($handler));
    }

    public function testInvalidWithJSONDecode()
    {
        $obj = [
            'testInt01' => 7,
            'testString01' => "District5"
        ];

        $jsonStr = json_encode($obj);
        $handler = new JSON($jsonStr);

        $group = new SimpleGroupMock();

        $this->assertFalse($group->isValid($handler));
    }

    public function testInvalidWithoutJSONDecode()
    {
        $obj = [
            'testInt01' => 7,
            'testString01' => "District5"
        ];

        $handler = new JSON($obj);

        $group = new SimpleGroupMock();

        $this->assertFalse($group->isValid($handler));
    }
}
