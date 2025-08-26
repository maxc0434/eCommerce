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
        OrderRepository $orderRepository,
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        Cart $cart
    ): Response {
        $data = $cart->getCart($session);
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            if (!empty($data['total'])) {  //on verifie que le panier n'est pas vide
                $totalPrice = $data['total'] + $order->getCity()->getShippingCost();
                $order->setTotalPrice($totalPrice);
                $order->setCreatedAt(new \DateTimeImmutable());
                $order->setIsPaymentCompleted(0);
                $order->setUser($this->getUser());
                $entityManager->persist($order);
                $entityManager->flush();

                // dd($data['cart']); varDie and Dump : permet de voir dans la console le contenue du panier
                
                foreach ($data['cart'] as $value) {
                    $orderProduct = new OrderProducts();
                    $orderProduct->setOrder($order);
                    $orderProduct->setProduct($value['product']);
                    $orderProduct->setQuantity($value['quantity']);
                    $entityManager->persist($orderProduct);
                }
                $entityManager->flush();
                $entityManager->refresh($order);
                if ($order->isPayOnDelivery()) {
                    
                    $html = $this->renderView('mail/orderConfirm.html.twig', [
                        'order' => $order
                    ]);
                    $email = (new Email())
                        ->from('motoshop@gmail.com')
                        ->to($order->getEmail())
                        ->subject('Confirmation de réception de commande')
                        ->html($html);
                    $this->mailer->send($email);
                    foreach ($order->getOrderProducts() as $orderProduct) {
                        $quantity = $orderProduct->getQuantity();
                        $product= $orderProduct->getProduct();
                        $stock = $product-> getStock();
                        $updateStock = $stock - $quantity;
                        if ($updateStock < 0){
                            $this->addFlash("error", "One of cart products is not available anymore");
                            return $this->redirectToRoute("app_order");
                        }
                        $product->setStock($updateStock);
                        $session->set('cart', []);
                        
                    }
                    $entityManager->flush();


                    return $this->redirectToRoute('order_message');
                }

                $paymentStripe = new StripePayment();
                $shippingCost = $order->getCity()->getShippingCost();
                $paymentStripe->startPayment($data, $shippingCost, $order->getId());
                $stripeRedirectUrl = $paymentStripe->getStripeRedirectUrl();

                return $this->redirect($stripeRedirectUrl);
            }
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'total' => $data['total'],
        ]);
    }


    #[Route('/order/order_message', name: 'order_message')]
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


    #[
        Route('/editor/order', name: 'app_orders_show_all'),
        Route('/editor/order/{type}', name: 'app_orders_show')
    ]
    public function getAllOrder(?string $type, OrderRepository $orderRepo, PaginatorInterface $paginator, Request $request): Response
    {
        if($type == 'is-completed'){
        $orders = $orderRepo->findBy(['isCompleted'=>1], ['id' => "DESC"]);
        }
        else if($type == 'pay-on-stripe-not-delivered'){
            $orders = $orderRepo->findBy(['isCompleted'=>null,'payOnDelivery'=>0,'isPaymentCompleted'=>1],['id'=>'DESC']);
        }else if($type == 'pay-on-stripe-is-delivered'){
            $orders = $orderRepo->findBy(['isCompleted'=>1,'payOnDelivery'=>0,'isPaymentCompleted'=>1],['id'=>'DESC']);
        }else if($type == 'no_delivery'){
            $orders = $orderRepo->findBy(['isCompleted'=>null,'payOnDelivery'=>0,'isPaymentCompleted'=>0],['id'=>'DESC']);
        }
        else {
            $orders = $orderRepo->findAll();
        }

        $orders = $paginator->paginate(
            $orders,
            $request->query->getInt('page', 1),
            10
        );
        //dd($orders);
        return $this->render('order/orders.html.twig', [
            "orders" => $orders,
            "type" => $type
        ]);
    }

    #[Route('/editor/order/{id}/is-completed/update', name: 'app_orders_is-completed-update')]
    public function isCompletedUpdate($id, OrderRepository $orderRepo, EntityManagerInterface $entityManager)
    {
        $order = $orderRepo->find($id);
        $order->setIsCompleted(true);
        $entityManager->flush();
        $this->addFlash('success', 'Modification effectuée');
        return $this->redirectToRoute('app_orders_show', ["type" => "is-completed"]);
    }

    #[Route('/editor/order/{id}/delete', name: 'app_orders_delete'),
    Route('/editor/order/{id}/delete/{type}', name: 'app_orders_delete_type')]

    public function deleteOrder(?string  $type, $id, OrderRepository $orderRepo, EntityManagerInterface $entityManager)
    {
        $order = $orderRepo->find($id);
        $entityManager->remove($order);
        $entityManager->flush();
        $this->addFlash('danger', 'Commande Supprimée');
        if (isset($type)){
            return $this->redirectToRoute('app_orders_show', ["type" => $type]);
        }
        return $this->redirectToRoute('app_orders_show_all');
    }
}
