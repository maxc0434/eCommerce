<?php

namespace App\Controller;

use Stripe\Stripe;
use Symfony\Component\Mime\Email;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class StripeController extends AbstractController
{
    #[Route('/stripe', name: 'app_stripe')]
    public function index(): Response
    {
        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/pay/success', name: 'app_stripe_success')]
    public function success(Session $session): Response
    {
        $session->set('cart', []);
        return $this->render('stripe/stripeSuccess.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/pay/cancel', name: 'app_stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/stripeCancel.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/stripe/notify', name: "app_stripe_notify")]
    public function stripeNotify(Request $request, OrderRepository $orderRepo, MailerInterface $mailer, EntityManagerInterface $entityManager, ProductRepository $productRepo): Response
    {

        Stripe::setApiKey($_SERVER['STRIPE_SECRET_KEY']);

        $endpoint_secret = $_SERVER['ENDPOINT_KEY'];
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;

                $fileName = 'stripe-detail-' . uniqid() . '.txt';
                $orderId = $paymentIntent->metadata->orderid;
                $order = $orderRepo->find($orderId);
                $order->setIsPaymentCompleted(1);

                foreach ($order->getOrderProducts() as $orderProduct) {
                    $quantity = $orderProduct->getQuantity();
                    $product= $orderProduct->getProduct();
                    $stock = $product-> getStock();

                    $updateStock = $stock - $quantity;
                    $product->setStock($updateStock);
                }


                $entityManager->flush();
                file_put_contents($fileName, $orderId);

                $order = $orderRepo->findOneBy(['id' => $orderId]);
                $html = $this->renderView('mail/orderConfirm.html.twig', [
                    'order' => $order
                ]);
                $email = (new Email())
                    ->from('motoshop@gmail.com')
                    ->to($order->getEmail())
                    ->subject('Confirmation de réception de commande')
                    ->html($html);
                $mailer->send($email);

                // return $this->redirectToRoute('order_message');

                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object;
                break;
            default:

                break;
        }

        return new Response('Evenement reçu avec succès', 200);
    }
}
