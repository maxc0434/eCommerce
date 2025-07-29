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
    #[Route('/category', name: 'app_category')]
    public function categorie(CategorieRepository $repo): Response
    {
        $categories = $repo->findAll();

        return $this->render('category/categories.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
        ]);
    }
    #[Route('/category/new', name: 'app_category_new')]
    public function addCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Categorie();

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($category);
            $entityManager->flush();

        return $this->redirectToRoute('app_category');
        }
        return $this->render('category/newCategory.html.twig',[
            'form'=>$form->createView()
        ]);
    }
    #[Route('/category/update/{id}', name :'app_update_category')]
    public function update(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $category = $entityManager->getRepository(Categorie::class)->find($id);
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        if ( $form->isSubmitted()&& $form->isValid()){
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category');
            
        }
        return $this->render('category/updateCategory.html.twig', [
        'form' => $form->createView(),
        ]);
    }
    #[Route('/delete/{id}', name :'app_delete_category')]
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $category = $entityManager->getRepository(Categorie::class)->find($id);
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('app_category');

    }

}