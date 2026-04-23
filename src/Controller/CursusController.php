<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Repository\CursusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CursusController extends AbstractController
{
    #[Route('/cursus', name: 'app_cursus')]
    public function index(CursusRepository $cursusRepository): Response
    {
        return $this->render('cursus/index.html.twig', [
            'controller_name' => 'CursusController',
        ]);
    }

    #[Route('/cursus/{id}', name: 'cursus_show')]
    public function show(Cursus $cursus): Response
    {
        return $this->render('cursus/show.html.twig', [
            'cursus' => $cursus,
            'lessons' => $cursus->getLessons()
        ]);
    }
}
