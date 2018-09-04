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
            return $this->redirectToRoute('jobnow_home');
        }

        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $employerCount = $employerRepository->countTotalDifferentEmployer();

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
            'countEmployer' => $employerCount,
            'totalSLot' => $totalSLot
        ));
    }

    public function listEmployerAction(){

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('jobnow_home');
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $users = $repository->findAll();

        $employers = [];
        foreach($users as $user)
        {
            if($user->getEmployer() != NULL)
            {
                $employers[] = $user;
            }
        }

        return $this->render('AdminBundle::listEmployer.html.twig', array(
            'employers' => $employers,
        ));
    }

    public function listCandidateAction(){

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('jobnow_home');
        }

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $candidates = $candidateRepository->findAll();

        return $this->render('AdminBundle::listCandidate.html.twig', array(
            'candidates' => $candidates
        ));
    }

    public function listAdminAction(){

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('jobnow_home');
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
            return $this->redirectToRoute('jobnow_home');
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
            return $this->redirectToRoute('jobnow_home');
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

        $archived = $request->get('archived');
        $archived = isset($archived)?$archived:0;
        $active = $request->get('active');
        $active = isset($active)?$active:1;
        $validated = $request->get('validated');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('jobnow_home');
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

        return $this->render('AdminBundle::listOffer.html.twig', array(
            'offers' => $offers,
            'active' => $active,
            'archived' => $archived,
            'validated' => $validated,
            'totalActiveOffer' => $totalActiveOffer,
            'totalNotValidatedActiveOffer' => $totalNotValidatedActiveOffer
        ));
    }

    public function changeValidationStatusAction(Request $request){
        $id = $request->get('id');
        $status = $request->get('status');
        $message = $request->get('message');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('jobnow_home');
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
        $users = $userRepository->findBy(array('employer' => $offer->getEmployer()));
        $arrayEmail = array();

        foreach ($users as $employerUser){
            $arrayEmail[] = $employerUser->getEmail();
        }

        if(is_array($arrayEmail) && !$status){
            $firstUser = $arrayEmail[0];

            $mailer = $this->container->get('swiftmailer.mailer');
            $translated = $this->get('translator')->trans('form.offer.invalid.subject');
            $message = (new \Swift_Message($translated . ' ' . $offer->getTitle() . " Id: " .$offer->getId()))
                ->setFrom('jobnowlu@noreply.lu')
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
            return $this->redirectToRoute('jobnow_home');
        }

        $logRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:ActiveLog');

        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer');

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate');

        $logCreditRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:LogCredit');

        $applicationRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:PostulatedOffers');

        $finalActiveLog = array();
        $finalCandidateLog = array();
        $finalEmployerLog = array();
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
            $finalCandidateLog[] = (int)$candidateRepository->countActiveBetween($endDate)[0]['total'];
            $finalApplicationLog[] = (int)$applicationRepository->countTotalBefore($endDate)[0]['total'];
            $monthlyApplicationLog[] = (int)$applicationRepository->countTotalMonthly($i, $year)[0]['total'];
            $finalEmployerLog[] =(int)$employerRepository->countActiveBetween($endDate)[0]['total'];
            $finalCreditLog[] =(int)$logCreditRepository->countTotalBefore($endDate)[0]['total'];
            $monthlyCreditLog[] = (int)$logCreditRepository->countTotalMonthly($i, $year)[0]['total'];
            $finalPriceLog[] =(int)$logCreditRepository->countTotalMoneyBefore($endDate)[0]['total'];
            $monthlyPriceLog[] = (int)$logCreditRepository->countTotalMoneyMonthly($i, $year)[0]['total'];
        }

        return $this->render('AdminBundle::logPage.html.twig',array(
            'activeOfferLog' => $finalActiveLog,
            'activeEmployerLog' => $finalEmployerLog,
            'activeCandidateLog' => $finalCandidateLog,
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
            return $this->redirectToRoute('jobnow_home');
        }

        $employerId = $request->get('id');

        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer');
        $employer = $employerRepository->findOneBy(array('id' => $employerId));
        $vatNumber = $employer->getVatNumber();
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
                    'class' => 'jobnow-button',
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
                $logCredit->setEmployer($employer);
                $logCredit->setPrice($data['price']);
                $logCredit->setName($data['name']);
                $logCredit->setPhone($data['phone']);
                $logCredit->setLocation($data['location']);
                $logCredit->setZipcode($data['zipcode']);

                $employer->setCredit($employer->getCredit() + $data['credits']);

                $em->persist($logCredit);
                $em->flush();

                $userRepository = $this
                    ->getDoctrine()
                    ->getManager()
                    ->getRepository('AppBundle:User')
                ;
                $users = $userRepository->findBy(array('employer' => $employer));
                $arrayEmail = array();

                foreach ($users as $employerUser){
                    $arrayEmail[] = $employerUser->getEmail();
                }

                if(is_array($arrayEmail)) {
                    $firstUser = $arrayEmail[0];
                    $html2pdf = new Html2Pdf();

                    $html = $this->renderView('AppBundle:Credit:billsPdf.html.twig', array(
                        'logCredit' => $logCredit,
                        'vatNumber' => $logCredit->getEmployer()->getVatNumber(),
                        'withVat' => $withVat
                    ));

                    $html2pdf->writeHTML($html);
                    $pdfContent = $html2pdf->output('facture.pdf', 'S');

                    $mailer = $this->container->get('swiftmailer.mailer');
                    $translated = $this->get('translator')->trans('admin.addCredit.subject');
                    $message = (new \Swift_Message($translated))
                        ->setFrom('jobnowlu@noreply.lu')
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

                return $this->redirectToRoute('list_employer_admin');
            }
        }
        return $this->render('AdminBundle::addCredit.html.twig', [
            'form' => $form->createView(),
            'employer' => $employer,
            'withVat' => $withVat
        ]);
    }

}