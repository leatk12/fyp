<?php
// src/Controller/DefaultController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(): RedirectResponse
    {
        // Redirect from '/' to '/login'
        return $this->redirectToRoute('app_login');
    }
}
