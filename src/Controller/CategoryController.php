<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategoryFormType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category')]
    public function categorie(CategorieRepository $repo): Response
    {
        $categories = $repo->findAll();

        return $this->render('category/categories.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
        ]);
    }
    #[Route('/admin/category/new', name: 'app_category_new')] // déclaration d'une route via l'url /admin/category/new 
    public function addCategory(Request $request, EntityManagerInterface $entityManager): Response 
    // Méthode du controle qui gere la création d'une nouvelle catégorie
    // Elle a pour paramètre le gestionnaire d'entité pour la base de donnée et la requete HTTP; Elle renvoie une réponse 
    {
        $category = new Categorie();
        //Création d'une nouvelle instance de l'entity Categorie

        $form = $this->createForm(CategoryFormType::class, $category);
        //création d'un formulaire basé sur la classe CategoryFormType, lié à l'objet category
        $form->handleRequest($request);
        //Traite les données envoyées dans la requête pour remplir le formulaire

        if ($form->isSubmitted() && $form->isValid()) {
        //Verifie si le formulaire est soumis et que les données sont valides

            $entityManager->persist($category);
            //prépar l'objet catégorie a être envoyé en base de donnée
            $entityManager->flush();
            //execute la requête d'insertion en BDD, et sauvegarde ces infos en base de donnée

        $this->addFlash('success', 'Vous avez bien CREE votre catégorie');
        //ajoute un msg flash

        return $this->redirectToRoute('app_category');
        //redirige l'utilisateur vers la route indiquée
        }
        return $this->render('category/newCategory.html.twig',[
        //affiche le formulaire s'il est soumis et valide, mais ré affiche le formulaire si formulaire non valide 
            'form'=>$form->createView()
            //affiche le formulaire
        ]);
    }
    #[Route('/admin/category/update/{id}', name :'app_update_category')]
    public function update(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $category = $entityManager->getRepository(Categorie::class)->find($id);
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        if ( $form->isSubmitted()&& $form->isValid()){
            $entityManager->persist($category);
            $entityManager->flush();

         $this->addFlash('success', 'Vous avez bien MODIFIE votre catégorie');

            return $this->redirectToRoute('app_category');
            
        }
        return $this->render('category/updateCategory.html.twig', [
        'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/delete/{id}', name :'app_delete_category')]
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $category = $entityManager->getRepository(Categorie::class)->find($id);
        $entityManager->remove($category);
        $entityManager->flush();

     $this->addFlash('info', 'Vous avez bien SUPPRIME votre catégorie');

        return $this->redirectToRoute('app_category');

    }
}