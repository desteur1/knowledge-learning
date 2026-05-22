<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Entity\Cursus;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;

class PurchaseService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    // Purchase a cursus for a user
    public function purchaseCursus(User $user, Cursus $cursus): Order
    {
        // Check if user already purchased this cursus
        foreach ($user->getOrders() as $order) {
            foreach ($order->getOrderItems() as $item) {
                if ($item->getCursus() === $cursus) {
                    throw new \Exception('Tu as déjà acheté ce cursus.');
                }
            }
        }

        // Create order
        $order = new Order();
        $order->setUser($user);
        $order->setStatus('paid');
        $order->setTotal($cursus->getDynamicPrice());

        // Create order item
        $item = new OrderItem();
        $item->setOrder($order);
        $item->setCursus($cursus);
        $item->setPrice($cursus->getDynamicPrice());

        $order->addOrderItem($item);

        // Save
        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

    // Purchase a single lesson for a user
    public function purchaseLesson(User $user, Lesson $lesson): Order
    {

        // Check if user already purchased this lesson
        foreach ($user->getOrders() as $order) {
            foreach ($order->getOrderItems() as $item) {
                if ($item->getLesson() === $lesson) {
                    throw new \Exception('Tu as déjà acheté cette leçon.');
                }
            }
        }

        // Create order
        $order = new Order();
        $order->setUser($user);
        $order->setStatus('paid');
        $order->setTotal($lesson->getPrice());

        // Create order item
        $item = new OrderItem();
        $item->setOrder($order);
        $item->setLesson($lesson);
        $item->setPrice($lesson->getPrice());

        $order->addOrderItem($item);

        // Save
        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }
}
