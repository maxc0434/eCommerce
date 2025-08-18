<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BillController extends AbstractController
{
    #[Route('/bill', name: 'app_bill')]
    public function index($id, OrderRepository $orderRepo): Response
    {
        $order = $orderRepo->find($id);

        return $this->render('bill/index.html.twig', [
            'order' => '$order',
        ]);
    }
}
