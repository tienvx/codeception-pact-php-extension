# Codeception PACT Extension

[![Build Status](https://github.com/tienvx/codeception-pact-extension/workflows/main/badge.svg)](https://github.com/tienvx/codeception-pact-extension/actions)

## Installation

```
composer require --dev "tienvx/codeception-pact-extension"
```

## Usage

Enable the extension for your suite:

```yaml
extensions:
    enabled:
        - CodeceptionPact\Extension\PhpPact:
            PACT_CONSUMER_NAME: BookPublisher
            PACT_PROVIDER_NAME: BookStore
```
