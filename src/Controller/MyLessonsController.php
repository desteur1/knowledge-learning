<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MyLessonsController extends AbstractController
{
    #[Route('/mes-lecons', name: 'app_my_lessons')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('my_lessons/index.html.twig', [
            'lessons' => $user->getPurchasedLessons()
        ]);
    }
}
