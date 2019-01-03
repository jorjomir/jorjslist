<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('/default/index.html.twig');
    }


    public function sidebarWidgetAction() {
        $articles=$this->getDoctrine()->getRepository(Article::class)->findRecentArticles();
        //$articles=$this->getDoctrine()->getRepository(Article::class)->findAll();
        return $this->render('sidebarWidget.html.twig', ['articles' => $articles]);
    }

}
