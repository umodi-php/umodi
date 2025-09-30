<?php

declare(strict_types=1);

use Umodi\Attribute\Incomplete;
use Umodi\Attribute\Skipped;
use Umodi\Exception\TestPreconditionFailedException;
use Umodi\Result\AssertCollector;
use Umodi\Unit;
use function Umodi\Assert\Boolean\isTrue;
use function Umodi\unit;

unit('Bulk post archive', static function (Unit $unit) {
    $unit->test('Test precondition failed',
        #[Skipped('Bug')] static function (AssertCollector $a) {
            throw new TestPreconditionFailedException('Test precondition failed');
        });

    $unit->test('Test assert failed',
        #[Incomplete('Flaky')] static function (AssertCollector $a) {
            $a->assert(isTrue(false), 'Failed asserting true');
            $a->assert(isTrue(false), 'Failed asserting true 2');
        });

    $unit->test('Test assert success',
        static function (AssertCollector $a) {
            $a->assert(isTrue(true), 'Success asserting true');
            $a->assert(isTrue(true), 'Success asserting true 2');
        });

    $unit->test('Test assert skip',
        static function (AssertCollector $a) {
            $a->skip(isTrue(false), 'Skipped');
        });

    $unit->test('Test assert skip partial',
        static function (AssertCollector $a) {
            $a->assert(isTrue(true), 'Not skipped');
            $a->skip(isTrue(false), 'Skipped');
        });
});
