<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepo): Response
    {

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $userRepo->findAll(),
            //on créer la variable "frontend" users qui aura pour valeur toutes les valeurs du Repository et qu'on consomme immédiatement 
        ]);
    }

    #[Route('/admin/user/update/{id}', name: 'app_user_change_role')]
    public function updateRole(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setRoles(['ROLE_EDITOR', 'ROLE_USER']);
        $entityManager->flush();

        $this->addFlash('success', 'vous avez bien modifié le role');
        return $this->redirectToRoute('app_user');
    }

    #[Route('/admin/user/remove/editor/{id}', name: 'app_user_remove_editor_role')]
    public function removeRole(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setRoles([]);
        $entityManager->flush();

        $this->addFlash('success', 'vous avez bien supprimé le role');
        return $this->redirectToRoute('app_user');
    }
    #[Route('/admin/user/remove/{id}', name: 'app_user_remove')]
    public function userRemove($id, EntityManagerInterface $entityManager, User $user, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'vous avez bien supprimé le role');
        return $this->redirectToRoute('app_user');
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/order/history', name: 'app_user_order_history')]
    public function orderHistory(#[CurrentUser] ?User $user, UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $orders = $user->getOrderHistory();
        foreach ($orders as $order) {
            dump($order);
        }

        $orders = $paginator->paginate(
            $orders,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('order/orderHistory.html.twig', [
            'orders' => $orders,
            'user' => $user,
        ]);
    }
}
