<?php

namespace CodeceptionPact\Tests\unit\Extension;

use Amp\Process\ProcessException;
use Codeception\Event\SuiteEvent;
use Codeception\Suite;
use Codeception\Test\Unit;
use CodeceptionPact\Extension\PhpPact;
use PhpPact\Standalone\Exception\HealthCheckFailedException;
use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfig;
use PHPUnit\Framework\TestResult;
use ReflectionObject;

/**
 * Class PhpPactTest
 */
class PhpPactTest extends Unit
{
    /**
     * @var SuiteEvent
     */
    protected $event;

    public function _setUp()
    {
        $suite = new Suite();
        $result = new TestResult();
        $this->event = new SuiteEvent($suite, $result);
    }

    public function testInitSuite()
    {
        $extension = new PhpPact([
            'env' => [
                'PACT_MOCK_SERVER_HOST' => 'localhost',
                'PACT_MOCK_SERVER_PORT' => 7200,
                'PACT_CONSUMER_NAME' => 'ExampleOne',
                'PACT_PROVIDER_NAME' => 'ExampleAPI',
                'PACT_OUTPUT_DIR' => '/tmp/pact',
                'PACT_LOG' => '/tmp/pact_log',
                'PACT_CONSUMER_VERSION' => '1.0.0',
                'PACT_CONSUMER_TAG' => 'master',
            ]
        ], []);
        $extension->initSuite($this->event);
        $this->assertEquals('localhost', \getenv('PACT_MOCK_SERVER_HOST'));
        $this->assertEquals('7200', \getenv('PACT_MOCK_SERVER_PORT'));
        $this->assertEquals('ExampleOne', \getenv('PACT_CONSUMER_NAME'));
        $this->assertEquals('ExampleAPI', \getenv('PACT_PROVIDER_NAME'));
        $this->assertEquals('/tmp/pact', \getenv('PACT_OUTPUT_DIR'));
        $this->assertEquals('/tmp/pact_log', \getenv('PACT_LOG'));
        $this->assertEquals('1.0.0', \getenv('PACT_CONSUMER_VERSION'));
        $this->assertEquals('master', \getenv('PACT_CONSUMER_TAG'));

        $mockServerConfig = $this->getProperty($extension, 'mockServerConfig');
        $this->assertInstanceOf(MockServerConfig::class, $mockServerConfig);
    }

    public function testBeforeAfterSuite()
    {
        $extension = new PhpPact([], []);
        try {
            $extension->initSuite($this->event);
            $extension->beforeSuite($this->event);
            $extension->afterSuite($this->event);
        } catch (HealthCheckFailedException $exception) {
            $this->fail('Pact server must be started successfully before suite');
        } catch (ProcessException $exception) {
            $this->fail('Pact server must be stopped successfully after suite');
        }

        $this->assertEquals('./tests/_output/pact', \getenv('PACT_OUTPUT_DIR'));
        $this->assertEquals('./tests/_output/pact_log', \getenv('PACT_LOG'));

        $server = $this->getProperty($extension, 'server');
        $this->assertInstanceOf(MockServer::class, $server);
    }

    protected function getProperty($object, $propertyName)
    {
        $reflection = new ReflectionObject($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
