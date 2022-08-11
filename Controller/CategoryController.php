<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryController extends AbstractController
{
    #[Route('/cate', name: 'app_category')]
    public function listAction(ManagerRegistry $doctrine): Response
    {
      //  $products = $doctrine->getRepository(Product::class)->findAll();
        $categories = $doctrine->getRepository(Category::class)->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/category/detailsCategory/{id}", name="category_details")
     */
    public function detailsAction(ManagerRegistry $doctrine, $id)
    {
        $category = $doctrine->getRepository(Category::class)->find($id);

        return $this->render('category/detailsCategory.html.twig', [ 'category' => $category
        ]);
    }

    /**
     * @Route("/category/delete/{id}", name="category_delete")
     */
    public function deleteAction(ManagerRegistry $doctrine, $id)
    {
        $em = $doctrine->getManager();
        $Category = $em->getRepository(Category::class)->find($id);
        $em->remove($Category);
        $em->flush();

        $this->addFlash(
            'error',
            'Deleted Success'
        );

        return $this->redirectToRoute('app_category');
    }

    /**
     * @Route("/category/create", name="category_create", methods={"GET","POST"})
     */
    public function createAction(ManagerRegistry$doctrine,Request $request, SluggerInterface $slugger)
    {
        $Category = new Category();
        $form = $this->createForm(CategoryType::class, $Category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doctrine->getManager();
            $em->persist($Category);
            $em->flush();

            $this->addFlash(
                'notice',
                'Category Added Success'
            );
            return $this->redirectToRoute('app_category');
        }
        return $this->renderForm('category/create.html.twig', ['form' => $form,]);
    }

    /**
     * @Route("/category/edit/{id}", name="category_edit")
     */
    public function editAction(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $entityManager  = $doctrine->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);
        $form = $this->createForm(CategoryType::class, @$category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $doctrine->getManager();
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute('app_category', [
                'id' => $category->getId()
            ]);
        }
        return $this->renderForm('category/edit.html.twig', ['form' => $form,]);
    }

    /**
     * @Route("/category/categoryByCat/{id}", name="categoryByCat")
     */
    public  function categoryByCatAction(ManagerRegistry $doctrine ,$id):Response
    {
        $category = $doctrine->getRepository(Category::class)->find($id);
        //$products = $category->getProducts();
        $categories = $doctrine->getRepository('App:Category')->findAll();
        return $this->render('category/index.html.twig', [
            'categories'=>$categories]);
    }
}
