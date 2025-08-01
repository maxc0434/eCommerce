<?php

namespace App\Controller;


use App\Entity\Product;
use App\Form\ProductType;
use App\Entity\AddProductHistory;
use App\Form\AddProductHistoryType;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/editor/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

#region ADD
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response //SluggerInterface est une interface d'un composant string qui va servir a changer la chaine de caractère en 'slug' càd de simplifier les noms en caractère safe pour le code (enlève les espaces, majuscules, accents)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData(); //Permet de recuperer le fichier de l'image et son contenu qui sera upload

            if($image){ //si une image a bien été envoyé
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME); // on recupere le nom d'origine sans l'extension (.jpeg)
                $safeImageName = $slugger->slug($originalName); // on va 'slugger' (= remplacer accent, majuscule, espace, etc... par "_") 
                $newFileImageName = $safeImageName.'_'.uniqid().'.'.$image->guessExtension(); // ajoute un id unique et donc l'extension

                try { // deplace le fichier, ici l'image, dans le dossier défini dans le parametre imagedirectory
                    $image->move 
                        ($this->getParameter('image_directory'),
                        $newFileImageName);

                } catch (FileException $exception) {} //gestion d'un message d'erreur si besoin
                    $product->setImage($newFileImageName);// on sauvegarde le nom du fichier dans son entité
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $stockHistory = new AddProductHistory();
            $stockHistory->setQuantity($product->getStock());
            $stockHistory->setProduct($product);
            $stockHistory->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($stockHistory);
            $entityManager->flush();

            $this->addFlash('success', 'Vous avez bien CREE votre Produit');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
#endregion 
#region SHOW
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
#endregion 
#region EDIT
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }
#endregion
#region DELETE
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
#endregion

    #[Route('/{id}', name: 'app_product_stock_add', methods: ['POST'])]
    public function stockAdd($id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $stockAdd = new AddProductHistory();
        $form =$this->createForm(AddProductHistoryType::class, $stockAdd);
        $form->handleRequest($request);

        return
    }
}


