<?php

namespace CodeceptionPactPhp\Tests\unit\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Suite;
use Codeception\Test\Unit;
use CodeceptionPactPhp\Extension\Env;
use PHPUnit\Framework\TestResult;

/**
 * Class EnvTest
 */
class EnvTest extends Unit
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
        \putenv("PACT_PROVIDER_BASE_URL=http://localhost:58000");
        \putenv("PACT_PROVIDER_STATES_SETUP_URL=http://localhost:58000/states-setup");
        \putenv("PACT_BROKER_URI=https://testing-example.pact.dius.com.au");
        \putenv("PACT_BROKER_BEARER_TOKEN=xxxxxx");

        $extension = new Env([
            'PACT_MOCK_SERVER_HOST' => 'localhost',
            'PACT_MOCK_SERVER_PORT' => 7200,
            'PACT_CONSUMER_NAME' => 'ExampleOne',
            'PACT_PROVIDER_NAME' => 'ExampleAPI',
            'PACT_OUTPUT_DIR' => '/tmp/pact',
            'PACT_LOG' => '/tmp/pact_log',
            'PACT_CONSUMER_VERSION' => '1.0.0',
            'PACT_CONSUMER_TAG' => 'master',
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

        $this->assertEquals('http://localhost:58000', \getenv('PACT_PROVIDER_BASE_URL'));
        $this->assertEquals('http://localhost:58000/states-setup', \getenv('PACT_PROVIDER_STATES_SETUP_URL'));
        $this->assertEquals('https://testing-example.pact.dius.com.au', \getenv('PACT_BROKER_URI'));
        $this->assertEquals(false, \getenv('PACT_BROKER_HTTP_AUTH_USER'));
        $this->assertEquals(false, \getenv('PACT_BROKER_HTTP_AUTH_PASS'));
        $this->assertEquals('xxxxxx', \getenv('PACT_BROKER_BEARER_TOKEN'));
    }
}
