<?php

namespace CodeceptionPactPhp\Standalone\ProviderVerifier;

use PhpPact\Standalone\ProviderVerifier\Verifier;

/**
 * Class ExtendedVerifier.
 */
class ExtendedVerifier extends Verifier
{
    /**
     * @return array parameters to be passed into the process
     */
    public function getArguments(): array
    {
        $parameters = parent::getArguments();

        if ($brokerToken = \getenv('BROKER_TOKEN')) {
            $parameters[] = "--broker-token={$brokerToken}";
        }

        if ($this->config->getProviderName() !== null) {
            $parameters[] = "--provider={$this->config->getProviderName()}";
        }

        if ($this->config->getProviderVersionTag() !== null) {
            $parameters[] = "[--provider-version-tag={$this->config->getProviderVersionTag()}";
        }

        if ($logDir = \getenv('PACT_LOG_DIR')) {
            $parameters[] = "--log-dir={$logDir}";
        }

        return $parameters;
    }
}
