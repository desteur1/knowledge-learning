<?php

namespace App\Tests\Purchase;

use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Cursus;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PurchaseAccessTest extends WebTestCase
{
    /**
     * Create an unverified user for testing.
     */
    private function createUnverifiedUser(EntityManagerInterface $em): User
    {
        $user = new User();
        $user->setEmail('user_'.uniqid().'@example.com');
        $user->setPassword('password');
        $user->setIsVerified(false);
        $user->setRoles(['ROLE_CLIENT']);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Create a minimal Theme entity.
     */
    private function createTheme(EntityManagerInterface $em): Theme
    {
        $theme = new Theme();
        $theme->setName('Theme Test');

        $em->persist($theme);
        $em->flush();

        return $theme;
    }

    /**
     * Create a minimal Cursus entity.
     */
    private function createCursus(EntityManagerInterface $em): Cursus
    {
        $theme = $this->createTheme($em);

        $cursus = new Cursus();
        $cursus->setName('Test Cursus');
        $cursus->setTheme($theme);

        $em->persist($cursus);
        $em->flush();

        return $cursus;
    }

    /**
     * Create a minimal Lesson entity.
     * A Lesson MUST belong to a Cursus (NOT NULL constraint).
     */
    private function createLesson(EntityManagerInterface $em): Lesson
    {
        $cursus = $this->createCursus($em);

        $lesson = new Lesson();
        $lesson->setName('Test Lesson');
        $lesson->setPrice(1000);
        $lesson->setContent('test content');
        $lesson->setVideoUrl('https://example.com/video.mp4');
        $lesson->setCursus($cursus); 

        $em->persist($lesson);
        $em->flush();

        return $lesson;
    }

    /**
     * Unverified users must be redirected when trying to purchase a lesson.
     */
    public function testUnverifiedUserCannotPurchaseLesson(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createUnverifiedUser($em);
        $lesson = $this->createLesson($em);

        $client->loginUser($user);

        $client->request('GET', '/purchase/lesson/'.$lesson->getId());

        $this->assertResponseRedirects('/verify/notice');
    }

    /**
     * Unverified users must be redirected when trying to purchase a cursus.
     */
    public function testUnverifiedUserCannotPurchaseCursus(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createUnverifiedUser($em);
        $cursus = $this->createCursus($em);

        $client->loginUser($user);

        $client->request('GET', '/purchase/cursus/'.$cursus->getId());

        $this->assertResponseRedirects('/verify/notice');
    }
}
