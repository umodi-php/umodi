<?php

declare(strict_types=1);

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Umodi\Assert\Browser\StatusOk;
use Umodi\Assert\IsTrue;
use Umodi\Attribute\Incomplete;
use Umodi\Attribute\Skipped;
use Umodi\Exception\TestPreconditionFailedException;
use Umodi\Unit;
use Unit\AssertCollector;

unit('Bulk post archive', static function (Unit $unit, EntityManagerInterface $em, KernelBrowser $browser) {
    dbMixin($unit, $em);

    $unit->test('Test precondition failed',
        #[Skipped('Bug')] static function (AssertCollector $a) {
            throw new TestPreconditionFailedException('Test precondition failed');
        });

    $unit->test('Test assert failed',
        #[Incomplete('Flaky')] static function (AssertCollector $a) {
            $a->assert(IsTrue::a(false), 'Failed asserting true');
            $a->assert(IsTrue::a(false), 'Failed asserting true 2');
        });

    $unit->test('Test assert success',
        static function (AssertCollector $a) {
            $a->assert(IsTrue::a(true), 'Success asserting true');
            $a->assert(IsTrue::a(true), 'Success asserting true 2');
        });

    $unit->test('Test assert skip',
        static function (AssertCollector $a) {
            $a->skip(IsTrue::a(false), 'Skipped');
        });

    $unit->test('Test assert skip partial',
        static function (AssertCollector $a) {
            $a->assert(IsTrue::a(true), 'Not skipped');
            $a->skip(IsTrue::a(false), 'Skipped');
        });

    $unit->test('Bulk post archive',
        static function (AssertCollector $a) use ($em, $browser) {
            $post = $em->find(Post::class, 1);
            if (!$post instanceof Post || $post->getIsArchived()) {
                throw new TestPreconditionFailedException('Post with id #1 is not exists or archived');
            }
            $post = $em->find(Post::class, 2);
            if (!$post instanceof Post || $post->getIsArchived()) {
                throw new TestPreconditionFailedException('Post with id #2 is not exists or archived');
            }
            $browser->request('PATCH', '/posts/archive/',
                [
                    'post_ids' => [1, 2],
                ]);

            $a->assert(StatusOk::a($browser->getResponse()), 'Http OK');

            $post = $em->find(Post::class, 1);
            $a->assert(IsTrue::a($post->getIsArchived()), "Post 1 archived");

            $post = $em->find(Post::class, 2);
            $a->assert(IsTrue::a($post->getIsArchived()), 'Post 2 archived');
        });
});
