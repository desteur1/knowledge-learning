<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Service\LessonProgressService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LessonController extends AbstractController
{
    #[Route('/lesson/{id}', name: 'lesson_show')]
    public function show(Lesson $lesson): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $hasAccess =
            $user->userHasPurchasedLesson($lesson) ||
            $user->userHasPurchasedCursus($lesson->getCursus());

        if (!$hasAccess) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette leçon.");
        }

        $validatedCount = $user->countUserValidatedLessonsForCursus($lesson->getCursus());
        $total = count($lesson->getCursus()->getLessonsOrdered());

        return $this->render('lesson/show.html.twig', [
            'lesson'         => $lesson,
            'user'           => $user,
            'validatedCount' => $validatedCount,
            'total'          => $total,
        ]);
    }

    #[Route('/lesson/{id}/next-locked', name: 'lesson_next_locked')]
    public function nextLocked(Lesson $lesson, LessonProgressService $progress): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cursus = $lesson->getCursus();
 
        // Access control: user must have access to either the lesson or the cursus
        if (
            !$user->userHasPurchasedLesson($lesson) &&
            !$user->userHasPurchasedCursus($cursus)
        ) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette leçon.");
        }

        // Validate lesson
        $progress->validateLesson($user, $lesson);

        // Check certification
        $progress->checkCertification($user, $lesson);

        // Navigation
        $next = $lesson->getNextLesson();
        if ($next) {
            return $this->redirectToRoute('lesson_show', ['id' => $next->getId()]);
        }

        return $this->redirectToRoute('cursus_show', ['id' => $cursus->getId()]);
    }
}
