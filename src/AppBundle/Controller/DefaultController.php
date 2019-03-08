<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Ad;
use AppBundle\Entity\Article;
use AppBundle\Entity\Category;
use AppBundle\Entity\ImageSlider;
use AppBundle\Form\SearchType;
use AppBundle\Repository\AdRepository;
use AppBundle\Repository\ArticleRepository;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMapBundle\IvoryGoogleMapBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request)
    {
        $ads=$this->getDoctrine()->getRepository(Ad::class)->findRecentAds();
        $categories=$this->getDoctrine()->getRepository(Category::class)->findAll();
        $images=$this->getDoctrine()->getRepository(ImageSlider::class)->recentImages();
        return $this->render('/default/index.html.twig', ['categories' => $categories,
            'images' => $images, 'ads' => $ads]);
    }

    /**
     * @Route("/documentation", name="documentation")
     */
    public function documentation() {
        return $this->render('default/documentation.html.twig');
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

    /**
     * @Route ("/search", name="search")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request) {
        $defaultData = ['message' => 'Type your message here'];
        $form= $this->createForm(SearchType::class, $defaultData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $query=$data['search_text'];

            //$this->redirect('/searchResults' . '/' . $query);
            //$this->redirectToRoute($this->searchResults($data['search_text']));
            return $this->forward('AppBundle\Controller\DefaultController::searchResults', ['query' => $query]);
        }
        return $this->render('search.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @param $query
     *
     * @Route("/searchResults", name="searchResults")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchResults($query) {

        $adsToShow=array();
        $em = $this->getDoctrine()->getManager();
        $ads=$em->getRepository(Ad::class)->findAll();
        foreach ($ads as $ad) {
            if((strpos(strtolower($ad->getSummary()), strtolower($query)) !== false ) ||
                (strpos(strtolower($ad->getTitle()), strtolower($query)) !== false ) ||
                (strpos(strtolower($ad->getDescription()), strtolower($query)) !== false )) {
                $adsToShow[]=$ad;
            }

        }

        return $this->render('searchResults.html.twig', array('ads' => $adsToShow, 'query' => $query));
    }
}
