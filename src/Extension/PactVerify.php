<?php

namespace CodeceptionPactPhp\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;
use CodeceptionPactPhp\Broker\Service\BrokerHttpClientConfig;
use GuzzleHttp\Psr7\Uri;
use PhpPact\Broker\Service\BrokerHttpClientInterface;
use PhpPact\Standalone\ProviderVerifier\Model\VerifierConfig;
use PhpPact\Broker\Service\BrokerHttpClient;
use PhpPact\Http\GuzzleClient;
use PhpPact\Standalone\ProviderVerifier\Model\VerifierConfigInterface;
use PhpPact\Standalone\ProviderVerifier\Verifier;

class PactVerify extends Extension
{
    use BrokerHttpClientConfig;

    protected $config = [
        'consumers' => [],
        'files' => [],
        'all' => false,
        'tag' => null,
    ];

    /**
     * @var VerifierConfigInterface
     */
    protected $verifierConfig;

    /**
     * @var BrokerHttpClientInterface
     */
    protected $brokerHttpClient;

    /**
     * @var Verifier
     */
    protected $verifier;

    public static $events = [
        Events::SUITE_INIT => 'initSuite',
        Events::SUITE_AFTER  => 'afterSuite',
    ];

    public function __construct($config, $options, VerifierConfigInterface $verifierConfig = null, BrokerHttpClientInterface $brokerHttpClient = null, Verifier $verifier = null)
    {
        parent::__construct($config, $options);

        $this->verifierConfig = $verifierConfig;
        $this->brokerHttpClient = $brokerHttpClient;
        $this->verifier = $verifier;
    }

    public function initSuite(SuiteEvent $e)
    {
        if (!$this->verifierConfig) {
            $this->verifierConfig = (new VerifierConfig())
                ->setProviderName(\getenv('PACT_PROVIDER_NAME'))
                ->setProviderVersion(\getenv('PACT_PROVIDER_VERSION'))
                ->setProviderBaseUrl(new Uri(\getenv('PACT_PROVIDER_BASE_URL')))
                ->setBrokerUri(new Uri(\getenv('PACT_BROKER_URI')))
                ->setPublishResults(\getenv('PACT_PUBLISH_VERIFICATION_RESULTS'));
            ;
        }
        if (!$this->brokerHttpClient) {
            $client = new GuzzleClient($this->getClientConfig());
            $this->brokerHttpClient = new BrokerHttpClient($client, new Uri($this->verifierConfig->getBrokerUri()), $this->getHeaders());
        }
        if (!$this->verifier) {
            $this->verifier = new Verifier($this->verifierConfig, null, null, $this->brokerHttpClient);
        }
    }

    public function afterSuite(SuiteEvent $e)
    {
        if ($e->getResult()->failureCount() > 0) {
            print 'A unit test has failed. Skipping PACT verifing.';
            return;
        }

        // Verify remote pacts from Pact Broker
        if ($this->config['all']) {
            $this->verifier->verifyAll();
        } elseif ($this->config['tag']) {
            $this->verifier->verifyAllForTag($this->config['tag']);
        } elseif ($this->config['consumers']) {
            foreach ($this->config['consumers'] as $consumer) {
                $this->verifier->verify($consumer['name'], $consumer['tag'], $consumer['version']);
            }
        }

        // Verify local pacts
        if ($this->config['files']) {
            $this->verifier->verifyFiles($this->config['files']);
        }
    }
}
