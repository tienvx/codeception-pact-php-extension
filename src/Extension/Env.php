<?php

namespace CodeceptionPactPhp\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;
use PhpPact\Standalone\MockService\MockServerEnvConfig;

class Env extends Extension
{
    protected $config = [
        'PACT_MOCK_SERVER_HOST' => 'localhost',
        'PACT_MOCK_SERVER_PORT' => 7200,
        'PACT_CONSUMER_NAME' => null,
        'PACT_CONSUMER_VERSION' => null,
        'PACT_CONSUMER_TAG' => null,
        'PACT_PROVIDER_NAME' => null,
        'PACT_PROVIDER_VERSION' => null,
        'PACT_PROVIDER_BASE_URL' => null,
        'PACT_OUTPUT_DIR' => './tests/_output/pact',
        'PACT_CORS' => false,
        'PACT_LOG' => './tests/_output/pact_log',
        'PACT_MOCK_SERVER_HEALTH_CHECK_TIMEOUT' => 10,
        'PACT_SPECIFICATION_VERSION' => MockServerEnvConfig::DEFAULT_SPECIFICATION_VERSION,
        'PACT_PUBLISH_VERIFICATION_RESULTS' => true,
        'PACT_BROKER_URI' => null,
        'PACT_BROKER_HTTP_AUTH_USER' => null,
        'PACT_BROKER_HTTP_AUTH_PASS' => null,
        'PACT_BROKER_SSL_VERIFY' => 'no',
        'PACT_BROKER_BEARER_TOKEN' => null,
    ];

    public static $events = [
        Events::SUITE_INIT => [
            // This method is called earlier than in Pact and PactVerify extensions
            ['initSuite', 1],
        ],
    ];

    public function initSuite(SuiteEvent $e)
    {
        foreach ($this->config as $key => $value) {
            if (!is_null($value)) {
                putenv("$key=$value");
            }
        }
    }
}
