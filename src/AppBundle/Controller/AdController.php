<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Ad;
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
            return $this->redirectToRoute('index');
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
        $em = $this->getDoctrine()->getManager();
        $ads=$em->getRepository('AppBundle:Ad')->findBy(array("author" => $currentUser->getUsername()));
        return $this->render('ads/myAds.html.twig', ['ads' => $ads]);
    }
}
