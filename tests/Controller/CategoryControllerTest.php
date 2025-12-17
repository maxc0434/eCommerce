<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    public function testCategoriesListPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/category');

        $this->assertResponseIsSuccessful();
    }

    public function testNewCategoryFormDisplaysAndCreatesCategory(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/category/new');

        // Page accessible + bon titre
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Ajouter une catégorie');

        // CategoryFormType -> "category_form" par défaut
        $form = $crawler
            ->selectButton('sauvegarder')
            ->form([
                'category_form[name]' => 'Catégorie de test',
            ]);

        $client->submit($form);

        // Redirection vers la liste
        $this->assertResponseRedirects('/admin/category');
        $crawler = $client->followRedirect();

        // Flash de succès
        $this->assertSelectorTextContains('.alert.alert-success', 'CREE');
    }

    public function testUpdateCategory(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        // Catégorie existante
        $category = new Categorie();
        $category->setName('Ancien nom');
        $em->persist($category);
        $em->flush();

        $crawler = $client->request('GET', '/admin/category/update/'.$category->getId());

        $this->assertResponseIsSuccessful();

        $form = $crawler
            ->selectButton('updater')
            ->form([
                'category_form[name]' => 'Nouveau nom',
            ]);

        $client->submit($form);

        $this->assertResponseRedirects('/admin/category');
        $crawler = $client->followRedirect();

        // Flash de succès de modification
        $this->assertSelectorTextContains('.alert.alert-success', 'MODIFIE');

        // Vérifie la mise à jour en base
        $em->refresh($category);
        $this->assertSame('Nouveau nom', $category->getName());
    }

    public function testDeleteCategory(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        // Catégorie à supprimer
        $category = new Categorie();
        $category->setName('A supprimer');
        $em->persist($category);
        $em->flush();

        $client->request('GET', '/admin/delete/'.$category->getId());

        $this->assertResponseRedirects('/admin/category');
        $crawler = $client->followRedirect();

        // Flash d’info de suppression
        $this->assertSelectorTextContains('.alert.alert-info', 'SUPPRIME');

        // Vérifie que la catégorie est supprimée
        $deleted = $em->getRepository(Categorie::class)->find($category->getId());
        $this->assertNull($deleted);
    }
}
