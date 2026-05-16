<?php

namespace App\Tests\Purchase;

use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Cursus;
use App\Entity\Theme;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PurchaseFlowTest extends WebTestCase
{
    private function createVerifiedUser(EntityManagerInterface $em): User
    {
        $user = new User();
        $user->setEmail('verified_'.uniqid().'@example.com');
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
        $cursus->setName('Test Cursus');
        $cursus->setTheme($theme);

        $em->persist($cursus);
        $em->flush();

        return $cursus;
    }

    private function createLesson(EntityManagerInterface $em, Cursus $cursus): Lesson
    {

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

    public function testVerifiedUserCanPurchaseLesson(): void
    {
        
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $cursus = $this->createCursus($em);
        $user = $this->createVerifiedUser($em);
        $lesson = $this->createLesson($em, $cursus);

        $client->loginUser($user);

        $client->request('GET', '/purchase/lesson/'.$lesson->getId());

        // Must redirect to lesson_show
        $this->assertResponseRedirects('/lesson/'.$lesson->getId());

        // Check Order created
        $order = $em->getRepository(Order::class)->findOneBy(['user' => $user]);
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->getStatus());

        // Check OrderItem created
        $items = $order->getOrderItems();
        $this->assertCount(1, $items);

        $item = $items->first();
        $this->assertSame($lesson->getId(), $item->getLesson()->getId());
        $this->assertSame(1000, $item->getPrice());
    }

    public function testVerifiedUserCanPurchaseCursus(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $user = $this->createVerifiedUser($em);
        $cursus = $this->createCursus($em);

        $client->loginUser($user);

        $client->request('GET', '/purchase/cursus/'.$cursus->getId());

        // Must redirect to cursus_show
        $this->assertResponseRedirects('/cursus/'.$cursus->getId());

        // Check Order created
        $order = $em->getRepository(Order::class)->findOneBy(['user' => $user]);
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->getStatus());

        // Check OrderItem created
        $items = $order->getOrderItems();
        $this->assertCount(1, $items);

        $item = $items->first();
        $this->assertSame($cursus->getId(), $item->getCursus()->getId());
        $this->assertSame($cursus->getDynamicPrice(), $item->getPrice());
    }
}
