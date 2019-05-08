<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Ad;
use AppBundle\Entity\Article;
use AppBundle\Entity\Report;
use AppBundle\Entity\User;
use AppBundle\Form\cssType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="adminIndex")
     */
    public function adminIndex()
    {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }
        $reports=$this->getDoctrine()->getRepository(Report::class)->findAll();
        $usernames=[];
        foreach ($reports as $report) {
            $user=$this->getDoctrine()->getRepository(User::class)->find($report->getUserId());
            array_push($usernames, $user->getUsername());
        }
        return $this->render('admin/index.html.twig', ['reports' => $reports, 'usernames' => $usernames]);
    }

    /**
     * @Route("/allUsers", name="allUsers")
     */
    public function allUsers() {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        $users=$this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('admin/allUsers.html.twig', ['users' => $users]);
    }

    /**
     * @Route("makeUserAdmin/{id}", name="makeUserAdmin")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function makeUserAdmin($id) {

        $em = $this->getDoctrine()->getManager();
        $user=$em->getRepository('AppBundle:User')->find($id);
        $user->setRole('admin');
        $em->persist($user);
        $em->flush();
        $this->addFlash('message', 'Успешно дадохте Администраторкси права на този потребител!');
        return $this->redirectToRoute('allUsers');
    }

    /**
     * @param Request $request
     * @Route("/css", name="css")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function css(Request $request) {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        $path = './css/customAdmin.css';
        $file=file_get_contents($path);
        $form=$this->createFormBuilder()
            ->add('css', TextareaType::class, array('data' => $file, 'label' => false,))
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $data= $form->getData();
            file_put_contents($path, $data);
            return $this->redirectToRoute('css');
        }

        return $this->render('admin/css.html.twig', ['form' => $form->createView(), 'file' => $file]);
    }

    /**
     * @Route("deleteUser/{id}", name="deleteUser")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUser($id) {
        $em = $this->getDoctrine()->getManager();
        $user=$em->getRepository('AppBundle:User')->find($id);
        $ads=$em->getRepository(Ad::class)->findAdsByUser($user->getUsername());
        foreach ($ads as $ad) {
            $this->deleteAd($ad["id"]);
        }
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Успешно изтрихте този потребител!');

        return $this->redirectToRoute('allUsers');
    }

    /**
     * @Route("deleteReport/{id}", name="deleteReport")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteReport($id) {

        $em = $this->getDoctrine()->getManager();
        $report=$em->getRepository('AppBundle:Report')->find($id);
        $em->remove($report);
        $em->flush();
        $this->addFlash('success', 'Успешно изтрихте съобщението!');

        return $this->redirectToRoute('adminIndex');
    }

    /**
     * @Route("/ads", name="allAds")
     */
    public function allAds() {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        if($currentUser->getRole()!=="admin") {
            return $this->redirectToRoute('index');
        }

        $ads=$this->getDoctrine()->getRepository(Ad::class)->adminRecentAds();
        return $this->render('admin/ads.html.twig', ['ads' => $ads]);
    }

    /**
     * @Route("/ads/delete/{id}", name="adminDeleteAd")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function adminDeleteAd($id)
    {
        $this->deleteAd($id);
        $this->addFlash('success', 'Успешно изтрихте обявата!');
        return $this->redirectToRoute('allAds');
    }

    public function deleteAd($id) {
        $em = $this->getDoctrine()->getManager();
        $ad=$em->getRepository('AppBundle:Ad')->find($id);
        $re = '/(...)(.jpeg)/m';
        $re1 = '/(...)(.png)/m';
        foreach ($ad->getImages() as $image) {
            if(preg_match($re, $image) || preg_match($re1, $image)) {
                /** @var Filesystem $fileSystem */
                $fileSystem = new Filesystem();
                $fileSystem->remove($this->get('kernel')->getRootDir() . "\..\web\uploads\images\\" . $image);
            }
        }

        $em->remove($ad);
        $em->flush();
    }
}
