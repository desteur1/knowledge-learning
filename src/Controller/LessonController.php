<?php

namespace App\Controller;

use App\Entity\LessonValidation;
use App\Entity\Lesson;
use App\Entity\Certification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class LessonController extends AbstractController
{
    #[Route('/lesson/{id}', name: 'lesson_show')]
    public function show(Lesson $lesson, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // access control : only users who bought the cursus or the lesson can access
        $hasAccess = false;

        foreach ($user->getOrders() as $order) {
            if ($order->getStatus() !== 'paid') continue;

            foreach ($order->getOrderItems() as $item) {
                if ($item->getLesson() && $item->getLesson()->getId() === $lesson->getId()) {
                    $hasAccess = true;
                }
                if ($item->getCursus() && $item->getCursus()->getId() === $lesson->getCursus()->getId()) {
                    $hasAccess = true;
                }
            }
        }

        if (!$hasAccess) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette leçon.");
        }

        // create certification if all lessons are validated
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
    public function nextLocked(Lesson $lesson, EntityManagerInterface $em): Response
    {
        $user   = $this->getUser();
        $cursus = $lesson->getCursus();
 
        // validate current lesson if not already validated
        $alreadyValidated = false;
        foreach ($user->getLessonValidations() as $v) {
            if ($v->getLesson()->getId() === $lesson->getId()) {
                $alreadyValidated = true;
                break;
            }
        }

        if (!$alreadyValidated) {
            $validation = new LessonValidation();
            $validation->setUser($user);
            $validation->setLesson($lesson);
            $validation->setValidatedAt(new \DateTimeImmutable());

            $em->persist($validation);
            $em->flush();

            $em->refresh($user); // pour mettre à jour les collections et éviter les incohérences dans la suite du code
        }

        // validation progression cursus
        $validatedCount = $user->countUserValidatedLessonsForCursus($cursus);
        $total          = count($cursus->getLessonsOrdered());

        // 3. Certification if all lessons validated and no cert yet
        if ($validatedCount == $total && !$user->userHasCertificationForCursus($cursus)) {
            $cert = new Certification();
            $cert->setUser($user);
            $cert->setCursus($cursus);
            $cert->setObtainedAt(new \DateTimeImmutable());

            $em->persist($cert);
            $em->flush();
        }

        // 4. Navigation : if user has access to next lesson, go to it, else go back to cursus page
        if ($user->userHasPurchasedCursus($cursus)) {
            $next = $lesson->getNextLesson();

            if ($next) {
                return $this->redirectToRoute('lesson_show', [
                    'id' => $next->getId(),
                ]);
            }

            return $this->redirectToRoute('cursus_show', [
                'id' => $cursus->getId(),
            ]);
        }

        return $this->redirectToRoute('cursus_show', [
            'id' => $cursus->getId(),
        ]);
    }
}
