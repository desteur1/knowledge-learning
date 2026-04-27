<?php

namespace App\Controller;

use App\Entity\Lesson;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LessonController extends AbstractController
{
    #[Route('/lesson', name: 'app_lesson')]
    public function index(): Response
    {
        return $this->render('lesson/index.html.twig', [
            'controller_name' => 'LessonController',
        ]);
    }

    #[Route('/lesson/{id}', name: 'lesson_show')]
    public function show(Lesson $lesson): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $hasAccess = false;

        foreach ($user->getOrders() as $order) {
            if ($order->getStatus() !== 'paid') {
                continue;
            }

            foreach ($order->getOrderItems() as $item) {

                // Buy lesson
                if ($item->getLesson() && $item->getLesson()->getId() === $lesson->getId()) {
                    $hasAccess = true;
                    break;
                }

                // Buy course
                if ($item->getCursus() && $item->getCursus()->getId() === $lesson->getCursus()->getId()) {
                    $hasAccess = true;
                    break;
                }
            }

            if ($hasAccess) {
            break;
           }
        }

        if (!$hasAccess) {
            throw $this->createAccessDeniedException('You must purchase this lesson.');
        }

        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
            'user' => $user,
        ]);
    }
}
