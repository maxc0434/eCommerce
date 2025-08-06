<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategorieRepository;
use App\Repository\SubCategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods : ['GET'])]
    public function index(ProductRepository $productRepository, CategorieRepository $categorieRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $data = $productRepository->findBy([], ['id'=>"DESC"]);
        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            8
        );
        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
            'categories'=>$categorieRepository->findAll(),
        ]);

    }
    
    #[Route('/product/{id}/show', name: 'app_home_product_show', methods : ['GET'])]
    public function showProduct(Product $product, ProductRepository $productRepository, CategorieRepository $categorieRepository): Response
    {
            $lastProductsAdd = $productRepository->findBy([], ['id'=>'DESC'],5);
        return $this->render('home/show.html.twig', [
            'product'=>$product,
            'products'=>$lastProductsAdd,
            'categories'=>$categorieRepository->findAll()
        ]);
    }
    
    #[Route('/product/subcategory/{id}/filter', name: 'app_home_product_filter', methods : ['GET'])]
    public function filter($id, SubCategoryRepository $subCategoryRepository, CategorieRepository $CategoryRepository ): Response
    {
        //on récupère la sous catégorie correspondante àl'id passé en paramètre
        //on accède au product de cette sous catégorie
        $product = $subCategoryRepository->find($id)->getProducts();
        //on récupère la sous catégorie complète (cad l'objet)
        $subCategory = $subCategoryRepository->find($id);
        
        return $this->render('home/filter.html.twig', [
        'products'=> $product, //liste des produits liés à la sous catégorie
        'subCategory'=>$subCategory, // l'objet sous catégorie qui correspond à l'id
        'categories'=>$CategoryRepository->findAll()
        ]);
    }
}
