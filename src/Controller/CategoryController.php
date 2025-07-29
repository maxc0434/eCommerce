<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function categorie(CategorieRepository $repo): Response
    {
        $categories = $repo->findAll();
        return $this->render('category/categorie.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
        ]);
    }
}
