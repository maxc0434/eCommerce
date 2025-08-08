<?php

namespace App\Controller;


use App\Service\Cart;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CartController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepository) //private=accessible que depuis l'interieur de cette classe (encapsulation, c'est une mesure de sécurité).
                                                                                       //readonly= cette propriété va être assigné qu'une seule fois dans le constructeur (acces au GET mais pas au SET)
                                                                                       //private readonly= propriété privé, accessible que dans cette classe, et qu'on ne peut l'assigner qu'une fois dans le constructeur
    {
    }
        #[Route('/cart', name: 'app_cart', methods : ['GET'])]
    public function index(SessionInterface $session, Cart $cart): Response
    {   
        $data = $cart->getCart($session);

        return $this->render('cart/cart.html.twig', [
            'items'=>$data['cart'],
            'total'=>$data['total'],
        ]);

    }

     #[Route('/cart/add/{id}', name: 'app_cart_new', methods : ['GET'])]
    // Définit une route pour ajouter un produit au panier
     public function addProductToCart(int $id, SessionInterface $session): Response //int= on oblige a ne prendre que des entiers
    //Methode pour ajouter un produit au panier, prend l'ID du produit et la session en paramètres
     {
        $cart = $session->get('cart', []);
        // Recupère le panier actuel de la session, ou un tableau vide si il n'existe pas
        if (!empty($cart[$id])){
            $cart[$id]++;
        }else{
            $cart[$id]=1;
        }
        // Si le produit est déjà dans le panier, incrémente sa quantité sinon l'ajoute avec une quantité de 1
        $session->set('cart',$cart);
        // Met à jour le panier dans la session
        return $this->redirectToRoute('app_cart');
        // Redirige vers la page du panier
     }

     #[Route('/cart/remove/{id}', name: 'app_cart_product_remove', methods : ['GET'])]
     public function removeProductToCart($id, SessionInterface $session): Response 
     {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])){
            unset($cart[$id]);
        }
        $session->set('cart',$cart);
        return $this->redirectToRoute('app_cart');
     }
    
     #[Route('/cart/remove', name: 'app_cart_remove', methods : ['GET'])]
     public function removeCart(SessionInterface $session): Response 
     {
        $session->set('cart', []);
        return $this->redirectToRoute('app_cart');
     }

}
