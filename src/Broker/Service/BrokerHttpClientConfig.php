<?php

namespace CodeceptionPact\Broker\Service;

trait BrokerHttpClientConfig
{
    protected function getClientConfig(): array
    {
        $clientConfig = [];
        if (($user = \getenv('PACT_BROKER_HTTP_AUTH_USER')) &&
            ($pass = \getenv('PACT_BROKER_HTTP_AUTH_PASS'))
        ) {
            $clientConfig = [
                'auth' => [$user, $pass],
            ];
        }

        if (($sslVerify = \getenv('PACT_BROKER_SSL_VERIFY'))) {
            $clientConfig['verify'] = $sslVerify !== 'no';
        }

        return $clientConfig;
    }

    protected function getHeaders(): array
    {
        $headers = [];
        if ($bearerToken = \getenv('PACT_BROKER_BEARER_TOKEN')) {
            $headers['Authorization'] = 'Bearer ' . $bearerToken;
        }

        return $headers;
    }
}
