<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 19/06/2018
 * Time: 14:45
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Favorite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FavoriteController extends Controller
{
    public function createAction(Request $request)
    {
        $session = $request->getSession();
        $offerId = $request->get('id');

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

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $offerId));

        $favoriteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Favorite')
        ;
        $favorite = $favoriteRepository->findOneBy(array(
            'candidate' => $candidate,
            'offer' => $offer
        ));

        if(isset($favorite) && !empty($favorite)){
            $translated = $this->get('translator')->trans('favorite.already');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        $session = $request->getSession();

        $favorite = new Favorite();

        $em = $this->getDoctrine()->getManager();

        $favorite->setCandidate($candidate);
        $favorite->setOffer($offer);

        $em->persist($favorite);
        $em->flush();

        $translated = $this->get('translator')->trans('favorite.created');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirect($_SERVER['HTTP_REFERER']);

    }

    public function deleteAction(Request $request){

        $session = $request->getSession();
        $favoriteId = $request->get('id');
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

        $favoriteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Favorite')
        ;
        $favorite = $favoriteRepository->findOneBy(array('id' => $favoriteId));

        if(!isset($favorite) || $candidate != $favorite->getCandidate()){
            return $this->redirectToRoute('create_candidate');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($favorite);
        $em->flush();

        $translated = $this->get('translator')->trans('favorite.deleted');

        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_candidate');
    }
}