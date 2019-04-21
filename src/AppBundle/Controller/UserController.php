<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser!=null) {
            return $this->redirectToRoute('index');
        }
        /*$msg = new \Plasticbrain\FlashMessages\FlashMessages();
        $msg->success('opa');
        $msg->display();*/
        //$this->addFlash('success', 'Успешно влязохте в профила си!');
        return $this->render('login/login.html.twig');
    }

    /**
     * @Route("/login/error", name="loginError")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function errorLogin() {
        $this->addFlash('error', 'Грешно потребителско име или парола!');
        return $this->render('login/login.html.twig');
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser!=null) {
            return $this->redirectToRoute('index');
        }

        if (!session_id()) @session_start();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        $usernames=$this->getDoctrine()->getRepository(User::class)->findExistingUsernames();
        $emails=$this->getDoctrine()->getRepository(User::class)->findExistingEmails();
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Успешна регистрация! Сега влезте в профила си!');
            return $this->redirectToRoute('login');
        }

        return $this->render(
            'login/register.html.twig',
            array('form' => $form->createView(), 'usernames' => $usernames, 'emails' => $emails)
        );
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in app/config/security.yml
     *
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        throw new \Exception('This should never be reached!');
    }
}
