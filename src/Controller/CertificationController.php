<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CertificationController extends AbstractController
{
    #[Route('/my-certifications', name: 'my_certifications')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('certification/index.html.twig', [
            'certifications' => $user->getCertifications(),
        ]);
    }
}
