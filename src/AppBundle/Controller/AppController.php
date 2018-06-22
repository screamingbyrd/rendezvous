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

        $sql = " 
        SELECT t.name, count(o.offer_id) as 'countOffer' FROM `tag` t left join offer_tag o on o.tag_id = t.id GROUP BY NAME
        ";

        $em = $this->getDoctrine()->getManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $tagArray = $stmt->fetchAll();

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
            'featuredOffer' => $featuredOffer,
            'tags' => $tagArray
        ));

    }

    public function AboutPageAction(Request $request)
    {

        return $this->render('AppBundle:Footer:about.html.twig');

    }

    public function faqPageAction(Request $request)
    {

        return $this->render('AppBundle:Footer:faq.html.twig');

    }

    public function privacyPageAction(Request $request)
    {

        return $this->render('AppBundle:Footer:privacy.html.twig');

    }

    public function legalPageAction(Request $request)
    {

        return $this->render('AppBundle:Footer:legal.html.twig');

    }

    public function howitworkPageAction(Request $request)
    {

        return $this->render('AppBundle:Default:howitwork.html.twig');

    }

}