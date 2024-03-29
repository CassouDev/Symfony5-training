<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Collection as ConstraintsCollection;

class ProductController extends AbstractController
{
    /**
     * @Route("/{slug}", name="product_category")
     */
    public function category($slug, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$category) {
            // throw new NotFoundHttpException("La catégorie demandée n'éxiste pas"); IDEM
            throw $this->createNotFoundException("La catégorie demandée n'éxiste pas");
        }

        return $this->render('product/category.html.twig', [
            'slug' => $slug,
            'category' => $category
        ]);
    }

    /**
     *  @Route("/{category_slug}/{slug}", name="product_show")
     */
    public function show($slug, ProductRepository $productRepository)
    {
        $product = $productRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$product) {
            throw $this->createNotFoundException("Le produit demandé n'éxiste pas");
        }

        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * @Route("admin/product/{id}/edit", name="product_edit")
     */
    public function edit($id, ProductRepository $productRepository, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ValidatorInterface $validator)
    {
        // VALIDATION DU PRODUIT non conventionnelle, sans passer par les assert de "Form/ProductType.php"
        // $product = new Product;
        // // $product->setName('Cassandre');
        // // $product->setPrice(200);

        // $resultat = $validator->validate($product); // validation via "validation/product.yaml" ou via l'entité Product

        // if ($resultat->count() > 0) {
        //     dd('Il y a des erreurs', $resultat);
        // } else {
        //     dd('tout va bien');
        // }

        // $client = [
        //     'nom' => 'Michelet',
        //     'prenom' => 'Cassandre',
        //     'voiture' => [
        //         'marque' => 'Kangoo',
        //         'couleur' => 'Grise'
        //     ]
        // ];

        // $collection = new ConstraintsCollection([
        //     'nom' => new NotBlank(['message' => "Le nom ne doit pas être vide"]),
        //     'prenom' => [
        //         new NotBlank(['message' => "Le prénom ne doit pas être vide"]),
        //         new Length(['min' => 3, 'minMessage' => "Le message ne doit pas faire moins de 3 caractères"])
        //     ],
        //     'voiture' => new ConstraintsCollection([
        //         'marque' => new NotBlank(['message' => "La marque de la voiture est obligatoire"]),
        //         'couleur' => new NotBlank(['message' => "La couleur de la voiture est obligatoire"])
        //     ])
        // ]);

        // $resultat = $validator->validate($client, $collection);

        $product = $productRepository->find($id);

        $form = $this->createForm(ProductType::class, $product, [
            "validation_groups" => ["Default", "with-price"]
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getData());
            $product->setSlug(strtolower($slugger->slug($product->getName())));
            $em->flush();

            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug()
            ]);
        }

        $formView = $form->createView();

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'formView' => $formView
        ]);
    }

    /**
     * @Route("/admin/product/create", name="product_create")
     */
    public function create(Request $request, SluggerInterface $slugger, EntityManagerInterface $em)
    {
        $product = new Product;

        $form = $this->createForm(ProductType::class, $product, [
            "validation_groups" => ["Default", "with-price"]
        ]); // $this->createFormBuilder = créé un formulaire vide, non existant

        $form->handleRequest($request); // gère la requête et créé un nouvel objet Product à partir des hamps du formulaire

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setSlug(strtolower($slugger->slug($product->getName())));

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug()
            ]);
        }

        $formView = $form->createView();

        return $this->render('product/create.html.twig', [
            'formView' => $formView
        ]);
    }
}
