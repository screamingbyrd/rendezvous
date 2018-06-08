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
        shuffle ($featuredEmployer);
        shuffle ($featuredOffer);
        return $this->render('AppBundle:Default:index.html.twig', array(
            'featuredEmployer' => $featuredEmployer,
            'featuredOffer' => $featuredOffer
        ));

    }


}