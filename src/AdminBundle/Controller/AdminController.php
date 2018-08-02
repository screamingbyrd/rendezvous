<?php

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;

class AdminController extends Controller
{

    public function indexAction()
    {
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
        return $this->render('AdminBundle::index.html.twig',array(
            'totalActiveOffer' => $totalActiveOffer,
            'countEmployer' => $employerCount
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

    public function listOfferAction(Request $request){

        $archived = $request->get('archived');
        $archived = isset($archived)?$archived:0;
        $active = $request->get('active');
        $active = isset($active)?$active:1;

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

        $offers = $offerRepository->findBy($arraySearch, array('creationDate' => 'DESC'));

        $totalActiveOffer = $offerRepository->countTotalActiveOffer();

        return $this->render('AdminBundle::listOffer.html.twig', array(
            'offers' => $offers,
            'active' => $active,
            'archived' => $archived,
            'totalActiveOffer' => $totalActiveOffer
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
            $message = (new \Swift_Message($translated . ' ' . $offer->getTitle()))
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

}