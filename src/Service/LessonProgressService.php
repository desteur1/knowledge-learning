<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\LessonValidation;
use App\Entity\Certification;
use Doctrine\ORM\EntityManagerInterface;

class LessonProgressService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function validateLesson(User $user, Lesson $lesson): LessonValidation
    {
        // Already validated?
        foreach ($user->getLessonValidations() as $v) {
            if ($v->getLesson()->getId() === $lesson->getId()) {
                return $v;
            }
        }

        $validation = new LessonValidation();
        $validation->setUser($user);
        $validation->setLesson($lesson);
        $validation->setValidatedAt(new \DateTimeImmutable());

        // Maintain inverse side of the relation (required for Doctrine and tests consistency)
        $user->addLessonValidation($validation);

        $this->em->persist($validation);
        $this->em->flush();

        $this->em->refresh($user);

        return $validation;
    }

    public function checkCertification(User $user, Lesson $lesson): ?Certification
    {
        // Check if the user has validated all lessons of the cursus, and if he doesn't already have a certif for it
        $cursus = $lesson->getCursus();
        $validatedCount = $user->countUserValidatedLessonsForCursus($cursus);
        $total = count($cursus->getLessonsOrdered());
    
         // If all lessons are validated and the user doesn't already have the certification, create it
        if ($validatedCount === $total && !$user->userHasCertificationForCursus($cursus)) {

            $cert = new Certification();
            $cert->setUser($user);
            $cert->setCursus($cursus);
            $cert->setObtainedAt(new \DateTimeImmutable());

            // Maintain inverse side of the relation (required for Doctrine and tests consistency)
            $user->addCertification($cert);

            $this->em->persist($cert);
            $this->em->flush();
            $this->em->refresh($user);

            return $cert;
        }

        return null;
    }
}
