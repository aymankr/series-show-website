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
    public function account(AuthenticationUtils $authenticationUtils): Response
    {
        // Verify that a user is loged in
        if ($this->getUser() == null) {
            return $this->redirectToRoute('user_login');
        }

        return $this->render('account/account.html.twig', ['user' => $this->getUser()]);
    }
}
