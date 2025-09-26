<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Umodi\Unit;

function dbMixin(Unit $unit, EntityManagerInterface $em): void
{
    $unit->before(static function() {
//        runMigrations();
//        runFixtures();
    });

    $unit->beforeEach(static function() use ($em) {
        $em->getConnection()->beginTransaction();
    });

    $unit->afterEach(static function() use ($em) {
        $em->getConnection()->rollBack();
    });
}
