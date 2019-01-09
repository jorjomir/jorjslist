<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ImageSlider;
use AppBundle\Entity\User;
use AppBundle\Form\ImageSliderType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;

class ImageSliderController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/admin/addImageToSlide", name="addImageToSlide")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addImageToSlide(Request $request)
    {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        /** @var ImageSlider $image */
        $image= new ImageSlider();
        $form= $this->createForm(ImageSliderType::class, $image);

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
            $image->setImage($fileName);

            $em=$this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();
            $this->addFlash('success', 'Image added successfuly!');
            return $this->redirectToRoute('allImagesInSlider');
        }
        return $this->render('admin/imageSlider/addImageSlider.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @Route("/admin/allImagesInSlider", name="allImagesInSlider")
     */
    public function allImagesInSlider() {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        $images=$this->getDoctrine()->getRepository(ImageSlider::class)->recentImages();

        return $this->render('admin/imageSlider/allImagesInSlider.html.twig', ['images' => $images]);
    }

    /**
     * @Route("/admin/imageDelete/{id}", name="imageDelete")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function imageDelete($id)
    {

        $em = $this->getDoctrine()->getManager();
        $image=$em->getRepository('AppBundle:ImageSlider')->find($id);
        $em->remove($image);
        $em->flush();
        $this->addFlash('success', 'Image Deleted Successfully!');
        return $this->redirectToRoute('allImagesInSlider');
    }
}
