<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Ad;
use AppBundle\Entity\Category;
use AppBundle\Entity\Report;
use AppBundle\Entity\User;
use AppBundle\Form\AdType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        /** @var Category $category */
        $category=$this->getDoctrine()->getRepository(Category::class)->find($id);
        $ads=$this->getDoctrine()->getRepository(Ad::class)->findAdsByCategory($id);
        return $this->render('ads/adsByCategory.html.twig', ['ads' => $ads, 'category' => $category->getName()]);
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
            $this->addFlash('error', 'За да създадете обява моля влезте в профила си!');
            return $this->redirectToRoute('login');
        }
        /** @var Ad $ad */
        $ad= new Ad();
        $form= $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em=$this->getDoctrine()->getManager();
            $images = $ad->getImages();
            $arr = [];
            if ($images) {
                foreach ($images as $image) {
                    $fileName = md5(uniqid()) . '.' . $image->guessExtension();
                    $arr[] = $fileName;
                    try {
                        $image->move(
                            $this->getParameter('images_directory'),
                            $fileName
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                }
            }
                $ad->setImages($arr);
                /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
                //$file = $form->get('images')->getData();


                $ad->setAuthor($currentUser->getUsername());
                $em_category = $this->getDoctrine()->getManager();
                $data=$request->request->all();
                $categoryId=intval($data["ad"]["categoryId"]);
                $category = $em_category->getRepository('AppBundle:Category')->findOneBy(array('id' => $categoryId));
                $ad->setCategoryId($category);
                $em->persist($ad);
                $em->flush();
                $this->addFlash('success', 'Успешно публикувахте Вашата обява!');
                return $this->redirectToRoute('myAds');

            }
            return $this->render('ads/newAd.html.twig',
                array('form' => $form->createView(),
                    'user' => $currentUser));

    }

    /**
     * @Route("/ad/{id}", name="viewAd")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAd($id, Request $request) {
        /** @var User $currentUser */
        $currentUser= $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $ad=$em->getRepository('AppBundle:Ad')->find($id);
        $images=$ad->getImages();
        $views=$ad->getViews();
        $ad->setViews($views+1);
        $em->persist($ad);
        $em->flush();
        /** @var Report $report */
        $report=new Report();
        $form=$this->createFormBuilder()
            ->add('comment', TextareaType::class, array('label' => false))
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $data= $form->getData();
            $report->setComment($data["comment"]);
            $report->setAdId($id);
            $report->setUserId($currentUser->getId());
            $em->persist($report);
            $em->flush();
            $this->addFlash("success", "Благодарим Ви, успешно докладвахте обявата!");
            return $this->redirectToRoute('viewAd', ['id' => $id]);
        }

        return $this->render('ads/viewAd.html.twig',
            ['ad' => $ad, 'images' => $images, 'form' => $form->createView()]);
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
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAd($id, Request $request, \Swift_Mailer $mailer) {
        $ad=$this->getDoctrine()->getRepository(Ad::class)->find($id);
        $oldPrice=$ad->getPrice();
        $oldImages=$ad->getImages();
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
            if($ad->getImages() == null)
            {
                $ad->setImages($oldImages);
            }
            else {
                $arr = [];
                foreach ($ad->getImages() as $image) {
                    $fileName = md5(uniqid()) . '.' . $image->guessExtension();
                    $arr[] = $fileName;
                    try {
                        $image->move(
                            $this->getParameter('images_directory'),
                            $fileName
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                }
                $fileSystem = new Filesystem();
                foreach ($oldImages as $image) {
                    $fileSystem->remove($this->get('kernel')->getRootDir() . "\..\web\uploads\images\\" . $image);
                }
                $ad->setImages($arr);
            }

            if($ad->getPrice()!==$oldPrice) {
                if($ad->getSubs()!==null || $ad->getSubs()!=="") {
                    foreach ($ad->getSubs() as $subscriber) {
                        $message = (new \Swift_Message('Обновена цена на ' . $ad->getTitle() . ' - JorjsList'))
                            ->setFrom('admin@jorjslist.eu')
                            ->setTo($subscriber)
                            ->setBody('Цената на обявата беше обновена!' . PHP_EOL .
                                'Стара цена: ' . $oldPrice . ' лв.' . PHP_EOL .
                                'Нова цена: ' . $ad->getPrice() . ' лв.' . PHP_EOL .
                                'Линк: http://jorjslist.eu/ad/' . $ad->getId());
                        $mailer->send($message);
                    }
                }
            }

            $em=$this->getDoctrine()->getManager();
            $em->persist($ad);
            $em->flush();
            $this->addFlash('success', 'Успешно редактирахте ' . $ad->getTitle() . '!');
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
        $this->addFlash('success', 'Успешно изтрихте Вашата обява!');
        return $this->redirectToRoute('myAds');
    }

    /**
     * @Route("/allCategories", name="allCategories")
     */
    public function allCategories() {

        $categories=$this->getDoctrine()->getRepository(Category::class)->findAll();

        return $this->render('default/allCategories.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/ads/user/{username}", name="adsByUser")
     * @param $username
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adsByUser($username) {
        $ads=$this->getDoctrine()->getRepository(Ad::class)->findAdsByUser($username);

        return $this->render('ads/adsByUser.html.twig', ['ads' => $ads, 'username' => $username]);
    }

    /**
     * @Route("/adSubscribe/{id}", name="subscribe")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function subscribe($id) {
        $em = $this->getDoctrine()->getManager();
        /** @var User $currentUser */
        $currentUser=$this->getUser();
        $ad=$this->getDoctrine()->getRepository(Ad::class)->find($id);
        if($ad->getSubs()==null || $ad->getSubs()=="") {
            $arr[]=$currentUser->getEmail();
            $ad->setSubs($arr);
            $em->persist($ad);
            $em->flush();
            $this->addFlash('success', 'При промяна на цената, ще получите имейл!');
            return $this->redirectToRoute('viewAd', ['id' => $id]);
        } else {
            $arr=$ad->getSubs();
            $arr[]=$currentUser->getEmail();
            $ad->setSubs($arr);
            $em->persist($ad);
            $em->flush();
            $this->addFlash('success', 'При промяна на цената, ще получите имейл!');
            return $this->redirectToRoute('viewAd', ['id' => $id]);
        }

    }

    /**
     * @Route("/adRemoveSubscription/{id}", name="removeSubscription")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeSubscription($id) {
        $em = $this->getDoctrine()->getManager();
        /** @var User $currentUser */
        $currentUser=$this->getUser();
        $ad=$this->getDoctrine()->getRepository(Ad::class)->find($id);
        $arr=$ad->getSubs();
        $newarr=[];
        foreach($arr as $el) {
            if($el!==$currentUser->getEmail()) {
                $newarr[]=$el;
            }
        }
        $index = array_search($currentUser->getEmail(), $newarr);
        if(count($newarr) == 1 && $index==true) {
            $newarr=null;
        }
        if(count($newarr) == 0) {
            $newarr=null;
        }

        $ad->setSubs($newarr);
        $em->persist($ad);
        $em->flush();
        $this->addFlash('success', 'Вече няма да получавате имейли при промяна на цената!');
        return $this->redirectToRoute('viewAd', ['id' => $id]);
    }

    public function deleteElement($element, &$array){
        $index = array_search($element, $array);
        if($index !== false){
            unset($array[$index]);
        }
    }
}
