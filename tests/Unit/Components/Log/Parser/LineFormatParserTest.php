<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Components\Log\Parser;

use Shopware\Components\Log\Parser\LineFormatParser;

class LineFormatParserTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleLine()
    {
        // Simple line without 'context' nor 'extra'
        $parts = [
            'timestamp' => new \DateTime('2000-01-02 03:04:05'),
            'level' => 'DEBUG',
            'message' => 'This is a test!',
            'context' => [],
            'extra' => []
        ];
        $parser = new LineFormatParser();
        $result = $parser->parseLine($this->createLogLine($parts));

        $this->assertParsedData($parts, $result);
        $this->assertEmpty($result['context']);
        $this->assertEmpty($result['extra']);
    }

    public function testLineWithoutMessage()
    {
        // Line with empty message, without 'context' nor 'extra'
        $parts = [
            'timestamp' => new \DateTime('2000-01-02 03:04:05'),
            'level' => 'DEBUG',
            'message' => '',
            'context' => [],
            'extra' => []
        ];
        $parser = new LineFormatParser();
        $result = $parser->parseLine($this->createLogLine($parts));

        $this->assertParsedData($parts, $result);
        $this->assertEmpty($result['context']);
        $this->assertEmpty($result['extra']);
    }

    public function testLineWithExtra()
    {
        // Simple line without 'context'
        $parts = [
            'timestamp' => new \DateTime('2000-01-02 03:04:05'),
            'level' => 'DEBUG',
            'message' => 'This is a test!',
            'context' => [],
            'extra' => [
                'uid' => 'abc123',
                'some' => [
                    'things',
                    'things'
                ]
            ]
        ];
        $parser = new LineFormatParser();
        $result = $parser->parseLine($this->createLogLine($parts));

        $this->assertParsedData($parts, $result);
        // Context
        $this->assertEmpty($result['context']);
        // Extra
        $this->assertArrayHasKey('uid', $result['extra']);
        $this->assertArrayHasKey('some', $result['extra']);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($parts['extra']['uid'], $result['id']);
    }

    public function testLineWithoutMessageWithExtra()
    {
        // Line with empty message, without 'context'
        $parts = [
            'timestamp' => new \DateTime('2000-01-02 03:04:05'),
            'level' => 'DEBUG',
            'message' => '',
            'context' => [],
            'extra' => [
                'uid' => 'abc123',
                'some' => [
                    'things',
                    'things'
                ]
            ]
        ];
        $parser = new LineFormatParser();
        $result = $parser->parseLine($this->createLogLine($parts));

        $this->assertParsedData($parts, $result);
        // Context
        $this->assertEmpty($result['context']);
        // Extra
        $this->assertArrayHasKey('uid', $result['extra']);
        $this->assertArrayHasKey('some', $result['extra']);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($parts['extra']['uid'], $result['id']);
    }

    public function testLineWithContext()
    {
        // Simple line without 'extra'
        $parts = [
            'timestamp' => new \DateTime('2000-01-02 03:04:05'),
            'level' => 'DEBUG',
            'message' => 'This is a test!',
            'context' => [
                'plugin' => 'APluginName',
                'params' => [
                    'first' => 1,
                    'second' => 2
                ]
            ],
            'extra' => []
        ];
        $parser = new LineFormatParser();
        $result = $parser->parseLine($this->createLogLine($parts));

        $this->assertParsedData($parts, $result);
        // Context
        $this->assertArrayHasKey('plugin', $result['context']);
        $this->assertArrayHasKey('params', $result['context']);
        $this->assertInternalType('array', $result['context']['params']);
        // Extra
        $this->assertEmpty($result['extra']);
    }

    public function testLineWithoutMessageWithContext()
    {
        // Simple line without 'extra'
        $parts = [
            'timestamp' => new \DateTime('2000-01-02 03:04:05'),
            'level' => 'DEBUG',
            'message' => '',
            'context' => [
                'plugin' => 'APluginName',
                'params' => [
                    'first' => 1,
                    'second' => 2
                ]
            ],
            'extra' => []
        ];
        $parser = new LineFormatParser();
        $result = $parser->parseLine($this->createLogLine($parts));

        $this->assertParsedData($parts, $result);
        // Context
        $this->assertArrayHasKey('plugin', $result['context']);
        $this->assertArrayHasKey('params', $result['context']);
        $this->assertInternalType('array', $result['context']['params']);
        // Extra
        $this->assertEmpty($result['extra']);
    }

    public function testLineWithContextContainingException()
    {
        // Simple line without 'extra'
        $parts = [
            'timestamp' => new \DateTime('2000-01-02 03:04:05'),
            'level' => 'DEBUG',
            'message' => 'This is a test!',
            'context' => [
                'plugin' => 'APluginName',
                'exception' => [
                    'message' => 'The exception message'
                ]
            ],
            'extra' => []
        ];
        $parser = new LineFormatParser();
        $result = $parser->parseLine($this->createLogLine($parts));

        $this->assertParsedData($parts, $result);
        // Context
        $this->assertArrayHasKey('plugin', $result['context']);
        $this->assertFalse(isset($result['context']['exception']));
        $this->assertArrayHasKey('exception', $result);
        $this->assertInternalType('array', $result['exception']);
        $this->assertEquals($parts['context']['exception']['message'], $result['exception']['message']);
        // Extra
        $this->assertEmpty($result['extra']);
    }

    public function testComplexLine()
    {
        // Simple line without 'extra'
        $parts = [
            'timestamp' => new \DateTime('2000-01-02 03:04:05'),
            'level' => 'DEBUG',
            'message' => 'This is a test!',
            'context' => [
                'plugin' => 'APluginName',
                'exception' => [
                    'message' => 'The exception message'
                ]
            ],
            'extra' => [
                'uid' => 'abc123',
                'some' => [
                    'things',
                    'things'
                ]
            ]
        ];
        $parser = new LineFormatParser();
        $result = $parser->parseLine($this->createLogLine($parts));

        $this->assertParsedData($parts, $result);
        // Context
        $this->assertArrayHasKey('plugin', $result['context']);
        $this->assertFalse(isset($result['context']['exception']));
        $this->assertArrayHasKey('exception', $result);
        $this->assertInternalType('array', $result['exception']);
        $this->assertEquals($parts['context']['exception']['message'], $result['exception']['message']);
        // Extra
        $this->assertArrayHasKey('uid', $result['extra']);
        $this->assertArrayHasKey('some', $result['extra']);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($parts['extra']['uid'], $result['id']);
    }

    /**
     * @param array $expected
     * @param array $actual
     */
    private function assertParsedData(array $expected, array $actual)
    {
        $this->assertEquals($expected['timestamp'], $actual['timestamp']);
        $this->assertEquals($expected['level'], $actual['level']);
        $this->assertEquals($expected['message'], $actual['message']);
        $this->assertInternalType('array', $actual['context']);
        $this->assertInternalType('array', $actual['extra']);
    }

    /**
     * @param array $parts
     * @return string
     */
    private function createLogLine(array $parts)
    {
        return sprintf(
            '[%s] core.%s: %s %s %s',
            $parts['timestamp']->format('Y-m-d H:i:s'),
            $parts['level'],
            $parts['message'],
            json_encode($parts['context']),
            json_encode($parts['extra'])
        );
    }
}
