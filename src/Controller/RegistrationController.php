<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            //  AJOUT OBLIGATOIRE : attribuer un rôle par défaut
            $defaultRole = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_CLIENT']);
            $user->setRole($defaultRole);

            $entityManager->persist($user);
            $entityManager->flush();



            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('smartbrief.me@gmail.com', 'Knowledge Learning'))
                    ->to((string) $user->getEmail())
                    ->subject('Activez votre compte')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context([])
            );

             $this->addFlash('success', 'Un email de confirmation vous a été envoyé.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email/{id}', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {

        // validate email confirmation link, sets User::isVerified=true and persists
       
        try {
            $this->emailVerifier->handleEmailConfirmation($request);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre email a bien été vérifié !');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/verify/notice', name: 'app_verify_notice')]
    public function verifyNotice(): Response
    {
        return $this->render('security/verify_notice.html.twig');
    }

}
