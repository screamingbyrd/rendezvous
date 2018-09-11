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

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $candidateCount = count($candidateRepository->findAll());

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $totalActiveOffer = $offerRepository->countTotalActiveOffer();

        $slotRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Slot')
        ;
        $totalSLot = $slotRepository->countTotalActiveSlot();

        return $this->render('AdminBundle::index.html.twig',array(
            'totalActiveOffer' => $totalActiveOffer,
            'countPro' => $proCount,
            'candidateCount' => $candidateCount,
            'totalSLot' => $totalSLot
        ));
    }

    public function listProAction(Request $request){

        $currentPage = $request->get('row');
        $sort = $request->get('sort');
        $currentPage = isset($currentPage)?$currentPage:1;
        $sort = isset($sort)?$sort:'DESC';

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
        $users = $repository->findAll();

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

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $candidates = $candidateRepository->findAll();

        $countResult = count($candidates);

        $finalArray = array_slice($candidates, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('AdminBundle::listClient.html.twig', array(
            'candidates' => $finalArray,
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
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $offer->setValidated($status);
        $em = $this->getDoctrine()->getManager();

        $em->merge($offer);
        $em->flush();

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $users = $userRepository->findBy(array('pro' => $offer->getPro()));
        $arrayEmail = array();

        foreach ($users as $proUser){
            $arrayEmail[] = $proUser->getEmail();
        }

        if(is_array($arrayEmail) && !$status){
            $firstUser = $arrayEmail[0];

            $mailer = $this->container->get('swiftmailer.mailer');
            $translated = $this->get('translator')->trans('form.offer.invalid.subject');
            $message = (new \Swift_Message($translated . ' ' . $offer->getTitle() . " Id: " .$offer->getId()))
                ->setFrom('rendezvouslu@noreply.lu')
                ->setTo($firstUser)
                ->setCc(array_shift($arrayEmail))
                ->setBody(
                    $this->renderView(
                        'AppBundle:Emails:offerInvalid.html.twig',
                        array('offer' => $offer,
                            'message' => $message
                        )
                    ),
                    'text/html'
                )
            ;

            $message->getHeaders()->addTextHeader(
                CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
            );
            $mailer->send($message);
        }

        return $this->redirectToRoute('list_offer_admin');
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

        $logRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:ActiveLog');

        $proRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro');

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client');

        $logCreditRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:LogCredit');

        $applicationRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:PostulatedOffers');

        $finalActiveLog = array();
        $finalClientLog = array();
        $finalProLog = array();
        $finalCreditLog = array();
        $monthlyCreditLog = array();
        $finalPriceLog = array();
        $monthlyPriceLog = array();
        $finalApplicationLog = array();
        $monthlyApplicationLog = array();

        for ($i = 1; $i <= 12; $i++){
            $startDate = new \DateTime();
            $startDate->setDate($year, $i, 1);
            $dateToTest = $year."-".$i."-01";
            $lastDay = date('t',strtotime($dateToTest));
            $endDate= new \DateTime();
            $endDate->setDate($year, $i, $lastDay);
            $finalActiveLog[] = (int)$logRepository->countActiveBetween($startDate,$endDate)[0]['total'];
            $finalClientLog[] = (int)$candidateRepository->countActiveBetween($endDate)[0]['total'];
            $finalApplicationLog[] = (int)$applicationRepository->countTotalBefore($endDate)[0]['total'];
            $monthlyApplicationLog[] = (int)$applicationRepository->countTotalMonthly($i, $year)[0]['total'];
            $finalProLog[] =(int)$proRepository->countActiveBetween($endDate)[0]['total'];
            $finalCreditLog[] =(int)$logCreditRepository->countTotalBefore($endDate)[0]['total'];
            $monthlyCreditLog[] = (int)$logCreditRepository->countTotalMonthly($i, $year)[0]['total'];
            $finalPriceLog[] =(int)$logCreditRepository->countTotalMoneyBefore($endDate)[0]['total'];
            $monthlyPriceLog[] = (int)$logCreditRepository->countTotalMoneyMonthly($i, $year)[0]['total'];
        }

        return $this->render('AdminBundle::logPage.html.twig',array(
            'activeOfferLog' => $finalActiveLog,
            'activeProLog' => $finalProLog,
            'activeClientLog' => $finalClientLog,
            'creditLog' => $finalCreditLog,
            'monthlyCreditLog' => $monthlyCreditLog,
            'finalPriceLog' => $finalPriceLog,
            'monthlyPriceLog' => $monthlyPriceLog,
            'application' => $finalApplicationLog,
            'monthlyApplication' => $monthlyApplicationLog,
            'year' => $year
        ));
    }

    public function addCreditAction(Request $request){

        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('rendezvous_home');
        }

        $proId = $request->get('id');

        $proRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro');
        $pro = $proRepository->findOneBy(array('id' => $proId));
        $vatNumber = $pro->getVatNumber();
        $countryCode = substr($vatNumber, 0, 2);

        $withVat = $countryCode == 'LU';

        $form = $this->get('form.factory')
            ->createNamedBuilder('credit-form')
            ->add('name',      TextType::class, array(
                'required' => true,
                'label' => 'dashboard.candidate.name'
            ))
            ->add('phone',      TextType::class, array(
                'required' => true,
                'label' => 'form.registration.phone'
            ))
            ->add('location',      TextType::class, array(
                'required' => true,
                'label' => 'offer.location',
            ))
            ->add('zipcode',      TextType::class, array(
                'required' => true,
                'label' => 'price.payment.zipcode'
            ))
            ->add('credits',      IntegerType::class, array(
                'required' => true,
                'label' => 'price.credits'
            ))
            ->add('price',      IntegerType::class, array(
                'required' => true,
                'label' => 'admin.addCredit.price'
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => array(
                    'class' => 'rendezvous-button',
                )
            ))
            ->getForm();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                //Logging credit purchase in db
                $logCredit = new LogCredit();
                $em = $this->getDoctrine()->getManager();

                $logCredit->setDate(new \DateTime());
                $logCredit->setCredit($data['credits']);
                $logCredit->setPro($pro);
                $logCredit->setPrice($data['price']);
                $logCredit->setName($data['name']);
                $logCredit->setPhone($data['phone']);
                $logCredit->setLocation($data['location']);
                $logCredit->setZipcode($data['zipcode']);

                $pro->setCredit($pro->getCredit() + $data['credits']);

                $em->persist($logCredit);
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

                if(is_array($arrayEmail)) {
                    $firstUser = $arrayEmail[0];
                    $html2pdf = new Html2Pdf();

                    $html = $this->renderView('AppBundle:Credit:billsPdf.html.twig', array(
                        'logCredit' => $logCredit,
                        'vatNumber' => $logCredit->getPro()->getVatNumber(),
                        'withVat' => $withVat
                    ));

                    $html2pdf->writeHTML($html);
                    $pdfContent = $html2pdf->output('facture.pdf', 'S');

                    $mailer = $this->container->get('swiftmailer.mailer');
                    $translated = $this->get('translator')->trans('admin.addCredit.subject');
                    $message = (new \Swift_Message($translated))
                        ->setFrom('rendezvouslu@noreply.lu')
                        ->setTo($firstUser)
                        ->setCc(array_shift($arrayEmail))
                        ->setBody(
                            $this->renderView(
                                'AppBundle:Emails:creditsAdded.html.twig',
                                array('credits' => $data['credits'],
                                )
                            ),
                            'text/html'
                        )
                        ->attach(\Swift_Attachment::newInstance($pdfContent, 'document.pdf', 'application/pdf'));

                    $message->getHeaders()->addTextHeader(
                        CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
                    );
                    $mailer->send($message);

                }

                $translated = $this->get('translator')->trans('price.payment.success', array('%credits%' => $data['credits']));
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('list_pro_admin');
            }
        }
        return $this->render('AdminBundle::addCredit.html.twig', [
            'form' => $form->createView(),
            'pro' => $pro,
            'withVat' => $withVat
        ]);
    }

}