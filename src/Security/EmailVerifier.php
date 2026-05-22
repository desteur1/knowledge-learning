<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    ) {}

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        
        // IMPORTANT: pass the user ID in the route parameters
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()] // mandatory to pass the ID for later validation
        );
        
        // add signed url and expiration details to the email context
        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    public function handleEmailConfirmation(Request $request): void
    {
        // collect id from the URL
        $userId = $request->attributes->get('id');

        if (!$userId) {
            throw new \LogicException('Missing user ID in verification link.');
        }

          // find the user and validate email
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            throw new \LogicException('User not found.');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            $userId,
            $user->getEmail()
        );

        // Activate the user account by setting isVerified to true
        $user->setIsVerified(true);
        $this->entityManager->flush();
    }
}
