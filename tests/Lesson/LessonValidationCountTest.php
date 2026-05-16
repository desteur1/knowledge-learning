<?php

namespace App\Tests\Lesson;

use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Cursus;
use App\Entity\Theme;
use App\Entity\LessonValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LessonValidationCountTest extends WebTestCase
{
    private function createVerifiedUser(EntityManagerInterface $em): User
    {
        $user = new User();
        $user->setEmail('count_'.uniqid().'@example.com');
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

    private function validateLesson(EntityManagerInterface $em, User $user, Lesson $lesson): void
    {
        $validation = new LessonValidation();
        $validation->setUser($user);
        $validation->setLesson($lesson);
        $validation->setValidatedAt(new \DateTimeImmutable());

        $em->persist($validation);
        $em->flush();
        $em->refresh($user); //to update collections and avoid inconsistencies in the rest of the code
    }

    public function testCountZeroValidatedLessons(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);
        $lesson = $this->createLesson($em, $cursus, 'Lesson A');

        $this->assertSame(0, $user->countUserValidatedLessonsForCursus($cursus));
    }

    public function testCountOneValidatedLesson(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);
        $lesson = $this->createLesson($em, $cursus, 'Lesson A');

        $this->validateLesson($em, $user, $lesson);

        $this->assertSame(1, $user->countUserValidatedLessonsForCursus($cursus));
    }

    public function testCountMultipleValidatedLessons(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);

        $lesson1 = $this->createLesson($em, $cursus, 'Lesson 1');
        $lesson2 = $this->createLesson($em, $cursus, 'Lesson 2');
        $lesson3 = $this->createLesson($em, $cursus, 'Lesson 3');

        $this->validateLesson($em, $user, $lesson1);
        $this->validateLesson($em, $user, $lesson2);

        $this->assertSame(2, $user->countUserValidatedLessonsForCursus($cursus));
    }

    public function testValidationsFromOtherCursusAreIgnored(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);

        $cursusA = $this->createCursus($em);
        $cursusB = $this->createCursus($em);

        $lessonA1 = $this->createLesson($em, $cursusA, 'A1');
        $lessonB1 = $this->createLesson($em, $cursusB, 'B1');

        $this->validateLesson($em, $user, $lessonA1);
        $this->validateLesson($em, $user, $lessonB1);

        // Only 1 validation belongs to cursus A
        $this->assertSame(1, $user->countUserValidatedLessonsForCursus($cursusA));
    }
}
