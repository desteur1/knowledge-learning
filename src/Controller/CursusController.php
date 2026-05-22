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
        $user = $this->getUser();

        // Calculate remaining price for the cursus if user is logged in, otherwise null
        $remainingPrice = null;

        if ($user) {
            $remainingPrice = $user->calculateRemainingPriceForCursus($cursus);
        }

       // Access control: if user is logged in, they must have access to either the cursus or at least one lesson
        return $this->render('cursus/show.html.twig', [
            'cursus' => $cursus,
            'lessons' => $cursus->getLessons(),
            'user' => $this->getUser(),
            'remainingPrice' => $remainingPrice,
        ]);
    }
}
