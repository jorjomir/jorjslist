<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Ad;
use AppBundle\Entity\Category;
use AppBundle\Entity\User;
use AppBundle\Form\AdType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdController extends Controller
{
    /**
     * @Route("/ads/{id}", name="adsByCategory")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adsByCategory($id) {
        $ads=$this->getDoctrine()->getRepository(Ad::class)->findAdsByCategory($id);

        return $this->render('ads/adsByCategory.html.twig', ['ads' => $ads]);
    }

    /**
     * @param Request $request
     *
     * @Route("/newAd", name="newAd")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAd(Request $request) {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            $this->addFlash('error', 'In order to create ad, please Log in!');
            return $this->redirectToRoute('login');
        }
        /** @var Ad $ad */
        $ad= new Ad();
        $form= $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em=$this->getDoctrine()->getManager();

            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form->get('images')->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            $ad->setImages($fileName);


            $ad->setAuthor($currentUser->getUsername());
            $em_category = $this->getDoctrine()->getManager();
            $data=$request->request->all();
            $categoryId=intval($data["ad"]["categoryId"]);
            $category = $em_category->getRepository('AppBundle:Category')->findOneBy(array('id' => $categoryId));
            $ad->setCategoryId($category);
            $em->persist($ad);
            $em->flush();
            $this->addFlash('success', 'Ad created successfuly!');
            return $this->redirectToRoute('myAds');
        }

        return $this->render('ads/newAd.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @Route("/ad/{id}", name="viewAd")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAd($id) {
        $em = $this->getDoctrine()->getManager();
        $ad=$em->getRepository('AppBundle:Ad')->find($id);
        $views=$ad->getViews();
        $ad->setViews($views+1);
        $em->persist($ad);
        $em->flush();
        return $this->render('ads/viewAd.html.twig', ['ad' => $ad]);
    }

    /**
     * @Route("/myAds", name="myAds")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function myAds() {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        $em = $this->getDoctrine()->getManager();
        $ads=$em->getRepository('AppBundle:Ad')->findBy(array("author" => $currentUser->getUsername()));
        return $this->render('ads/myAds.html.twig', ['ads' => $ads]);
    }

    /**
     * @Route("/ad/edit/{id}", name="editAd")
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAd($id, Request $request) {
        $ad=$this->getDoctrine()->getRepository(Ad::class)->find($id);
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser->getUsername()!=$ad->getAuthor()) {
            return $this->redirectToRoute('index');
        }

        if($ad===null) {
            return $this->redirectToRoute("myAds");
        }

        $form=$this->createForm(AdType::class, $ad);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em=$this->getDoctrine()->getManager();
            $em->persist($ad);
            $em->flush();
            $this->addFlash('success', 'You have successfuly edited ' . $ad->getTitle() . '!');
            return $this->redirect("/ad/".$ad->getId());
        }
        return $this->render('ads/editAd.html.twig',
            array('ad' => $ad,
                'form' => $form->createView()));
    }

    /**
     * @Route("/ad/delete/{id}", name="deleteAd")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAd($id)
    {
        $em = $this->getDoctrine()->getManager();
        $ad=$em->getRepository('AppBundle:Ad')->find($id);
        $em->remove($ad);
        $em->flush();
        $this->addFlash('success', 'Ad Deleted Successfully!');
        return $this->redirectToRoute('myAds');
    }

    /**
     * @Route("/allCategories", name="allCategories")
     */
    public function allCategories() {

        $categories=$this->getDoctrine()->getRepository(Category::class)->findAll();

        return $this->render('default/allCategories.html.twig', ['categories' => $categories]);
    }
}
