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

    #[Route('/lesson/{id}/validate', name: 'lesson_validate')]
public function validate(Lesson $lesson, EntityManagerInterface $em): Response
{
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Vérifier accès
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

    // Vérifier si déjà validée
    foreach ($user->getLessonValidations() as $validation) {
        if ($validation->getLesson()->getId() === $lesson->getId()) {
            $this->addFlash('warning', 'Leçon déjà validée.');
            return $this->redirectToRoute('lesson_show', ['id' => $lesson->getId()]);
        }
    }

    // Créer la validation
    $validation = new LessonValidation();
    $validation->setUser($user);
    $validation->setLesson($lesson);
    $validation->setValidatedAt(new \DateTimeImmutable());

    $em->persist($validation);
    $em->flush();
     

    // IMPORTANT : to refresh user entity to get the latest lesson validations
     $em->refresh($user);
    // verify if all lessons of the cursus are validated to grant certification
    $cursus = $lesson->getCursus();
    $totalLessons = $cursus->getLessons()->count();
    $validatedLessons = $user->countValidatedLessonsForCursus($cursus);

if ($validatedLessons === $totalLessons && !$user->hasCertificationForCursus($cursus)) {
    $certification = new Certification();
    $certification->setUser($user);
    $certification->setCursus($cursus);
    $certification->setObtainedAt(new \DateTimeImmutable());

    $em->persist($certification);
    $em->flush();

    $this->addFlash('success', '🎓 Félicitations ! Vous avez obtenu la certification du cursus.');
}


    $this->addFlash('success', 'Leçon validée avec succès !');

    return $this->redirectToRoute('cursus_show', [
    'id' => $lesson->getCursus()->getId()
]);

}

}
