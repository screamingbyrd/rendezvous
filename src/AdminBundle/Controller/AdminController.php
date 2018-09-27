<?php

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Entity\LogCredit;
use Spipu\Html2Pdf\Html2Pdf;

class AdminController extends Controller
{

    public function indexAction()
    {
        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $proRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro')
        ;
        $proCount = $proRepository->countTotalDifferentPro();

        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $clientCount = count($clientRepository->findAll());


        return $this->render('AdminBundle::index.html.twig',array(
            'countPro' => $proCount,
            'clientCount' => $clientCount,
        ));
    }

    public function listProAction(Request $request){

        $currentPage = $request->get('row');
        $sort = $request->get('sort');
        $currentPage = isset($currentPage)?$currentPage:1;
        $sort = isset($sort)?$sort:'DESC';

        $valid = $request->get('validated');

        $numberOfItem = 20;

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $users = $repository->findPro($valid);

        $pros = [];
        foreach($users as $user)
        {
            if($user->getPro() != NULL)
            {
                $pros[] = $user;
            }
        }

        $countResult = count($pros);

        $finalArray = array_slice($pros, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('AdminBundle::listPro.html.twig', array(
            'pros' => $finalArray,
            'page' => $currentPage,
            'total' => $totalPage,
            'sort' => $sort,
            'validated' => $valid,
        ));
    }

    public function listClientAction(Request$request){
        $currentPage = $request->get('row');
        $sort = $request->get('sort');
        $currentPage = isset($currentPage)?$currentPage:1;
        $sort = isset($sort)?$sort:'DESC';

        $numberOfItem = 20;

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $clients = $clientRepository->findAll();

        $countResult = count($clients);

        $finalArray = array_slice($clients, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('AdminBundle::listClient.html.twig', array(
            'clients' => $finalArray,
            'page' => $currentPage,
            'total' => $totalPage,
            'sort' => $sort,
        ));
    }

    public function listAdminAction(){

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $admins = $userRepository->getAdmins();

        return $this->render('AdminBundle::listAdmin.html.twig', array(
            'admins' => $admins
        ));
    }

    public function removeFromAdminAction(Request $request)
    {
        $elementId = $request->get('id');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $user = $userRepository->findOneBy(array('id' => $elementId));

        $em = $this->getDoctrine()->getManager();

        $user->removeRole('ROLE_ADMIN');

        $em->merge($user);
        $em->flush();

        return $this->redirectToRoute('list_admin');
    }

    public function promoteToAdminAction(Request $request)
    {
        $mail = $request->get('mail');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $user = $userRepository->findOneBy(array('username' => $mail));

        $em = $this->getDoctrine()->getManager();

        $user->addRole('ROLE_ADMIN');

        $em->merge($user);
        $em->flush();

        return $this->redirectToRoute('list_admin');
    }

    public function listOfferAction(Request $request){
        $currentPage = $request->get('row');
        $sort = $request->get('sort');
        $currentPage = isset($currentPage)?$currentPage:1;
        $sort = isset($sort)?$sort:'DESC';

        $numberOfItem = 20;
        $archived = $request->get('archived');
        $archived = isset($archived)?$archived:0;
        $active = $request->get('active');
        $active = isset($active)?$active:1;
        $validated = $request->get('validated');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $arraySearch = array('archived' => $archived);

        if(isset($validated)){
            $arraySearch['validated'] = null;
        }

        $offers = $offerRepository->findBy($arraySearch, array('creationDate' => 'DESC'));

        $totalActiveOffer = $offerRepository->countTotalActiveOffer();
        $totalNotValidatedActiveOffer = $offerRepository->countTotalNotValidatedActiveOffer();

        $countResult = count($offers);

        $finalArray = array_slice($offers, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('AdminBundle::listOffer.html.twig', array(
            'offers' => $finalArray,
            'active' => $active,
            'archived' => $archived,
            'validated' => $validated,
            'totalActiveOffer' => $totalActiveOffer,
            'totalNotValidatedActiveOffer' => $totalNotValidatedActiveOffer,
            'page' => $currentPage,
            'total' => $totalPage,
            'sort' => $sort,
        ));
    }

    public function changeValidationStatusAction(Request $request){
        $id = $request->get('id');
        $status = $request->get('status');
        $message = $request->get('message');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro')
        ;
        $pro = $offerRepository->findOneBy(array('id' => $id));

        $pro->setValidated($status);
        $em = $this->getDoctrine()->getManager();

        $em->merge($pro);
        $em->flush();

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $users = $userRepository->findBy(array('pro' => $pro));
        $arrayEmail = array();

        foreach ($users as $proUser){
            $arrayEmail[] = $proUser->getEmail();
        }

        if(is_array($arrayEmail) && !$status){
            $firstUser = $arrayEmail[0];

            $mailer = $this->container->get('swiftmailer.mailer');
            $translated = $this->get('translator')->trans('form.offer.invalid.subject');
//            $message = (new \Swift_Message($translated))
//                ->setFrom('rendezvouslu@noreply.lu')
//                ->setTo($firstUser)
//                ->setCc(array_shift($arrayEmail))
//                ->setBody(
//                    $this->renderView(
//                        'AppBundle:Emails:offerInvalid.html.twig',
//                        array('pro' => $pro,
//                            'message' => $message
//                        )
//                    ),
//                    'text/html'
//                )
//            ;
//
//            $message->getHeaders()->addTextHeader(
//                CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
//            );
//            $mailer->send($message);
        }

        return $this->redirectToRoute('list_pro_admin');
    }

    public function logPageAction(Request $request)
    {
        $now = new \DateTime();
        $year = $request->get('year');
        $year = isset($year)?$year:$now->format('Y');
        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $proRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro');

        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client');

        $finalClientLog = array();
        $finalProLog = array();

        for ($i = 1; $i <= 12; $i++){
            $startDate = new \DateTime();
            $startDate->setDate($year, $i, 1);
            $dateToTest = $year."-".$i."-01";
            $lastDay = date('t',strtotime($dateToTest));
            $endDate= new \DateTime();
            $endDate->setDate($year, $i, $lastDay);
            $finalClientLog[] = (int)$clientRepository->countActiveBetween($endDate)[0]['total'];
            $finalProLog[] =(int)$proRepository->countActiveBetween($endDate)[0]['total'];
        }

        return $this->render('AdminBundle::logPage.html.twig',array(
            'activeProLog' => $finalProLog,
            'activeClientLog' => $finalClientLog,
            'year' => $year
        ));
    }

}