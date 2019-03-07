<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Category;
use AppBundle\Entity\User;
use AppBundle\Form\ArticleType;
use AppBundle\Form\CategoryType;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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

        /** @var Article $article */
        $article= new Article();
        $form= $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form->get('image')->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            // Move the file to the directory where brochures are stored
            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'brochure' property to store the PDF file name
            // instead of its contents
            $article->setImage($fileName);

            $em=$this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'Article created successfuly!');
            return $this->redirectToRoute('adminAllArticles');
        }
        return $this->render('admin/createArticle.html.twig',
            array('form' => $form->createView()));
    }


    /**
     * @Route("/admin/allArticles", name="adminAllArticles")
     */
    public function adminAllArticles() {
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

        $re = '/(...)(.jpeg)/m';

        if(preg_match($re, $article->getImage())) {
            /** @var Filesystem $fileSystem */
            $fileSystem = new Filesystem();
            $fileSystem->remove($this->get('kernel')->getRootDir() . "\..\web\uploads\images\\" . $article->getImage());
        }
        $em->remove($article);
        $em->flush();
        $this->addFlash('success', 'Article Deleted Successfully!');
        return $this->redirectToRoute('adminAllArticles');
    }

    /**
     * @Route("/allArticles", name="allArticles")
     */
    public function allArticles() {

        $articles=$this->getDoctrine()->getRepository(Article::class)->findRecentArticles();
        return $this->render('default/allArticles.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route("/article/{id}", name="viewArticle")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewArticle($id) {
        $em = $this->getDoctrine()->getManager();
        $article=$em->getRepository('AppBundle:Article')->find($id);
        $views=$article->getViews();
        $article->setViews($views+1);
        $em->persist($article);
        $em->flush();
        return $this->render('default/viewArticle.html.twig', ['article' => $article]);
    }



}
