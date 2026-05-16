<?php

namespace App\Tests\Cursus;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CursusPriceTest extends WebTestCase
{
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

    private function addLesson(EntityManagerInterface $em, Cursus $cursus, int $price, int $position): Lesson
    {
        $lesson = new Lesson();
        $lesson->setName('Lesson '.$position);
        $lesson->setPrice($price);
        $lesson->setContent('content');
        $lesson->setVideoUrl('https://example.com/video.mp4');
        $lesson->setPosition($position);
        $lesson->setCursus($cursus);

        $cursus->addLesson($lesson);

        $em->persist($lesson);
        $em->flush();

        return $lesson;
    }

    public function testPriceWithNoLessons(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $cursus = $this->createCursus($em);

        $this->assertSame(0, $cursus->getDynamicPrice());
    }

    public function testPriceWithOneLessonNoDiscount(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $cursus = $this->createCursus($em);
        $this->addLesson($em, $cursus, 1000, 1);

        $this->assertSame(1000, $cursus->getDynamicPrice());
    }

    public function testPriceWithTwoLessonsDiscount(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $cursus = $this->createCursus($em);
        $this->addLesson($em, $cursus, 1000, 1);
        $this->addLesson($em, $cursus, 1000, 2);

        // total = 2000
        // discount = 2 lessons * 200 = 400
        // final = 1600
        $this->assertSame(1600, $cursus->getDynamicPrice());
    }

    public function testPriceWithSixLessonsMaxDiscount(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $cursus = $this->createCursus($em);

        for ($i = 1; $i <= 6; $i++) {
            $this->addLesson($em, $cursus, 1000, $i);
        }

        // total = 6000
        // discount = min(6 * 200, 1000) = 1000
        // final = 5000
        $this->assertSame(5000, $cursus->getDynamicPrice());
    }
}
