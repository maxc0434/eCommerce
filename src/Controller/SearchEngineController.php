<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SearchEngineController extends AbstractController
{
    #[Route('/search/engine', name: 'app_search_engine', methods: ['POST'])]
    public function index(Request $request, ProductRepository $productRepo): Response
    {
        if ($request->isMethod('POST')){
            $data = $request->request->all();
            $word = $data['word'];
            dd($word);
            $results = $productRepo->searchEngine($word);
        }
        return $this->render('search_engine/index.html.twig', [
            'products' => '$results',
        ]);
    }
}
