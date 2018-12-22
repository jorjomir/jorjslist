<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\User;
use AppBundle\Form\ArticleType;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/admin/createArticle", name="createArticle")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createArticle(Request $request)
    {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        $article= new Article();
        $form= $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em=$this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'Article created successfuly!');
            return $this->redirectToRoute('allArticles');
        }
        return $this->render('admin/createArticle.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @Route("/admin/allArticles", name="allArticles")
     */
    public function allArticles() {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        $articles=$this->getDoctrine()->getRepository(Article::class)->findAll();

        return $this->render('admin/allArticles.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/admin/article/edit/{id}", name="editArticle")
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editArticle($id, Request $request) {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        $article=$this->getDoctrine()->getRepository(Article::class)->find($id);

        if($article===null) {
            return $this->redirectToRoute("allArticles");
        }

        $form=$this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em=$this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'Article edited successfuly!');
            return $this->redirectToRoute('allArticles');
        }
        return $this->render('admin/editArticle.html.twig',
            array('article' => $article,
                'form' => $form->createView()));
    }

    /**
     * @Route("/admin/article/delete/{id}", name="deleteArticle")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteArticle($id)
    {

        $em = $this->getDoctrine()->getManager();
        $article=$em->getRepository('AppBundle:Article')->find($id);
        $em->remove($article);
        $em->flush();
        $this->addFlash('success', 'Article Deleted Successfully!');
        return $this->redirectToRoute('allArticles');
    }

}
