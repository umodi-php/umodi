<?php

declare(strict_types=1);

namespace Umodi;

use ReflectionFunction;
use ReflectionMethod;
use Umodi\Attribute\Incomplete;
use Umodi\Attribute\Skipped;
use Umodi\Di\Invoker\InvokerInterface;
use Umodi\Exception\TestPreconditionFailedException;
use Umodi\ProgressWatcher\ProgressWatcherInterface;
use Umodi\Result\AssertCollector;
use Umodi\Result\Assertion;
use Umodi\Result\TestOutcome;
use Umodi\Severity\AssertResolution;
use Umodi\Severity\DefaultExceptionClassifier;
use Umodi\Severity\DefaultSeverityPolicy;
use Umodi\Severity\ExceptionClassifierInterface;
use Umodi\Severity\TestResolutionAggregator;
use Umodi\UnitLoader\FileSystemUnitLoader;
use Umodi\UnitLoader\UnitLoaderInterface;

class UnitRunner
{
    private UnitLoaderInterface $unitLoader;
    private TestResolutionAggregator $testResolutionAggregator;
    private ExceptionClassifierInterface $classifier;

    public function __construct(
        private readonly ProgressWatcherInterface $progressWatcher,
        private readonly InvokerInterface $invoker,
        ?UnitLoaderInterface $unitLoader = null,
        ?TestResolutionAggregator $aggregator = null,
        ?ExceptionClassifierInterface $classifier = null,
    ) {
        $this->unitLoader = $unitLoader ?? new FilesystemUnitLoader(getcwd().'/tests');
        $this->testResolutionAggregator = $aggregator ?? new TestResolutionAggregator(new DefaultSeverityPolicy());
        $this->classifier = $classifier ?? new DefaultExceptionClassifier();
    }

    public function run(): void
    {
        $units = $this->unitLoader->load();
        $allTests = $this->extractTestsFromUnits($units);

        $this->progressWatcher->onStart($allTests);

        foreach ($allTests as $unitTitle => $unit) {
            $tests = $unit->getTests();
            $this->progressWatcher->onUnitStart($unitTitle, $unit);

            if ($unit->skippedReason === null) {
                $unit->runBefore();
            }

            foreach ($tests as $testTitle => $testCallback) {
                $assertCollector = new AssertCollector();
                if ($unit->skippedReason !== null) {
                    $assertCollector->assertions[] = new Assertion(
                        $unitTitle,
                        'Skipped: ' . $unit->skippedReason,
                        AssertResolution::Skipped,
                        $unit->file,
                        $unit->line,
                    );
                } else {

                    $unit->runBeforeEach();

                    try {
                        $this->invoker->invoke(
                            $testCallback,
                            [
                                AssertCollector::class => $assertCollector,
                                Unit::class => $unit,
                            ],
                        );
                    } catch (\Umodi\Exception\StopTestException) {
                    } catch (TestPreconditionFailedException $e) {
                        $assertCollector->assertions[] = new Assertion(
                            'Test precondition failed',
                            $e->getMessage(),
                            $this->classifier->classify($e),
                            $e->getFile(),
                            $e->getLine(),
                        );
                    } catch (\Throwable $e) {
                        $assertCollector->assertions[] = new Assertion(
                            'Error occurred',
                            $e::class . ': ' . $e->getMessage(),
                            $this->classifier->classify($e),
                            $e->getFile(),
                            $e->getLine(),
                        );
                    }
                }
                $testResolution = $this->testResolutionAggregator->aggregate($assertCollector);
                $outcome = new TestOutcome($unitTitle, $testTitle, $testResolution, $assertCollector->assertions);

                $this->progressWatcher->onTestResult($outcome);

                $unit->runAfterEach();
            }

            if ($unit->skippedReason === null) {
                $unit->runAfter();
            }
        }
        $this->progressWatcher->onEnd();
    }

    /**
     * @param iterable<string, callable> $units
     * @return array<string, Unit>
     */
    private function extractTestsFromUnits(iterable $units): array
    {
        /** @var array<string, Unit> $allTests */
        $allTests = [];

        foreach ($units as $unitTitle => $unitCallback) {
            $unit = new Unit();
            $this->enrichWithMeta($unit, $unitCallback);

            $this->invoker->invoke(
                $unitCallback,
                [
                    Unit::class => $unit,
                ]
            );

            $allTests[$unitTitle] = $unit;
        }
        return $allTests;
    }

    private function enrichWithMeta(Unit $unit, callable $unitCallback): void
    {
        $ref = null;
        if ($unitCallback instanceof \Closure) {
            $ref = new ReflectionFunction($unitCallback);
        } elseif (is_array($unitCallback)) {
            [$objOrClass, $method] = $unitCallback;
            $ref = new ReflectionMethod($objOrClass, (string)$method);
        } elseif (is_string($unitCallback) && \function_exists($unitCallback)) {
            $ref = new ReflectionFunction($unitCallback);
        }
        $skippedReason = null;
        $incompleteReason = null;
        $filename = '';
        $line = 0;
        if ($ref) {
            $filename = $ref->getFileName();
            $line = $ref->getStartLine();
            $attrs = $ref->getAttributes(Skipped::class);
            if ($attrs) {
                $skippedReason = $attrs[0]->newInstance()->reason;
            }
            $attrs = $ref->getAttributes(Incomplete::class);
            if ($attrs) {
                $incompleteReason = $attrs[0]->newInstance()->reason;
            }
        }

        $unit->file = $filename;
        $unit->line = $line;
        $unit->skippedReason = $skippedReason;
        $unit->incompleteReason = $incompleteReason;
    }
}
