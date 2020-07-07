<?php

namespace CodeceptionPact\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;
use GuzzleHttp\Psr7\Uri;
use PhpPact\Standalone\ProviderVerifier\Model\VerifierConfig;
use CodeceptionPact\ProviderVerifier\PactflowVerifier;

class PhpPactVerify extends Extension
{
    protected $config = [
        'env' => [
            'PACT_PROVIDER_NAME' => null,
            'PACT_PROVIDER_VERSION' => '1.0.0',
            'PACT_PROVIDER_BASE_URL' => 'http://localhost:58000',
            'PACT_PUBLISH_VERIFICATION_RESULTS' => true,
            'PACT_BROKER_URI' => 'http://localhost',
            'PACT_BROKER_HTTP_AUTH_USER' => null,
            'PACT_BROKER_HTTP_AUTH_PASS' => null,
            'PACT_BROKER_SSL_VERIFY' => 'no',
            'PACT_BROKER_BEARER_TOKEN' => null,
        ],
        'consumers' => [],
        'files' => [],
        'all' => false,
        'tag' => null,
    ];

    public static $events = [
        Events::SUITE_INIT => 'initSuite',
        Events::SUITE_AFTER  => 'afterSuite',
    ];

    public function initSuite(SuiteEvent $e)
    {
        foreach ($this->config['env'] as $key => $value) {
            putenv("$key=$value");
        }
    }

    public function afterSuite(SuiteEvent $e)
    {
        if ($e->getResult()->failureCount() > 0) {
            print 'A unit test has failed. Skipping PACT verifing.';
            return;
        }

        $config = new VerifierConfig();
        $config
            ->setProviderName(\getenv('PACT_PROVIDER_NAME'))
            ->setProviderVersion(\getenv('PACT_PROVIDER_VERSION'))
            ->setProviderBaseUrl(new Uri(\getenv('PACT_PROVIDER_BASE_URL')))
            ->setBrokerUri(new Uri(\getenv('PACT_BROKER_URI')))
            ->setPublishResults(\getenv('PACT_PUBLISH_VERIFICATION_RESULTS'));
    
        $verifier = new PactflowVerifier($config);

        // Verify remote pacts from Pact Broker
        if ($this->config['all']) {
            $verifier->verifyAll();
        } elseif ($this->config['tag']) {
            $verifier->verifyAllForTag($this->config['tag']);
        } elseif ($this->config['consumers']) {
            foreach ($this->config['consumers'] as $consumer) {
                $verifier->verify($consumer['name'], $consumer['tag'], $consumer['version']);
            }
        }

        // Verify local pacts
        if ($this->config['files']) {
            $verifier->verifyFiles($this->config['files']);
        }
    }
}
