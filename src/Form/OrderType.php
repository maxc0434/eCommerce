<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, [
                'attr'=>[
                    'class'=> 'form form-control',
                    'value'=>'Test'

                ]
            ])
            ->add('lastName', null, [
                'attr'=>[
                    'class'=> 'form form-control',
                    'value'=>'Test'

                ]
            ])
            ->add('email', null, [
                'attr'=>[
                    'class'=> 'form form-control',
                    'value'=>'Test@gmail.com'

                ]
            ])
            ->add('phone', null, [
                'attr'=>[
                    'class'=> 'form form-control',
                    'value'=>'06060606'

                ]
            ])
            ->add('adress', null, [
                'attr'=>[
                    'class'=> 'form form-control',
                    'value'=>'Test'

                ]
            ])
            // ->add('createdAt', null, [
            //     'widget' => 'single_text',
            // ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'attr'=>[
                    'class'=> 'form form-control'
                ]
            ])
            ->add('payOnDelivery', null, [
                'attr'=>[
                    'class'=> 'mx-2'
                ],
                'label'=>'Payez à la Livraison',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
