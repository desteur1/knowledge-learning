<?php

namespace App\Controller;

use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class ResendVerificationEmailController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/verify/resend', name: 'app_resend_verification_email')]
    public function resend(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->addFlash('success', 'Votre email est déjà vérifié.');
            return $this->redirectToRoute('app_profile');
        }

        // create email
        $email = (new TemplatedEmail())
             ->from(new Address('smartbrief.me@gmail.com', 'Knowledge Learning'))
            ->to($user->getEmail())
            ->subject('Vérifiez votre adresse email')
            ->htmlTemplate('registration/confirmation_email.html.twig');

        // send email confirmation
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            $email
        );

        $this->addFlash('success', 'Email de vérification renvoyé.');
        return $this->redirectToRoute('app_profile');
    }
}

