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
use Symfony\Component\HttpFoundation\Response;
use Ivory\GoogleMap\Place\Autocomplete;
use Ivory\GoogleMap\Place\AutocompleteType;
use Ivory\GoogleMap\Helper\Builder\PlaceAutocompleteHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;

class AppController extends Controller
{


    public function indexAction(Request $request)
    {

        $featuredProRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedPro')
        ;

        $featuredPro = $featuredProRepository->getCurrentFeaturedPro();

        $featuredOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedOffer')
        ;

        $featuredOffer = $featuredOfferRepository->getCurrentFeaturedOffer();

        $sql = " 
        SELECT t.name, count(ot.offer_id) as 'countOffer' FROM `tag` t left join offer_tag ot on ot.tag_id = t.id left join offer o on o.id = ot.offer_id  WHERE o.archived = 0 AND (o.slot_id is not null OR (o.startDate <= NOW() AND o.endDate >= NOW())) GROUP BY NAME
        ";

        $em = $this->getDoctrine()->getManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $tagArray = $stmt->fetchAll();

        $em = $this->getDoctrine()->getManager();

        $generateUrlService = $this->get('app.offer_generate_url');

        foreach ($featuredOffer as $offer){
            $now = new \DateTime();
            $next = new \DateTime();
            $offer->getOffer()->setOfferUrl($generateUrlService->generateOfferUrl($offer->getOffer()));
            if($offer->getOffer()->getEndDate() < $now){
                $offer->getOffer()->setStartDate($now);
                $offer->getOffer()->setUpdateDate($now);

                $offer->getOffer()->setEndDate($next->modify( '+ 2 month' ));

                $em->merge($offer->getOffer());
                $em->flush();
            }
        }

        $autoComplete = new Autocomplete();
        $autoComplete->setInputId('place_input');

        $autoComplete->setInputAttributes(array('class' => 'form-control', 'name' => 'location','placeholder' =>  $this->get('translator')->trans('form.offer.search.location')));

        $autoComplete->setTypes(array(AutocompleteType::CITIES));
        $autoCompleteHelperBuilder = new PlaceAutocompleteHelperBuilder();

        $autoCompleteHelper = $autoCompleteHelperBuilder->build();
        $apiHelperBuilder = ApiHelperBuilder::create();
        $apiHelperBuilder->setKey('AIzaSyBY8KoA6XgncXKSfDq7Ue7R2a1QWFSFxjc');
        $apiHelperBuilder->setLanguage($request->getLocale());

        $apiHelper = $apiHelperBuilder->build();

        $tags = array();
        foreach ($tagArray as $tag){
            $tags[] = $tag['name'];
        }

        shuffle ($featuredPro);
        shuffle ($featuredOffer);

        $adRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Ad')
        ;
        $ads = $adRepository->getCurrentAds();
        shuffle($ads);

        return $this->render('AppBundle:Default:index.html.twig', array(
            'featuredPro' => $featuredPro,
            'featuredOffer' => $featuredOffer,
            'tagArray' => $tagArray,
            'tags' => $tags,
            'autoComplete' => $autoCompleteHelper->render($autoComplete),
            'autoCompleteScript' => $apiHelper->render([$autoComplete]),
            'ads' => $ads,
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

    public function checkUserAlreadyExistAction(Request $request)
    {
        $mail = $request->get('mail');
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $user = $userRepository->findOneBy(array('email' => $mail));

        return new Response($user);

    }

    public function checkVatNumberAction(Request $request)
    {
        $vat = $request->get('vat');

        $countryCode = substr($vat, 0, 2);
        $vatNumber = substr($vat, 2);


        if(!ctype_alpha($countryCode) || !ctype_digit($vatNumber)){
            $response = null;
        }else{
            $client = new \SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
            $response = $client->checkVat(array(
                'countryCode' => $countryCode,
                'vatNumber' => $vatNumber
            ));
            $response = $response->valid;
            $response = $response ? 'true':'false';
        }

        return new Response($response);

    }

    public function sendStartMailAction(Request $request){

        $arrayNewUser = array();

//        $arrayNewUser = $request->get('list');

        $mailer = $this->container->get('swiftmailer.mailer');

        if(empty($arrayNewUser)){
            var_dump('Array is empty dude!');exit;
        }

        foreach ($arrayNewUser as $mail){
            $message = (new \Swift_Message('Jownow is live !'))
                ->setFrom('rendezvouslu@noreply.lu')
                ->setTo($mail)
                ->setBody(
                    $this->renderView(
                        'AppBundle:Emails:startRendezvous.html.twig',
                        array()
                    ),
                    'text/html'
                )
            ;

            $message->getHeaders()->addTextHeader(
                CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
            );
            $mailer->send($message);
        }

        var_dump('send');

        return new Response();
    }
}