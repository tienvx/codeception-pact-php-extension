<?php

namespace CodeceptionPactPhp\Tests\unit\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Suite;
use Codeception\Test\Unit;
use CodeceptionPactPhp\Extension\Pact;
use PhpPact\Broker\Service\BrokerHttpClient;
use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfig;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;
use PHPUnit\Framework\TestResult;
use ReflectionObject;

/**
 * Class PhpPactTest
 */
class PactTest extends Unit
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
        $extension = new Pact([], []);
        $extension->initSuite($this->event);

        $mockServerConfig = $this->getProperty($extension, 'mockServerConfig');
        $this->assertInstanceOf(MockServerConfig::class, $mockServerConfig);

        $server = $this->getProperty($extension, 'server');
        $this->assertInstanceOf(MockServer::class, $server);

        $httpService = $this->getProperty($extension, 'httpService');
        $this->assertInstanceOf(MockServerHttpService::class, $httpService);
    }

    public function testBeforeSuite()
    {
        $server = $this->createMock(MockServer::class);
        $server->expects($this->once())
            ->method('start');

        $extension = new Pact([], [], null, $server);
        $extension->beforeSuite($this->event);
    }

    public function testAfterSuite()
    {
        $server = $this->createMock(MockServer::class);
        $server->expects($this->once())
            ->method('stop');

        $httpService = $this->createMock(MockServerHttpService::class);
        $httpService->expects($this->once())
            ->method('verifyInteractions');
        $httpService->expects($this->once())
            ->method('getPactJson')
            ->will($this->returnValue(file_get_contents(codecept_data_dir('someconsumer-someprovider.json'))));

        $brokerHttpClient = $this->createMock(BrokerHttpClient::class);
        $brokerHttpClient->expects($this->once())
            ->method('tag')
            ->with('Some Consumer', '1.0.0', 'master');
        $brokerHttpClient->expects($this->once())
            ->method('publishJson')
            ->with(file_get_contents(codecept_data_dir('someconsumer-someprovider.json')), '1.0.0');

        putenv('PACT_BROKER_URI=localhost');
        putenv('PACT_CONSUMER_NAME=Some Consumer');
        putenv('PACT_CONSUMER_VERSION=1.0.0');
        putenv('PACT_CONSUMER_TAG=master');
        $extension = new Pact([], [], new MockServerEnvConfig(), $server, $httpService, $brokerHttpClient);
        $extension->afterSuite($this->event);
    }

    protected function getProperty($object, $propertyName)
    {
        $reflection = new ReflectionObject($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
