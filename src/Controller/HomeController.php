<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/{vueRouting}', name: 'app_home', requirements: ['vueRouting' => '^(?!api|build|_profiler|_wdt).*$'], defaults: ['vueRouting' => null])]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
}