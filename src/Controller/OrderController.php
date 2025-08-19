<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Service\Cart;
use App\Form\OrderType;
use App\Entity\OrderProducts;
use App\Service\StripePayment;
use Symfony\Component\Mime\Email;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class OrderController extends AbstractController
{

    public function __construct(private MailerInterface $mailer) {}

    #[Route('/order', name: 'app_order')]
    public function index(
        ProductRepository $productRepository,
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        Cart $cart
    ): Response {
        $data = $cart->getCart($session);
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        $stripeRedirectUrl = "";
        if ($form->isSubmitted() && $form->isValid()) {
            // if ($order->isPayOnDelivery()) {

            if (!empty($data['total'])) {  //on verifie que le panier n'est pas vide
                $order->setTotalPrice($data['total']);
                $order->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($order);
                $entityManager->flush();

                // dd($data['cart']); varDie and Dump : permet de voir dans la console le contenue du panier

                foreach ($data['cart'] as $value) {
                    $orderProduct = new OrderProducts();
                    $orderProduct->setOrder($order);
                    $orderProduct->setProduct($value['product']);
                    $orderProduct->setQuantity($value['quantity']);
                    $entityManager->persist($orderProduct);
                    $entityManager->flush();
                }

                $paymentStripe = new StripePayment();
                $paymentStripe->startPayment($data);
                $stripeRedirectUrl = $paymentStripe->getStripeRedirectUrl();
                $html = $this->renderView('mail/orderConfirm.html.twig', [
                    'order' => $order
                ]);

                return $this->redirect($stripeRedirectUrl);
            }
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'total' => $data['total'],
        ]);


        return $this->redirectToRoute('order_message');
        $session->set('cart', []);

        $email = (new Email())
            ->from('motoshop@gmail.com')
            ->to($order->getEmail())
            ->subject('Confirmation de réception de commande')
            ->html($html);
        $this->mailer->send($email);
    }


    #[Route('/order_message', name: 'order_message')]
    public function orderMessage(): Response
    {
        return $this->render('order/orderMessage.html.twig');
    }



    #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();

        return new Response(json_encode(['status' => 200, "message" => "on", 'content' => $cityShippingPrice]));
    }


    #[Route('/editor/order/show', name: 'app_orders_show')]
    public function getAllOrder(OrderRepository $orderRepo, PaginatorInterface $paginator, Request $request): Response
    {
        $orders = $orderRepo->findBy([], ['id' => "DESC"]);
        $orders = $paginator->paginate(
            $orders,
            $request->query->getInt('page', 1),
            3
        );
        //dd($orders);
        return $this->render('order/orders.html.twig', [
            "orders" => $orders
        ]);
    }

    #[Route('/editor/order/{id}/is-completed/update', name: 'app_orders_is-completed-update')]
    public function isCompletedUpdate($id, OrderRepository $orderRepo, EntityManagerInterface $entityManager)
    {
        $order = $orderRepo->find($id);
        $order->setIsCompleted(true);
        $entityManager->flush();
        $this->addFlash('success', 'Modification effectuée');
        return $this->redirectToRoute('app_orders_show');
    }

    #[Route('/editor/order/{id}/delete', name: 'app_orders_delete')]
    public function deleteOrder($id, OrderRepository $orderRepo, EntityManagerInterface $entityManager)
    {
        $order = $orderRepo->find($id);
        $entityManager->remove($order);
        $entityManager->flush();
        $this->addFlash('danger', 'Commande Supprimée');
        return $this->redirectToRoute('app_orders_show');
    }
}
