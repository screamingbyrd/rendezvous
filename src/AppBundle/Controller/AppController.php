<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 30/05/2018
 * Time: 16:21
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class AppController extends Controller
{

    public function indexAction(Request $request)
    {
        $featuredEmployerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedEmployer')
        ;

        $featuredEmployer = $featuredEmployerRepository->getCurrentFeaturedEmployer();

        $featuredOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedOffer')
        ;

        $featuredOffer = $featuredOfferRepository->getCurrentFeaturedOffer();
        $em = $this->getDoctrine()->getManager();

        foreach ($featuredOffer as $offer){
            $now = new \DateTime();
            $next = new \DateTime();
            if($offer->getOffer()->getEndDate() < $now){
                $offer->getOffer()->setStartDate($now);
                $offer->getOffer()->setUpdateDate($now);

                $offer->getOffer()->setEndDate($next->modify( '+ 2 month' ));

                $em->merge($offer->getOffer());
                $em->flush();
            }
        }

        shuffle ($featuredEmployer);
        shuffle ($featuredOffer);
        return $this->render('AppBundle:Default:index.html.twig', array(
            'featuredEmployer' => $featuredEmployer,
            'featuredOffer' => $featuredOffer
        ));

    }


}