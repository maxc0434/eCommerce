<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SearchEngineController extends AbstractController
{
    #[Route('/search/engine', name: 'app_search_engine', methods: ['GET','POST'])]
    public function index(Request $request, ProductRepository $productRepo): Response
    {
        if ($request->isMethod('GET')){
            $data = $request->query->all();
            $word = $data['word'];
            $results = $productRepo->searchEngine($word);
        }
        if ($results ==[]){
            $this->addFlash('warning', 'pas de produit a ce nom');
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('search_engine/index.html.twig', [
            'products' => $results,
            'word' => $word
        ]);
    }
}

