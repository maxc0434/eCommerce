<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/** 
*Classe de test pour le controleur de sécurité (gestion de la connexion/deconnexion).
*Elle hérite de WebTextCase pour utiliser les outils de test fournis par Symfony. 
*/ 

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageLoadsForAnonymousUser(): void
    {
        // 1. Crée un client HTTP simulé qui agit comme un navigateur pour faire des requetes
        $client = static::createClient();

        //2. Effectue une requete GET vers l'URL '/login'
        //Le Crawler ($crawler) est retourné pour permettre l'inspection du contenu HTML.
        $crawler = $client->request('GET', '/login');

        //3. Assertion : Vérifie que la réponse du serveur a reussi (code HTTP 2xx)
        // C'est une verif générique (ex: 200, 201, 204...).
        $this->assertResponseIsSuccessful(); //Vérifi juste que le /login répond 200.

        // 4. Assertion : Vérifie spécifiquement que le code de statut HTTP est exactement 200 (OK)
        // $this->assertResponseStatusCodeSame(200);

        // 4.bis Vérifie l'existence du formulaire principal de connexion
        //en utilisant son selecteur CSS (ici, la classe 'loginForm')
        $this->assertSelectorExists('form.loginForm');

        // 5. Vérifie l'existence du champ de saisie pour email
        //(recherche un input avec l'attribut name="email")
        $this->assertSelectorExists('input[name="_username"]');

        // 6. Vérifie l'existence du champ de saisie pour mdp
        //(recherche un input avec l'attribut name="password")
        $this->assertSelectorExists('input[name="_password"]');

        // 7. Vérifie qu'il y a un élément h1 (titre principal)
        // sur la page qui contient le texte "Connexion"
        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    public function testLoginRedirectsIfAlreadyAuthenticated(): void {

        // 1. Crée un client HTTP simulé
        $client = static::createClient();

        // 2. Accede au conteneur de services du kernel de test 
        $container = static::getContainer();

        // 3. Recupere le service qui gère la récupération des users
        $userProvider = $container->get('security.user.provider.concrete.app_user_provider_test');

        // 4. Charge l'objet utilisateur réel en utilisant un identifiant 
        $user = $userProvider->loadUserByIdentifier('test@test.com');

        // 5. Simule la connexion de cet objet utilisateur au client HTTP
        // a partir de la, le client est considéré comme authentifi& par Symfony.
        $client->loginUser($user);

        // 6. Effectue une requete GET vers l'URL '/login' alors que le client est connecté
        $client->request('GET', '/login');

        // 7. Assertion: Vérifie que la réponse du serveur est une redirection (code HTTP 3xx) vers une autre page        
        $this->assertResponseRedirects(); 
    }

    public function testLogoutWorks(): void {

        // 1. Crée un client HTTP simulé
        $client = static::createClient();

        // 2. Accede au conteneur de services du kernel de test 
        $container = static::getContainer();

        // 3. Recupere le service qui gère la récupération des users
        $userProvider = $container->get('security.user.provider.concrete.app_user_provider_test');

        // 4. Charge l'objet utilisateur réel en utilisant un identifiant 
        $user = $userProvider->loadUserByIdentifier('test@test.com');

        // 5. Simule la connexion de cet objet utilisateur au client HTTP
        // a partir de la, le client est considéré comme authentifi& par Symfony.
        $client->loginUser($user);

        // 6. Effectue une requete GET vers l'URL '/login' alors que le client est connecté
        $client->request('GET', '/logout');

        // 7. Assertion: Vérifie que la réponse du serveur est une redirection (code HTTP 3xx) vers une autre page        
        $this->assertResponseRedirects(); 
    }
}
