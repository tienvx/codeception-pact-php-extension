# Codeception PACT Module


Module for contract testing using PACT.

## Configuration

    modules:
        enabled:
            - \CodeceptionPact\Codeception\PhpPact


## Actions

### haveTask

Create task object contains settings for later testing.

Example:
```php
$task = $I->haveTask('Test shopping cart checkout', 'sylus_demo_checkout', 'random', 'loop', ['email'], true, [
    'max-steps' => 300,
    'transition-coverage' => 100,
    'place-coverage' => 100,
]);
```

Task will be automatically executed

### consumeMessages

Consume all messages one by one.

Example:
```php
$I->consumeMessages('async');
```


### grabBugsFromTask

Finds and returns bugs of task.

```php
$bugs = $I->grabBugsFromTask($task);
$I->assertCount(1, $bugs);
$I->assertEquals('Can not place order', $bugs[0]->getBugMessage());
$I->assertGreaterOrEquals(9, $bugs[0]->getSteps()->getLength());
```
