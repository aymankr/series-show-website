<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="user_account")
     */
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // Verify that a user is loged in
        if (!$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();
            
            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
        }

        return $this->render('account/account.html.twig', ['user' => $this->getUser()]);
    }
}
