<?php

namespace App\Tests\Lesson;

use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Cursus;
use App\Entity\Theme;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LessonAccessTest extends WebTestCase
{
    private function createVerifiedUser(EntityManagerInterface $em): User
    {
        $user = new User();
        $user->setEmail('user_'.uniqid().'@example.com');
        $user->setPassword('password');
        $user->setIsVerified(true);
        $user->setRoles(['ROLE_CLIENT']);

        $em->persist($user);
        $em->flush();
        return $user;
    }

    private function createTheme(EntityManagerInterface $em): Theme
    {
        $theme = new Theme();
        $theme->setName('Theme Test');
        $em->persist($theme);
        $em->flush();
        return $theme;
    }

    private function createCursus(EntityManagerInterface $em): Cursus
    {
        $theme = $this->createTheme($em);

        $cursus = new Cursus();
        $cursus->setName('Cursus Test');
        $cursus->setTheme($theme);

        $em->persist($cursus);
        $em->flush();
        return $cursus;
    }

    private function createLesson(EntityManagerInterface $em, Cursus $cursus): Lesson
    {
        $lesson = new Lesson();
        $lesson->setName('Lesson Test');
        $lesson->setPrice(1000);
        $lesson->setContent('content');
        $lesson->setVideoUrl('https://example.com/video.mp4');
        $lesson->setCursus($cursus);
        $lesson->setPosition(1);

        $cursus->addLesson($lesson);
        

        $em->persist($lesson);
        $em->flush();
        return $lesson;
    }

    public function testGuestCannotAccessLesson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/lesson/1');

        $this->assertResponseRedirects('/login');
    }

    public function testUserCannotAccessLessonIfNotPurchased(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);
        $lesson = $this->createLesson($em, $cursus);


        $client->loginUser($user);

        $client->request('GET', '/lesson/'.$lesson->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUserCanAccessLessonIfPurchased(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);
        $lesson = $this->createLesson($em, $cursus);

        // simulate purchase
        $order = new Order();
        $order->setUser($user)->setStatus('paid');
        $item = new OrderItem();
        $item->setLesson($lesson)->setPrice(1000);
        $order->addOrderItem($item);
        $em->persist($order);
        $em->flush();
        $em->refresh($user);
        $em->refresh($order);


        $client->loginUser($user);

        $client->request('GET', '/lesson/'.$lesson->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testUserCanAccessLessonIfCursusPurchased(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);
        $lesson = $this->createLesson($em, $cursus);

        // simulate purchase of cursus
        $order = new Order();
        $order->setUser($user)->setStatus('paid');
        $item = new OrderItem();
        $item->setCursus($cursus)->setPrice(2000);
        $order->addOrderItem($item);
        $em->persist($order);
        $em->flush();
        $em->refresh($user);
        $em->refresh($order);

        $client->loginUser($user);

        $client->request('GET', '/lesson/'.$lesson->getId());

        $this->assertResponseIsSuccessful();
    }
}
