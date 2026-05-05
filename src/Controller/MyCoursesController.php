<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MyCoursesController extends AbstractController
{
    #[Route('/mes-cours', name: 'app_my_courses')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // collect all purchased courses
        $purchasedCourses = $user->getPurchasedCursus();

        // filter unfinished courses first
        usort($purchasedCourses, function($a, $b) use ($user) {
            $va = $user->countValidatedLessonsForCursus($a);
            $ta = count($a->getLessons());

            $vb = $user->countValidatedLessonsForCursus($b);
            $tb = count($b->getLessons());

            // prioritize unfinished courses 
            return ($va == $ta) <=> ($vb == $tb);
        });

        return $this->render('my_courses/index.html.twig', [
            'courses' => $purchasedCourses
        ]);
    }
}
