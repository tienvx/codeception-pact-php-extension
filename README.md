# Codeception PACT PHP Extensions

[![Build Status](https://github.com/tienvx/codeception-pact-php-extension/workflows/main/badge.svg)](https://github.com/tienvx/codeception-pact-php-extension/actions)

## Installation

```
composer require --dev "tienvx/codeception-pact-php-extension"
```

## Usage

Enable the extensions for your suite:

```yaml
extensions:
    enabled:
        - CodeceptionPactPhp\Extension\Env:
            PACT_CONSUMER_NAME: BookPublisher
            PACT_PROVIDER_NAME: BookStore
        - CodeceptionPactPhp\Extension\Pact
```
