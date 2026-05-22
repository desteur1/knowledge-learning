<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Service\PurchaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PurchaseController extends AbstractController
{
    #[Route('/purchase/cursus/{id}', name: 'purchase_cursus')]
    public function purchaseCursus(Cursus $cursus, PurchaseService $purchaseService): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isVerified()) {
            return $this->redirectToRoute('app_verify_notice');
        }
         
        // Check if user already has access to the cursus
        try {
            $purchaseService->purchaseCursus($user, $cursus);
            $this->addFlash('success', 'Cursus acheté avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('cursus_show', ['id' => $cursus->getId()]);
    }

    #[Route('/purchase/lesson/{id}', name: 'purchase_lesson')]
    public function purchaseLesson(Lesson $lesson, PurchaseService $purchaseService): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isVerified()) {
            return $this->redirectToRoute('app_verify_notice');
        }

        // Check if user already has access to the lesson
        if ($user->hasAccessToLesson($lesson)) {
            $this->addFlash('warning', 'Vous avez déjà accès à cette leçon.');
            return $this->redirectToRoute('lesson_show', ['id' => $lesson->getId()]);
        }
        // Attempt to purchase the lesson, handle exceptions for insufficient funds or other issues
        try {
            $purchaseService->purchaseLesson($user, $lesson);
            $this->addFlash('success', 'Leçon achetée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('lesson_show', ['id' => $lesson->getId()]);
    }
}
