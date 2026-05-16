<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Entity\OrderItem;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class PurchaseController extends AbstractController
{
    #[Route('/purchase/cursus/{id}', name: 'purchase_cursus')]
    public function purchaseCursus(Cursus $cursus, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
         // check if user is logged in, if not redirect to login page
        if (!$user) return $this->redirectToRoute('app_login');

        if (!$user->isVerified()) {
                $this->addFlash('error', 'Vous devez activer votre compte pour acheter.');
                return $this->redirectToRoute('app_verify_notice');
            }

        // check if user already bought this cursus
       foreach ($user->getOrders() as $order) {
         if ($order->getStatus() !== 'paid') continue;

         // check if order contains this cursus
         foreach ($order->getOrderItems() as $item) {
            if ($item->getCursus() && $item->getCursus()->getId() === $cursus->getId()) {
                $this->addFlash('warning', 'Vous avez déjà acheté ce cursus.');
                return $this->redirectToRoute('cursus_show', ['id' => $cursus->getId()]);
            }
        }
    }


      // create order and order item
        $order = new Order();
        $order->setUser($user);
        $order->setStatus('paid'); // SIMULATION

        $item = new OrderItem();
        // $item->setOrder($order);
        $item->setCursus($cursus);
        $item->setPrice($cursus->getDynamicPrice()); // Set the price from the cursus

        $order->addOrderItem($item);
        
        $em->persist($order);
        $em->flush();

        //  confirmation message
        $this->addFlash('success', 'Votre achat a bien été enregistré !');

        return $this->redirectToRoute('cursus_show', ['id' => $cursus->getId()]);
    }

    #[Route('/purchase/lesson/{id}', name: 'purchase_lesson')]
    public function purchaseLesson(Lesson $lesson, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) return $this->redirectToRoute('app_login');
        
        if (!$user->isVerified()) {
                $this->addFlash('error', 'Vous devez activer votre compte pour acheter.');
                return $this->redirectToRoute('app_verify_notice');
            }

        // check if user already bought this lesson
        foreach ($user->getOrders() as $order) {
          if ($order->getStatus() !== 'paid') continue;

          foreach ($order->getOrderItems() as $item) {
            if ($item->getLesson() && $item->getLesson()->getId() === $lesson->getId()) {
                $this->addFlash('warning', 'Vous avez déjà acheté cette leçon.');
                return $this->redirectToRoute('lesson_show', ['id' => $lesson->getId()]);
            }
        }
    }

        $order = new Order();
        $order->setUser($user);
        $order->setStatus('paid'); // SIMULATION

        $item = new OrderItem();
        // $item->setOrder($order);
        $item->setLesson($lesson);
        $item->setPrice($lesson->getPrice()); // Set the price from the 

        $order->addOrderItem($item);

        $em->persist($order);
        $em->flush();


        $this->addFlash('success', 'Votre achat a bien été enregistré !');

        return $this->redirectToRoute('lesson_show', ['id' => $lesson->getId()]);
    }
}
