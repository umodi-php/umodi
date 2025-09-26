<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'Umodi\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $path = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/Assert/Comparison/Eq.php';
require_once __DIR__ . '/../src/Assert/Boolean/IsTrue.php';
require_once __DIR__ . '/../src/Assert/Array/Count.php';
require_once __DIR__ . '/../src/Assert/Type/IsInstanceOf.php';

use Umodi\AssertCollector;
use Umodi\AssertResolution;
use Umodi\Assertion;
use Umodi\Di\ParameterResolverInterface;
use Umodi\Di\ProvidedMapResolver;
use Umodi\Di\Resolution;
use Umodi\ProgressWatcher\ProgressWatcherInterface;
use Umodi\Unit;
use Umodi\UnitLoaderInterface;
use Umodi\UnitRunner;
use function Umodi\Assert\Array\count as assertCount;
use function Umodi\Assert\Boolean\isTrue;
use function Umodi\Assert\Comparison\eq;
use function Umodi\Assert\Type\isInstanceOf;
use function Umodi\unit;

final class FakeParameterResolver implements ParameterResolverInterface
{
    public function resolve(\ReflectionParameter $param, array $provided, array $context = []): Resolution
    {
        $name = $param->getName();
        if (array_key_exists($name, $provided)) {
            return Resolution::hit($provided[$name]);
        }

        $type = $param->getType();
        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            $className = $type->getName();
            if (array_key_exists($className, $provided)) {
                return Resolution::hit($provided[$className]);
            }
        }

        return Resolution::miss();
    }
}

final class SpyProgressWatcher implements ProgressWatcherInterface
{
    public array $startedUnits = [];
    public array $unitStarts = [];
    public array $testResults = [];
    public bool $ended = false;

    public function onStart(array $units): void
    {
        $this->startedUnits = array_keys($units);
    }

    public function onUnitStart(string $unitTitle, Unit $unit): void
    {
        $this->unitStarts[] = $unitTitle;
    }

    public function onTestResult(string $unitTitle, Unit $unit, string $testTitle, AssertCollector $assertCollector): void
    {
        $this->testResults[] = [$unitTitle, $testTitle, $assertCollector];
    }

    public function onEnd(): void
    {
        $this->ended = true;
    }
}

final class FakeUnitLoader implements UnitLoaderInterface
{
    public int $loadCalls = 0;

    /** @param array<string, callable> $units */
    public function __construct(private array $units)
    {
    }

    public function load(): array
    {
        $this->loadCalls++;

        return $this->units;
    }
}

final class InlineUnitLoader implements UnitLoaderInterface
{
    public function load(): array
    {
        return \Umodi\_unit();
    }
}

final class NullProgressWatcher implements ProgressWatcherInterface
{
    public function onStart(array $units): void {}

    public function onUnitStart(string $unitTitle, Unit $unit): void {}

    public function onTestResult(string $unitTitle, Unit $unit, string $testTitle, AssertCollector $assertCollector): void {}

    public function onEnd(): void {}
}

unit('UnitRunner', function (Unit $unit): void {
    $unit->test('it runs units returned by loader', function (AssertCollector $assert): void {
        $beforeCalls = 0;
        $afterCalls = 0;
        $beforeEachCalls = 0;
        $afterEachCalls = 0;
        $testCalls = 0;

        $loader = new FakeUnitLoader([
            'Demo unit' => function (Unit $unit) use (&$beforeCalls, &$afterCalls, &$beforeEachCalls, &$afterEachCalls, &$testCalls): void {
                $unit->before(function (Unit $unit) use (&$beforeCalls): void {
                    $beforeCalls++;
                });

                $unit->after(function (Unit $unit) use (&$afterCalls): void {
                    $afterCalls++;
                });

                $unit->beforeEach(function (Unit $unit) use (&$beforeEachCalls): void {
                    $beforeEachCalls++;
                });

                $unit->afterEach(function (Unit $unit) use (&$afterEachCalls): void {
                    $afterEachCalls++;
                });

                $unit->test('it runs', function (AssertCollector $collector) use (&$testCalls): void {
                    $testCalls++;
                    $collector->assertions[] = new Assertion(
                        'passes',
                        'Everything is fine',
                        AssertResolution::Success,
                        __FILE__,
                        __LINE__,
                    );
                });
            },
        ]);

        $watcher = new SpyProgressWatcher();
        $resolver = new FakeParameterResolver();
        $runner = new UnitRunner($watcher, $resolver, $loader);

        $result = $runner->run();

        $assert->assert(eq(1, $loader->loadCalls), 'Loader should be called once');
        $assert->assert(eq(1, $beforeCalls), 'before hook should be executed once');
        $assert->assert(eq(1, $afterCalls), 'after hook should be executed once');
        $assert->assert(eq(1, $beforeEachCalls), 'beforeEach hook should be executed once');
        $assert->assert(eq(1, $afterEachCalls), 'afterEach hook should be executed once');
        $assert->assert(eq(1, $testCalls), 'Test callback should be executed once');

        $assert->assert(eq(1, $result->tests), 'Exactly one test should be registered');
        $assert->assert(eq(1, $result->assertions), 'Exactly one assertion should be registered');
        $assert->assert(eq(1, $result->testsFor(AssertResolution::Success)), 'Test should be marked as successful');
        $assert->assert(eq(AssertResolution::Success, $result->worstResolution()), 'Worst resolution should be success');

        $assert->assert(eq(['Demo unit'], $watcher->startedUnits), 'Runner should notify watcher about started unit');
        $assert->assert(eq(['Demo unit'], $watcher->unitStarts), 'Runner should notify watcher about unit start');
        $assert->assert(assertCount(1, $watcher->testResults), 'Watcher should receive one test result');

        [$unitTitle, $testTitle, $collector] = $watcher->testResults[0];
        $assert->assert(eq('Demo unit', $unitTitle), 'Watcher should receive correct unit title');
        $assert->assert(eq('it runs', $testTitle), 'Watcher should receive correct test title');
        $assert->assert(isInstanceOf(AssertCollector::class, $collector), 'Watcher should receive collector instance');
        $assert->assert(assertCount(1, $collector->assertions), 'Collector should contain one assertion');
        $assert->assert(isTrue($watcher->ended), 'Watcher should be notified about test run completion');
    });
});

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $runner = new UnitRunner(
        new NullProgressWatcher(),
        new ProvidedMapResolver(),
        new InlineUnitLoader(),
    );

    $result = $runner->run();

    if ($result->worstResolution() !== AssertResolution::Success) {
        fwrite(STDERR, "UnitRunner tests failed\n");
        exit(1);
    }

    echo 'All assertions passed' . PHP_EOL;
}
