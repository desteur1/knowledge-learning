<?php

namespace App\Tests\Lesson;

use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Cursus;
use App\Entity\Theme;
use App\Entity\LessonValidation;
use App\Entity\Certification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LessonValidationTest extends WebTestCase
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

    private function createLesson(EntityManagerInterface $em, Cursus $cursus, string $name): Lesson
    {
        $lesson = new Lesson();
        $lesson->setName($name);
        $lesson->setPrice(1000);
        $lesson->setContent('content');
        $lesson->setVideoUrl('https://example.com/video.mp4');
        $lesson->setCursus($cursus);

        $em->persist($lesson);
        $em->flush();
        return $lesson;
    }

    public function testLessonValidationCreatesRecord(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);
        $lesson = $this->createLesson($em, $cursus, 'Lesson A');

        // simulate purchase
        $order = new \App\Entity\Order();
        $order->setUser($user)->setStatus('paid');
        $item = new \App\Entity\OrderItem();
        $item->setLesson($lesson)->setPrice(1000);
        $order->addOrderItem($item);
        $em->persist($order);
        $em->flush();

        $client->loginUser($user);

        // validate lesson
        $client->request('GET', '/lesson/'.$lesson->getId().'/next-locked');

        // check validation created
        $validation = $em->getRepository(LessonValidation::class)->findOneBy([
            'user' => $user,
            'lesson' => $lesson
        ]);

        $this->assertNotNull($validation);
        $this->assertInstanceOf(\DateTimeImmutable::class, $validation->getValidatedAt());
    }

    public function testLessonCannotBeValidatedTwice(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);
        $lesson = $this->createLesson($em, $cursus, 'Lesson B');

        // simulate purchase
        $order = new \App\Entity\Order();
        $order->setUser($user)->setStatus('paid');
        $item = new \App\Entity\OrderItem();
        $item->setLesson($lesson)->setPrice(1000);
        $order->addOrderItem($item);
        $em->persist($order);
        $em->flush();

        $client->loginUser($user);

        // first validation
        $client->request('GET', '/lesson/'.$lesson->getId().'/next-locked');

        // second validation
        $client->request('GET', '/lesson/'.$lesson->getId().'/next-locked');

        $validations = $em->getRepository(LessonValidation::class)->findBy([
            'user' => $user,
            'lesson' => $lesson
        ]);

        $this->assertCount(1, $validations);
    }

    public function testCertificationCreatedWhenAllLessonsValidated(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);

        // create 2 lessons
        $lesson1 = $this->createLesson($em, $cursus, 'Lesson 1');
        $lesson2 = $this->createLesson($em, $cursus, 'Lesson 2');

        // simulate purchase of cursus
        $order = new \App\Entity\Order();
        $order->setUser($user)->setStatus('paid');
        $item = new \App\Entity\OrderItem();
        $item->setCursus($cursus)->setPrice(2000);
        $order->addOrderItem($item);
        $em->persist($order);
        $em->flush();

        $client->loginUser($user);

        // validate lesson 1
        $client->request('GET', '/lesson/'.$lesson1->getId().'/next-locked');

        // validate lesson 2 → should trigger certification
        $client->request('GET', '/lesson/'.$lesson2->getId().'/next-locked');

        $cert = $em->getRepository(Certification::class)->findOneBy([
            'user' => $user,
            'cursus' => $cursus
        ]);

        $this->assertNotNull($cert);
        $this->assertInstanceOf(\DateTimeImmutable::class, $cert->getObtainedAt());
    }
}
