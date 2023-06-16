<?php

namespace CodeceptionPactPhp\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;
use CodeceptionPactPhp\Broker\Service\BrokerHttpClientConfig;
use GuzzleHttp\Psr7\Uri;
use PhpPact\Broker\Service\BrokerHttpClient;
use PhpPact\Broker\Service\BrokerHttpClientInterface;
use PhpPact\Http\GuzzleClient;
use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfig;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;
use PhpPact\Standalone\MockService\Service\MockServerHttpServiceInterface;

/**
 * Code taken from https://github.com/pact-foundation/pact-php/blob/master/src/PhpPact/Consumer/Listener/PactTestListener.php
 */
class Pact extends Extension
{
    use BrokerHttpClientConfig;

    /**
     * @var MockServer
     */
    protected $server;

    /**
     * @var MockServerConfig
     */
    protected $mockServerConfig;

    /**
     * @var MockServerHttpService
     */
    protected $httpService;

    /**
     * @var BrokerHttpClientInterface
     */
    protected $brokerHttpClient;

    public static $events = [
        Events::SUITE_INIT => 'initSuite',
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER  => 'afterSuite',
    ];

    public function __construct($config, $options, MockServerEnvConfig $mockServerConfig = null, MockServer $server = null, MockServerHttpServiceInterface $httpService = null, BrokerHttpClientInterface $brokerHttpClient = null)
    {
        parent::__construct($config, $options);

        $this->mockServerConfig = $mockServerConfig;
        $this->server = $server;
        $this->httpService = $httpService;
        $this->brokerHttpClient = $brokerHttpClient;
    }

    public function initSuite(SuiteEvent $e)
    {
        if (!$this->mockServerConfig) {
            $this->mockServerConfig = new MockServerEnvConfig();
        }
        if (!$this->server) {
            $this->server = new MockServer($this->mockServerConfig);
        }
        if (!$this->httpService) {
            $this->httpService = new MockServerHttpService(new GuzzleClient(), $this->mockServerConfig);
        }
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $this->server->start();
    }

    public function afterSuite(SuiteEvent $e)
    {
        try {
            $this->httpService->verifyInteractions();
            $json = $this->httpService->getPactJson();
        } finally {
            $this->server->stop();
        }

        if ($e->getResult()->failureCount() > 0) {
            print 'A unit test has failed. Skipping PACT file upload.';
        } elseif (!($pactBrokerUri = \getenv('PACT_BROKER_URI'))) {
            print 'PACT_BROKER_URI environment variable was not set. Skipping PACT file upload.';
        } elseif (!($consumerVersion = \getenv('PACT_CONSUMER_VERSION'))) {
            print 'PACT_CONSUMER_VERSION environment variable was not set. Skipping PACT file upload.';
        } elseif (!($tag = \getenv('PACT_CONSUMER_TAG'))) {
            print 'PACT_CONSUMER_TAG environment variable was not set. Skipping PACT file upload.';
        } else {
            if (!$this->brokerHttpClient) {
                $client = new GuzzleClient($this->getClientConfig());
                $this->brokerHttpClient = new BrokerHttpClient($client, new Uri($pactBrokerUri), $this->getHeaders());
            }

            $this->brokerHttpClient->tag($this->mockServerConfig->getConsumer(), $consumerVersion, $tag);
            $this->brokerHttpClient->publishJson($consumerVersion, $json);
            print 'Pact file has been uploaded to the Broker successfully.';
        }
    }
}
