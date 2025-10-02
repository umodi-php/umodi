<?php

declare(strict_types=1);

use Umodi\Di\Invoker\FakeInvoker;
use Umodi\Exception\TestPreconditionFailedException;
use Umodi\ProgressWatcher\ProgressWatcherSpy;
use Umodi\Result\AssertCollector;
use Umodi\Severity\AssertResolution;
use Umodi\Unit;
use Umodi\UnitLoader\InMemoryUnitLoader;
use Umodi\UnitRunner;
use function Umodi\Assert\Comparison\eq;
use function Umodi\unit;

unit('Test UnitRunner class', static function (Unit $unit) {
    $unit->test('precondition', static function (AssertCollector $collector): void {
        $defs = [
            'Suite B' => static function (Unit $u): void {
                $u->test('precondition', static function (): void {
                    throw new TestPreconditionFailedException('no data');
                });
            },
        ];

        $watcher = new ProgressWatcherSpy();
        $runner = new UnitRunner($watcher, new FakeInvoker(), new InMemoryUnitLoader($defs));
        $runner->run();

        $collector->assertOrStop(\Umodi\Assert\Array\count(1, $watcher->outcomes), 'Exactly one test outcome expected');

        $outcome = $watcher->outcomes[0];

        $collector->assert(eq(AssertResolution::Risky, $outcome->resolution), 'Should be risky');
        $collector->assertOrStop(\Umodi\Assert\Array\count(1, $outcome->assertions), 'Exactly one assertion expected');

        $collector->assert(eq('no data', $outcome->assertions[0]->description), 'Risky reason "no data" must be present');
    });
});
