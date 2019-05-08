<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request, \Swift_Mailer $mailer)
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
            $email=$form["email"]->getData();
            $username=$form["username"]->getData();
            $message = (new \Swift_Message('Регистрация в JorjsList.eu'))
                ->setFrom('admin@jorjslist.eu')
                ->setTo($email)
                ->setBody('Вие успешно се регистрирахте!' . PHP_EOL .
                    'Потребителско име: ' . $username . PHP_EOL .
                    'Влезте в профила си от тук: ' . 'http://jorjslist.eu/login/');
            $mailer->send($message);

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
     * @Route("/editUser", name="editUser")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editUser(Request $request) {
        /** @var User $currentUser */
        $currentUser= $this->getUser();
        if($currentUser==null) {
            return $this->redirectToRoute('index');
        }
        $id=(int)$currentUser->getId();
        $user=$this->getDoctrine()->getRepository(User::class)->find($id);
        $oldPass=$user->getPassword();
        $form= $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em=$this->getDoctrine()->getManager();
            $newPass= $form["password"]->getData();
            $password = $this->get('security.password_encoder')->encodePassword($user, $newPass);
            if($newPass==null || $newPass=="") {
                $user->setPassword($oldPass);
            } else {
                $user->setPassword($password);
            }
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Успешно редактирахте своят профил!');
            return $this->redirectToRoute('index');
        }
        return $this->render('default/editUser.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/forgottenPassword", name="forgottenPassword")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function forgottenPassword(Request $request, \Swift_Mailer $mailer) {
        $form = $this->createFormBuilder()
            ->add('email', TextType::class, array('label' => false))
            ->add('username', TextType::class, array('label' => false))
            ->getForm();
        $form->handleRequest($request);
        if($form->isValid() && $form->isSubmitted()) {
            $username= $form["username"]->getData();
            $email=$form["email"]->getData();

            $user=$this->getDoctrine()->getRepository(User::class)->findOneBy(["email" => $email]);
            if($user==null) {
                $this->addFlash("error", "Не съществува такъв потребител!");
                return $this->redirectToRoute('forgottenPassword');
            } else {
                if($user->getUsername()==$username)
                {
                    $em=$this->getDoctrine()->getManager();
                    $usr=$this->getDoctrine()->getRepository(User::class)->find($user->getId());
                    $newPass=$this->generateRandomString();
                    $password = $this->get('security.password_encoder')->encodePassword($usr, $newPass);
                    $usr->setPassword($password);
                    $em->persist($usr);
                    $em->flush();
                    $message = (new \Swift_Message('Забравена парола в JorjsList.eu'))
                        ->setFrom('admin@jorjslist.eu')
                        ->setTo($email)
                        ->setBody('Вашата нова парола е: ' . $newPass . PHP_EOL .
                                    'Влезте в профила си и я сменете!');
                    $mailer->send($message);

                    $this->addFlash("success", "Обновената парола е изпратена на имейл: " . $user->getEmail() . "!");
                    return $this->redirectToRoute('index');
                } else {
                    $this->addFlash("error", "Не съществува такъв потребител!");
                    return $this->redirectToRoute('forgottenPassword');
                }
            }

        }
        return $this->render('default/forgottenPassword.html.twig', ['form' => $form->createView()]);

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


    public function generateRandomString($length = 8, $characters = 'abcdefghijklmnopqrstuvwxyz')
    {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
