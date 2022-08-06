<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProductController extends AbstractController
{
    #[Route('/', name: 'product_list')]
    public function listAction(ManagerRegistry $doctrine): Response
    {
        $products = $doctrine->getRepository(Product::class)->findAll();
        $categories = $doctrine->getRepository(Category::class)->findAll();
        return $this->render('product/index.html.twig', ['products' => $products,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/product/details/{id}", name="product_details")
     */
    public function detailsAction(ManagerRegistry $doctrine, $id)
    {
        $products = $doctrine->getRepository(Product::class)->find($id);

        return $this->render('product/details.html.twig', ['products' => $products
        ]);
    }

    /**
     * @Route("/product/delete/{id}", name="product_delete")
     */
    public function deleteAction(ManagerRegistry $doctrine, $id)
    {
        $em = $doctrine->getManager();
        $product = $em->getRepository(Product::class)->find($id);
        $em->remove($product);
        $em->flush();

        $this->addFlash(
            'error',
            'Product deleted'
        );

        return $this->redirectToRoute('product_list');
    }

    /**
     * @Route("/product/productByCat/{id}", name="productByCat")
     */
    public  function productByCatAction(ManagerRegistry $doctrine ,$id):Response
    {
        $category = $doctrine->getRepository(Category::class)->find($id);
        $products = $category->getProducts();
        $categories = $doctrine->getRepository('App:Category')->findAll();
        return $this->render('product/index.html.twig', ['products' => $products,
            'categories'=>$categories]);
    }

    /**
     * @Route("/product/create", name="product_create", methods={"GET","POST"})
     */
    public function createAction(ManagerRegistry$doctrine,Request $request, SluggerInterface $slugger)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // uplpad file
            $productImage = $form->get('productImage')->getData();
            if ($productImage) {
                $originalFilename = pathinfo($productImage->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$productImage->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $productImage->move(
                        $this->getParameter('productImages_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        'Cannot upload'
                    );// ... handle exception if something happens during file upload
                }
                $product->setProductImage($newFilename);
            }else{
                $this->addFlash(
                    'error',
                    'Cannot upload'
                );// ... handle exception if something happens during file upload
            }
            $em = $doctrine->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash(
                'notice',
                'Product Added'
            );
            return $this->redirectToRoute('product_list');
        }
        return $this->renderForm('product/create.html.twig', ['form' => $form,]);
    }

    /**
     * @Route("/product/edit/{id}", name="product_edit")
     */
    public function editAction(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $entityManager  = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);
        $form = $this->createForm(ProductType::class, @$product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $doctrine->getManager();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute('product_list', [
                'id' => $product->getId()
            ]);
        }
        return $this->renderForm('product/edit.html.twig', ['form' => $form,]);
    }
}
