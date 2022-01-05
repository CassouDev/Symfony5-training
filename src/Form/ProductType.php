<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use App\Form\Type\PriceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\DataTransformer\CentimesTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Tapez le nom du produit'],
                'required' => false
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => 'Description courte',
                'attr' => [
                    'placeholder' => 'Tapez une description courte mais parlante pour le visiteur'
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix du produit',
                'attr' => [
                    'placeholder' => 'Tapez le prix du produit'
                ],
                // 'divide' => false // si je ne veux pas que cela divise et remultiplie par 100 dans le PriceType::class dans Type/PriceType
                'divisor' => 100,  // $builder->get('price')->addModelTransformer(new CentimesTransformer); idem
                'required' => false
            ])
            ->add('mainPicture', UrlType::class, [
                'label' => 'Image du produit',
                'attr' => ['placeholder' => 'Tapez une url d\'image']
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie --',
                'class' => Category::class, // Donne une liste d'objet Category
                // 'choice_label' => 'name' // Nom de la propriété de la catégorie que je veux comme name
                'choice_label' => function (Category $category) {
                    return strtoupper($category->getName()); // Pour la mettre en majuscules
                }
            ]);

        // $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
        //     $product = $event->getData();

        //     if ($product->getPrice() !== null) {
        //         $product->setPrice($product->getPrice() * 100);
        //     }
        // });

        // $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) { // avant qu'on positionne les données sur le formulaire
        //     $form = $event->getForm();

        //     /** @var Product */
        //     $product = $event->getData();

        //     if ($product->getPrice() !== null) {
        //         $product->setPrice($product->getPrice() / 100);
        //     }

        // if ($product->getId() === null) {
        //     $form->add('category', EntityType::class, [
        //         'label' => 'Catégorie',
        //         'placeholder' => 'Choisir une catégorie --',
        //         'class' => Category::class, // Donne une liste d'objet Category
        //         // 'choice_label' => 'name' // Nom de la propriété de la catégorie que je veux comme name
        //         'choice_label' => function (Category $category) {
        //             return strtoupper($category->getName()); // Pour la mettre en majuscules
        //         }
        //     ]);
        // }
        // });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
