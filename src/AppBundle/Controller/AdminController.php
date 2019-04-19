<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\User;
use AppBundle\Form\cssType;
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
        $articles=$this->getDoctrine()->getRepository(Article::class)->findRecentArticles();
        return $this->render('admin/index.html.twig', ['articles' => $articles]);
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
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Успешно изтрихте този потребител!');

        return $this->redirectToRoute('allUsers');
    }


}
