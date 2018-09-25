<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 28/05/2018
 * Time: 12:00
 */

namespace ClientBundle\Controller;

use AppBundle\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ClientType;
use Symfony\Component\HttpFoundation\Response;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Service\Geocoder\GeocoderService;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderAddressRequest;
use Ivory\GoogleMap\Overlay\InfoWindow;
use Ivory\GoogleMap\Event\Event;
use Ivory\GoogleMap\Overlay\Marker;

class ClientController extends Controller
{
    public function createAction(Request $request)
    {
        $idOffer = $request->get('offerId');
        if(isset($idOffer)){
            $_SESSION['offerId'] = $idOffer;
        }

        $session = $request->getSession();

        $client = new Client();

        $form = $this->get('form.factory')->create(ClientType::class);

        // Si la requête est en POST
        if ($request->isMethod('POST')) {


            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);
            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {

                $data = $form->getData();

                $userRegister = $this->get('app.user_register');


                $user = $userRegister->register($data->getEmail(),$data->getEmail(),$data->getPassword(),$data->getFirstName(),$data->getLastName(), 'ROLE_CLIENT');


                if($user != false){
                $client->setUser($user);
                $client->setPhone($data->getPhone());

                // On enregistre notre objet $advert dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($client);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.successClient');
                $session->getFlashBag()->add('info', $translated);

                if(isset($_SESSION['offerId'])){
                    $id = $_SESSION['offerId'];
                    unset($_SESSION['offerId']);
                    $offerRepository = $this
                        ->getDoctrine()
                        ->getManager()
                        ->getRepository('AppBundle:Offer')
                    ;
                    $offer = $offerRepository->find($id);
                    $generateUrlService = $this->get('app.offer_generate_url');
                    $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));

                    return $this->redirectToRoute('show_offer', array('id' => $id, 'url' => $offer->getOfferUrl()));
                }else{
                    return $this->redirectToRoute('edit_client');
                }



                }else{
                    $translated = $this->get('translator')->trans('form.registration.error');
                    $session->getFlashBag()->add('danger', $translated);

                    return $this->redirectToRoute('rendezvous_home');
                }
            }
        }



        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule
        return $this->render('ClientBundle:Client:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request ){
        $user = $this->getUser();
        $session = $request->getSession();
        $idClient = $request->get('id');

        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $client = $clientRepository->findOneBy(isset($idClient)?array('id' => $idClient):array('user' => $user->getId()));

        if(!((isset($user) and in_array('ROLE_CLIENT', $user->getRoles())) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.client');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_client');
        }


        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $user = $userRepository->findOneBy(array('id' => $client->getUser()));

        $client->setFirstName($client->getUser()->getFirstName());
        $client->setLastName($client->getUser()->getLastName());
        $client->setEmail($client->getUser()->getEmail());

        $form = $this->get('form.factory')->create(ClientType::class, $client);

        $form->remove('password');
        $form->remove('terms');

        // Si la requête est en POST
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->isValid()) {

                $data = $form->getData();

                $userManager = $this->get('fos_user.user_manager');

                $user->setEmail($data->getEmail());
                $user->setEmailCanonical($data->getEmail());
                $user->setFirstName($data->getFirstName());
                $user->SetLastName($data->getLastName());
                $userManager->updateUser($user);

                $client->setPhone($data->getPhone());

                $em = $this->getDoctrine()->getManager();
                $em->merge($client);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.editedClient');
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('edit_client', array('id' => $client->getId()) );
            }
        }


        return $this->render('ClientBundle:Client:edit.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

    public function dashboardAction(Request $request){
        $user = $this->getUser();

        $idClient = $request->get('id');
        $session = $request->getSession();

        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $client = $clientRepository->findOneBy(isset($idClient)?array('id' => $idClient):array('user' => $user->getId()));

        if(!((isset($user) and in_array('ROLE_CLIENT', $user->getRoles())) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.client');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_client');
        }

        $proRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro')
        ;

        $rendezvousRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Rendezvous')
        ;

        $nextRendezvous = $rendezvousRepository->findNextForClient(array('client' => $client));

        $lastRendezvous = $rendezvousRepository->findLastForClient(array('client' => $client));

        $locationArray =array();
        foreach ($nextRendezvous as $rendezvous){
            $address = $rendezvous->getUser()->getPro()->getLocation() . ', ' . $rendezvous->getUser()->getPro()->getCity();
            $marker = $this->get('app.find_latlong')->geocode($address);
            $marker[] = $rendezvous->getUser()->getPro()->getName();
            $marker[] = $this->generateUrl('show_pro', array('id' => $rendezvous->getUser()->getPro()->getId()));
            $marker[] = ($rendezvous->getUser()->getPro()->getImages()->first()?$rendezvous->getUser()->getPro()->getImages()->first():'');
            $locationArray[$rendezvous->getUser()->getPro()->getId()] = $marker;
        }

        return $this->render('ClientBundle:Client:dashboard.html.twig',
            array(
                'nextRendezvous' => $nextRendezvous,
                'lastRendezvous' => $lastRendezvous,
                'location' => $locationArray
            ));
    }

    public function deleteAction(Request $request){

        $session = $request->getSession();
        $user = $this->getUser();
        $idClient = $request->get('id');
        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $client = $clientRepository->findOneBy(isset($idClient)?array('id' => $idClient):array('user' => $user->getId()));

        if(!((isset($user) and in_array('ROLE_CLIENT', $user->getRoles())) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.client');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_client');
        }
        $em = $this->getDoctrine()->getManager();

        if(in_array('ROLE_ADMIN', $user->getRoles())){
            $user = $client->getUser();
        }

        $postulatedRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:PostulatedOffers')
        ;
        $postulated = $postulatedRepository->findBy(array('client' => $client));
        foreach ($postulated as $offer){
            $em->remove($offer);
        }
        
        $favoriteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Favorite')
        ;
        $favorites = $favoriteRepository->findBy(array('client' => $client));
        foreach ($favorites as $favorite){
            $em->remove($favorite);
        }

        $mail = $user->getEmail();

        $cv = $client->getCv();

        if(isset($cv)){
            unlink($cv);
        }

        $em->remove($client);
        $em->remove($user);
        $em->flush();

        $mailer = $this->container->get('swiftmailer.mailer');

        $message = (new \Swift_Message($translated = $this->get('translator')->trans('email.deleted')))
            ->setFrom('rendezvouslu@noreply.lu')
            ->setTo($mail)
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:userDeleted.html.twig',
                    array()
                ),
                'text/html'
            )
        ;

        $message->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );
        $mailer->send($message);

        $translated = $this->get('translator')->trans('client.delete.deleted');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('rendezvous_home');
    }

    public function showAction(Request $request, $id){
        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and in_array('ROLE_EMPLOYER', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) || $user->getId() != $id)){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $client = $clientRepository->findOneBy(array('id' => $id));

        return $this->render('ClientBundle:Client:show.html.twig', array(
            'client' => $client,
        ));
    }

    public function searchAction(){
        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $clients = $clientRepository->findAll();

        return $this->render('ClientBundle:Client:search.html.twig', array(
            'clients' => $clients,
        ));
    }

    //@TODO put in CRON
    public function eraseUnusedCvsAction(){
        $clientRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $postulatedOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:PostulatedOffers')
        ;
        $clients = $clientRepository->findAll();

        $now = new \datetime();
        $past = $now->modify( '- 2 month' );
        $em = $this->getDoctrine()->getManager();
        foreach ($clients as $client){
            $recentOffers = $postulatedOfferRepository->getRecentPostulatedOffers($client);
            if(empty($recentOffers) && $client->getCreationDate() < $past){
                $cvLink = $client->getCv();
                $coverLink = $client->getCoverLetter();

                if(isset($cvLink) and $cvLink != ''){
                    if(file_exists($cvLink)){
                        unlink($cvLink);
                    }
                    $client->setCv('');
                }
                if(isset($coverLink) and $coverLink != ''){
                    if(file_exists($coverLink)){
                        unlink($coverLink);
                    }
                    $client->setCoverLetter('');
                }
                $em->merge($client);
            }
        }
        $em->flush();
        return new Response();
    }

}