<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
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
        return $this->render('admin/index.html.twig');
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
        $this->addFlash('message', 'Article Deleted Successfully!');
        return $this->redirectToRoute('allUsers');
    }

    /**
     * @Route("deleteUser/{id}", name="deleteUser")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUser($id) {

        $em = $this->getDoctrine()->getManager();
        $user=$em->getRepository('AppBundle:User')->find($id);
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'User Deleted Successfully!');

        return $this->redirectToRoute('allUsers');
    }


}
