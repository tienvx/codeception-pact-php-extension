<?php

namespace CodeceptionPact\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;
use GuzzleHttp\Psr7\Uri;
use PhpPact\Broker\Service\BrokerHttpClient;
use PhpPact\Http\GuzzleClient;
use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfig;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;
use CodeceptionPact\Broker\Service\BrokerHttpClientConfig;

/**
 * Code taken from https://github.com/pact-foundation/pact-php/blob/master/src/PhpPact/Consumer/Listener/PactTestListener.php
 */
class PhpPact extends Extension
{
    use BrokerHttpClientConfig;

    protected $config = [
        'env' => [
            'PACT_MOCK_SERVER_HOST' => 'localhost',
            'PACT_MOCK_SERVER_PORT' => 7200,
            'PACT_CONSUMER_NAME' => null,
            'PACT_CONSUMER_VERSION' => null,
            'PACT_CONSUMER_TAG' => null,
            'PACT_PROVIDER_NAME' => null,
            'PACT_OUTPUT_DIR' => './tests/_output/pact',
            'PACT_CORS' => false,
            'PACT_LOG' => './tests/_output/pact_log',
            'PACT_MOCK_SERVER_HEALTH_CHECK_TIMEOUT' => 10,
            'PACT_SPECIFICATION_VERSION' => MockServerEnvConfig::DEFAULT_SPECIFICATION_VERSION,
            'PACT_BROKER_URI' => null,
            'PACT_BROKER_HTTP_AUTH_USER' => null,
            'PACT_BROKER_HTTP_AUTH_PASS' => null,
            'PACT_BROKER_SSL_VERIFY' => 'no',
            'PACT_BROKER_BEARER_TOKEN' => null,
        ],
    ];

    /**
     * @var MockServer
     */
    protected $server;

    /**
     * @var MockServerConfig
     */
    protected $mockServerConfig;

    public static $events = [
        Events::SUITE_INIT => 'initSuite',
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER  => 'afterSuite',
    ];

    public function initSuite(SuiteEvent $e)
    {
        foreach ($this->config['env'] as $key => $value) {
            putenv("$key=$value");
        }
        $this->mockServerConfig = new MockServerEnvConfig();
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $this->server = new MockServer($this->mockServerConfig);
        $this->server->start();
    }

    public function afterSuite(SuiteEvent $e)
    {
        try {
            $httpService = new MockServerHttpService(new GuzzleClient(), $this->mockServerConfig);
            $httpService->verifyInteractions();

            $json = $httpService->getPactJson();
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
            $client = new GuzzleClient($this->getClientConfig());

            $brokerHttpService = new BrokerHttpClient($client, new Uri($pactBrokerUri), $this->getHeaders());
            $brokerHttpService->tag($this->mockServerConfig->getConsumer(), $consumerVersion, $tag);
            $brokerHttpService->publishJson($json, $consumerVersion);
            print 'Pact file has been uploaded to the Broker successfully.';
        }
    }
}
