<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 19/06/2018
 * Time: 08:33
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{

    public function createAction(Request $request)
    {
        $session = $request->getSession();
        $type = $request->get('type');
        $elementId = $request->get('id');

        $user = $this->getUser();
        if(!isset($user) || !in_array('ROLE_CANDIDATE', $user->getRoles())){
            return $this->redirectToRoute('create_candidate');
        }

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $candidate = $candidateRepository->findOneBy(array('user' => $user->getId()));

        $notificationRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Notification')
        ;
        $notification = $notificationRepository->findOneBy(array(
            'candidate' => $candidate,
            'typeNotification' => $type,
            'elementId' => $elementId
        ));

        if(isset($notification) && !empty($notification)){
            $translated = $this->get('translator')->trans('notification.already');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('dashboard_candidate');
        }

        $session = $request->getSession();

        $notification = new Notification();

        $em = $this->getDoctrine()->getManager();

        $notification->setCandidate($candidate);

        $now =  new \DateTime();
        $notification->setDate($now);
        $notification->setTypeNotification($type);
        $notification->setElementId($elementId);

        $em->persist($notification);
        $em->flush();

        $translated = $this->get('translator')->trans('notification.created');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_candidate');

    }

    public function deleteAction(Request $request){

        $session = $request->getSession();
        $notificationId = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_CANDIDATE', $user->getRoles())){
            return $this->redirectToRoute('create_candidate');
        }

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $candidate = $candidateRepository->findOneBy(array('user' => $user->getId()));

        $notificationRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Notification')
        ;
        $notification = $notificationRepository->findOneBy(array('id' => $notificationId));

        if(!isset($notification) || $candidate != $notification->getCandidate()){
            return $this->redirectToRoute('create_candidate');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($notification);
        $em->flush();

        $translated = $this->get('translator')->trans('notification.deleted');

        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_candidate');
    }

    public function sendAction(Request $request){

        $session = $request->getSession();

        $now =  new \DateTime();

        $notificationRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Notification')
        ;
        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Candidate')
        ;
        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Employer')
        ;
        $tagRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
        ;
        $notifications = $notificationRepository->findAll();
        $em = $this->getDoctrine()->getManager();

        foreach ($notifications as $notification){
            $offers = $offerRepository->getNotificationOffers($notification);
            var_dump($offers);exit;
            if(!empty($offers)){
                $candidate = $candidateRepository->findOneBy(array('id' => $notification->getCandidate()));

                if($notification->getTypeNotification() == 'employer'){
                    $employer = $employerRepository->findOneBy(array('id' => $notification->getElementId()));
                    $subject = $employer->getName();
                }elseif ($notification->getTypeNotification() == 'tag'){
                    $tag = $tagRepository->findOneBy(array('id' => $notification->getElementId()));
                    $subject = $tag->getName();
                }
                $mail = $candidate->getUser()->getEmail();
                $mailer = $this->container->get('swiftmailer.mailer');

                $message = (new \Swift_Message('New offers could interest you'))
                    ->setFrom('jobnowlu@noreply.lu')
                    ->setTo('arthur.regnault@altea.lu')
                    ->setBody(
                        $this->renderView(
                            'AppBundle:Emails:notification.html.twig',
                            array('offers' => $offers,
                                'subject' => $subject)
                        ),
                        'text/html'
                    )
                ;

                $mailer->send($message);
            }
var_dump('rr');exit;
            $notification->setDate($now);
            $em->merge($notification);
        }

        $em->flush();

        return new Response();

    }

}