<?php

namespace App\Controller;

use App\Entity\Certification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;

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

    #[Route('/certification/{id}/download', name: 'certification_download')]
    public function download(Certification $certification): Response
    {
        // Access control: user must own the certification
         if ($certification->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette certification.");
        }


        $dompdf = new \Dompdf\Dompdf();
        
        // Generate PDF content using a Twig template
        $html = $this->renderView('certification/pdf.html.twig', [
            'certification' => $certification,
            'user' => $this->getUser(),
        ]);

        // Load the HTML content into Dompdf and render the PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();

            return new Response(
        $pdfContent,
        200,
        [ // Set appropriate headers for PDF download
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="certification.pdf"'
        ]
    );
        }

}
