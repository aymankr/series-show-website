<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    // The paths of the pages to render
    private static $regiserPage = 'security/register.html.twig';
    private static $loginPage = 'security/login.html.twig';

    /**
     * @Route("/register", name="userRegister")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User(); 
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Register the user and update the changes made in the database
            $entityManager->persist($this->setupUserRegistration($user, $userPasswordHasher));
            $entityManager->flush();

            return $this->redirectToRoute('userLogin');
        }

        return $this->render(SecurityController::$regiserPage, [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="userLogin")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(SecurityController::$loginPage, ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="userLogout")
     */
    public function logout(): void
    {
        $this->session_destroy;
    }

    /**
     * Encore the password of the user and other informations needed to the registration.
     */
    private function setupUserRegistration(User $userToRegister, UserPasswordHasherInterface $userPasswordHasher): User
    {
        // encode the plain password
        $userToRegister->setPassword(
            $userPasswordHasher->hashPassword(
                $userToRegister,
                $userToRegister->get('plainPassword')->getData()
            )
        );

        // add the other informations needed to the registration
        $userToRegister->setRegisterDate(new \DateTime());
        return $userToRegister;
    }
}
