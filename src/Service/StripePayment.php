<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripePayment
{
    private $redirectUrl;

    public function __construct()
    {
        Stripe::setApiKey($_SERVER['STRIPE_SECRET_KEY']);
        Stripe::setApiVersion('2025-07-30.basil');
    }

    public function startPayment($cart, $shippingCost, $orderId)
    {
        // dd($cart);
        // dd($orderId);
        $cartProducts = $cart['cart'];
        $products = [
            [
                'qte' => 1,
                'price' => $shippingCost,
                'name' => "Frais de Livraison"
            ]
        ];

        foreach ($cartProducts as $value) {
            $productItem = [];
            $productItem['name'] = $value['product']->getName();
            $productItem['price'] = $value['product']->getPrice();
            $productItem['qte'] = $value['quantity'];
            $products[] = $productItem;
        }

        $session = Session::create([
            'line_items' => [
                array_map(fn(array $product) => [
                    'quantity' => $product['qte'],
                    'price_data' => [
                        'currency' => 'Eur',
                        'product_data' => [
                            'name' => $product['name']
                        ],
                        'unit_amount' => $product['price'] * 100,
                    ],
                ], $products)

            ],
            'mode' => 'payment',
            'cancel_url' => 'http://127.0.0.1:8000/pay/cancel',
            'success_url' => 'http://127.0.0.1:8000/pay/success',
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ['FR'],
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'orderid' => $orderId,
                ]
            ]
        ]);

        $this->redirectUrl = $session->url;
    }
    public function getStripeRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
