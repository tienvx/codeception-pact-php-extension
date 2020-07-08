<?php

namespace CodeceptionPactPhp\Tests\unit\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Suite;
use Codeception\Test\Unit;
use CodeceptionPactPhp\Extension\PactVerify;
use PhpPact\Standalone\ProviderVerifier\Model\VerifierConfig;
use PhpPact\Standalone\ProviderVerifier\Verifier;
use PHPUnit\Framework\TestResult;
use ReflectionObject;
use PhpPact\Broker\Service\BrokerHttpClient;

/**
 * Class PhpPactTest
 */
class PactVerifyTest extends Unit
{
    /**
     * @var SuiteEvent
     */
    protected $event;

    public function _setUp()
    {
        $suite = new Suite();
        $result = new TestResult();
        $this->event = new SuiteEvent($suite, $result);
    }

    public function testInitSuite()
    {
        $extension = new PactVerify([], []);
        $extension->initSuite($this->event);

        $verifierConfig = $this->getProperty($extension, 'verifierConfig');
        $this->assertInstanceOf(VerifierConfig::class, $verifierConfig);

        $brokerHttpClient = $this->getProperty($extension, 'brokerHttpClient');
        $this->assertInstanceOf(BrokerHttpClient::class, $brokerHttpClient);

        $verifier = $this->getProperty($extension, 'verifier');
        $this->assertInstanceOf(Verifier::class, $verifier);
    }

    /**
     * @dataProvider provider
     */
    public function testAfterSuite(bool $all, ?string $tag, array $consumers, array $files)
    {
        $verifier = $this->createMock(verifier::class);

        if ($all) {
            $verifier->expects($this->once())
                ->method('verifyAll');
        } else {
            $verifier->expects($this->never())
                ->method('verifyAll');
        }

        if (!$all && $tag) {
            $verifier->expects($this->once())
                ->method('verifyAllForTag')
                ->with($tag);
        } else {
            $verifier->expects($this->never())
                ->method('verifyAllForTag');
        }

        if (!$all && !$tag && $consumers) {
            $verifier->expects($this->exactly(count($consumers)))
                ->method('verify')
                ->withConsecutive(
                    [$this->equalTo('Consumer Name 1'), $this->equalTo('master'), $this->equalTo('1.0.0')],
                    [$this->equalTo('Consumer Name 2'), $this->equalTo('develop'), $this->equalTo('1.0.1')]
                );
        } else {
            $verifier->expects($this->never())
                ->method('verify');
        }

        if ($files) {
            $verifier->expects($this->once())
                ->method('verifyFiles')
                ->with($files);
        } else {
            $verifier->expects($this->never())
                ->method('verifyFiles');
        }

        $extension = new PactVerify([
            'consumers' => $consumers,
            'files' => $files,
            'all' => $all,
            'tag' => $tag,
        ], [], null, null, $verifier);
        $extension->afterSuite($this->event);
    }

    public function provider()
    {
        return [
            [true, null, [], []],
            [false, 'test', [], []],
            [false, null, [
                [
                    'name' => 'Consumer Name 1',
                    'version' => '1.0.0',
                    'tag' => 'master'
                ],
                [
                    'name' => 'Consumer Name 2',
                    'version' => '1.0.1',
                    'tag' => 'develop'
                ]
            ], []],
            [false, null, [], ['/path/to/file1.json', '/path/to/file2.json']],
        ];
    }

    protected function getProperty($object, $propertyName)
    {
        $reflection = new ReflectionObject($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
