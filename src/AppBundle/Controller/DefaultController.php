<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Category;
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
        $categories=$this->getDoctrine()->getRepository(Category::class)->findAll();
        return $this->render('/default/index.html.twig', ['categories' => $categories]);
    }

    public function primaryMenuCategoriesDropdownMenuAction()
    {
        $categories=$this->getDoctrine()->getRepository(Category::class)->findAll();
        return $this->render('default/primaryMenuCategoriesDropdownMenu.html.twig', ['categories' => $categories]);
    }


    public function sidebarWidgetAction() {
        $articles=$this->getDoctrine()->getRepository(Article::class)->findRecentArticles();
        //$articles=$this->getDoctrine()->getRepository(Article::class)->findAll();
        return $this->render('sidebarWidget.html.twig', ['articles' => $articles]);
    }
}
