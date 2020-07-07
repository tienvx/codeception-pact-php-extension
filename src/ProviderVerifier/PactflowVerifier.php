<?php

namespace CodeceptionPact\ProviderVerifier;

use PhpPact\Standalone\ProviderVerifier\Verifier;
use PhpPact\Broker\Service\BrokerHttpClient;
use PhpPact\Http\GuzzleClient;
use CodeceptionPact\Broker\Service\BrokerHttpClientConfig;

class PactflowVerifier extends Verifier
{
    use BrokerHttpClientConfig;

    protected function getBrokerHttpClient(): BrokerHttpClient
    {
        if (!$this->brokerHttpClient) {
            $client = new GuzzleClient($this->getClientConfig());

            $this->brokerHttpClient = new BrokerHttpClient($client, $this->config->getBrokerUri(), $this->getHeaders());
        }

        return $this->brokerHttpClient;
    }
}
