<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\User;
use AppBundle\Form\CategoryType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/admin/createCategory", name="createCategory")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createCategory(Request $request) {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        /** @var Category $category */
        $category= new Category();
        $form= $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em=$this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'Успешно създадохте Категория!');
            return $this->redirectToRoute('adminAllCategories');
        }

        return $this->render('admin/category/createCategory.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @Route("/admin/allCategories", name="adminAllCategories")
     */
    public function adminAllCategories() {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        $categories=$this->getDoctrine()->getRepository(Category::class)->findAll();

        return $this->render('admin/category/allCategories.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/admin/category/delete/{id}", name="deleteCategory")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteCategory($id)
    {

        $em = $this->getDoctrine()->getManager();
        $category=$em->getRepository('AppBundle:Category')->find($id);
        $em->remove($category);
        $em->flush();
        $this->addFlash('success', 'Успешно изтрихте категорията!');
        return $this->redirectToRoute('adminAllCategories');
    }
}
