<?php

declare(strict_types=1);

namespace Umodi;

use DirectoryIterator;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Umodi\Attribute\Skipped;
use Umodi\Di\ParameterResolverInterface;
use Umodi\Exception\TestPreconditionFailedException;
use Umodi\ProgressWatcher\ProgressWatcherInterface;

class UnitRunner
{
    public function __construct(
        public readonly ProgressWatcherInterface    $progressWatcher,
        private readonly ParameterResolverInterface $resolver,
    )
    {
    }

    public function run(): void
    {
        foreach (new DirectoryIterator(getcwd() . '/tests') as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            include_once $fileInfo->getRealPath();
        }

        $units = _unit();

        /** @var array<string, Unit> $allTests */
        $allTests = [];

        foreach ($units as $unitTitle => $unitCallback) {
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
            $filename = '';
            $line = 0;
            if ($ref) {
                $filename = $ref->getFileName();
                $line = $ref->getStartLine();
                $attrs = $ref->getAttributes(Skipped::class);
                if ($attrs) {
                    /** @var Skipped $s */
                    $skippedAttribute = $attrs[0]->newInstance();
                    $skippedReason = $skippedAttribute->reason;
                }
            }

            $unit = new Unit();
            $unit->file = $filename;
            $unit->line = $line;
            $unit->skippedReason = $skippedReason;

            $this->invoke(
                $unitCallback,
                [
                    'unit'       => $unit,
                    Unit::class  => $unit,
                ],
                [
                    'unitTitle' => $unitTitle,
                ]
            );

            $allTests[$unitTitle] = $unit;
        }

        $this->progressWatcher->onStart($allTests);

        foreach ($allTests as $unitTitle => $unit) {
            $tests = $unit->getTests();
            $this->progressWatcher->onUnitStart($unitTitle, $unit);

            $unit->runBefore();

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
                        $this->invoke(
                            $testCallback,
                            [
                                'assertCollector' => $assertCollector,
                                AssertCollector::class => $assertCollector,
                                'unit' => $unit,
                                Unit::class => $unit,
                            ],
                            [
                                'unitTitle' => $unitTitle,
                                'testTitle' => $testTitle,
                            ],
                        );
                    } catch (TestPreconditionFailedException $e) {
                        $assertCollector->assertions[] = new Assertion(
                            'Test precondition failed',
                            $e->getMessage(),
                            AssertResolution::Error,
                            $e->getFile(),
                            $e->getLine(),
                        );
                    } catch (\Throwable $e) {
                        $assertCollector->assertions[] = new Assertion(
                            'Error occured',
                            $e::class . ': ' . $e->getMessage(),
                            AssertResolution::Error,
                            $e->getFile(),
                            $e->getLine(),
                        );
                    }
                }
                $this->progressWatcher->onTestResult($unitTitle, $unit, $testTitle, $assertCollector);

                $unit->runAfterEach();
            }

            $unit->runAfter();
        }
        $this->progressWatcher->onEnd();
    }

    private function invoke(callable $callable, array $provided = [], array $context = []): mixed
    {
        $ref = $this->reflectCallable($callable);
        $args = [];

        foreach ($ref->getParameters() as $param) {
            $res = $this->resolver->resolve($param, $provided, $context);
            if ($res->ok) {
                $args[] = $res->value;
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            $type = $param->getType();
            $class = $type && !$type->isBuiltin() ? (string)$type : null;
            $hint = $class ? "type {$class}" : "name \${$param->getName()}";
            throw new \RuntimeException("Can't inject {$hint} for {$this->callableToString($callable)}");
        }

        return $callable(...$args);
    }

    private function reflectCallable(callable $c): ReflectionFunctionAbstract
    {
        if ($c instanceof \Closure) {
            return new ReflectionFunction($c);
        }
        if (is_array($c)) {
            [$objOrClass, $method] = $c;
            return new ReflectionMethod($objOrClass, $method);
        }
        if (is_object($c) && method_exists($c, '__invoke')) {
            return new ReflectionMethod($c, '__invoke');
        }
        if (is_string($c) && function_exists($c)) {
            return new ReflectionFunction($c);
        }

        return new ReflectionFunction(\Closure::fromCallable($c));
    }

    private function callableToString(callable $c): string
    {
        if (is_array($c)) {
            [$objOrClass, $method] = $c;
            if (is_object($objOrClass)) {
                return get_class($objOrClass) . "->{$method}()";
            }
            return $objOrClass . "::{$method}()";
        }
        if ($c instanceof \Closure) {
            return 'Closure';
        }
        if (is_object($c) && method_exists($c, '__invoke')) {
            return get_class($c) . '::__invoke()';
        }
        if (is_string($c)) {
            return $c;
        }
        return 'callable';
    }
}
