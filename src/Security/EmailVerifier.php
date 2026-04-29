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
        //  IMPORTANT : on passe l'ID dans les paramètres de la route
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()] // ← obligatoire pour route /verify/email/{id}
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    public function handleEmailConfirmation(Request $request): void
    {
        // On récupère l'ID depuis la route
        $userId = $request->attributes->get('id');

        if (!$userId) {
            throw new \LogicException('Missing user ID in verification link.');
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            throw new \LogicException('User not found.');
        }

        // Validation de la signature
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            $userId,
            $user->getEmail()
        );

        // Activation du compte
        $user->setIsVerified(true);
        $this->entityManager->flush();
    }
}
