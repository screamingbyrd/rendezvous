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

class ClientController extends Controller
{
    public function createAction(Request $request)
    {
        $idOffer = $request->get('offerId');
        if(isset($idOffer)){
            $_SESSION['offerId'] = $idOffer;
        }

        $session = $request->getSession();

        $candidate = new Client();

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


                $user = $userRegister->register($data->getEmail(),$data->getEmail(),$data->getPassword(),$data->getFirstName(),$data->getLastName(), 'ROLE_CANDIDATE');


                if($user != false){
                $candidate->setUser($user);
                $candidate->setDescription($data->getDescription());
                $candidate->setAge($data->getAge());
                $candidate->setExperience($data->getExperience());
                $candidate->setLicense($data->getLicense());
                $candidate->setDiploma($data->getDiploma());
                $candidate->setSocialMedia($data->getSocialMedia());
                $candidate->setPhone($data->getPhone());

                // On enregistre notre objet $advert dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($candidate);
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
                    return $this->redirectToRoute('edit_candidate');
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

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $candidate = $candidateRepository->findOneBy(isset($idClient)?array('id' => $idClient):array('user' => $user->getId()));

        if(!((isset($user) and in_array('ROLE_CANDIDATE', $user->getRoles())) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.candidate');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_candidate');
        }

        $originalCvPath = $candidate->getCv();
        $titleCV = null;
        if (isset($originalCvPath)){
            $titleCV = substr($originalCvPath, strrpos($originalCvPath, '-job42-') + 7);
            $titleCV = str_replace('-space-', ' ', $titleCV);
        }

        $originalCoverLetterPath = $candidate->getCoverLetter();
        $titleCoverLetter = null;
        if(isset($originalCoverLetterPath)){
            $titleCoverLetter = substr($originalCoverLetterPath, strrpos($originalCoverLetterPath, '-job42-') + 7);
            $titleCoverLetter = str_replace('-space-', ' ', $titleCoverLetter);
        }


        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $user = $userRepository->findOneBy(array('id' => $candidate->getUser()));



        $candidate->setFirstName($candidate->getUser()->getFirstName());
        $candidate->setLastName($candidate->getUser()->getLastName());
        $candidate->setEmail($candidate->getUser()->getEmail());

        $form = $this->get('form.factory')->create(ClientType::class, $candidate);

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

                $candidate->setDescription($data->getDescription());
                $candidate->setAge($data->getAge());
                $candidate->setExperience($data->getExperience());
                $candidate->setLicense($data->getLicense());
                $candidate->setDiploma($data->getDiploma());
                $candidate->setSocialMedia($data->getSocialMedia());
                $candidate->setPhone($data->getPhone());
                $candidate->setModifiedDate( new \datetime());

                $target_dir = "uploads/images/candidate/";
                $newCv = $data->getCv();
                if(isset($newCv) && is_object($newCv)){

                    $target_file = $target_dir . md5(uniqid()) . '%' . basename($newCv->getClientOriginalName());
                    move_uploaded_file($newCv->getPathname(), $target_file);

                    if(isset($originalCvPath) && $originalCvPath != ''){
                        unlink($originalCvPath);
                    }
                    $candidate->setCv($target_file);
                }

                $newCoverLetter = $data->getCoverLetter();
                if(isset($newCoverLetter) && is_object($newCoverLetter)){
                    $target_file_cover = $target_dir . md5(uniqid()) . '%' . basename($newCoverLetter->getClientOriginalName());
                    move_uploaded_file($newCoverLetter->getPathname(), $target_file_cover);

                    if(isset($originalCoverLetterPath) && $originalCoverLetterPath != ''){
                        unlink($originalCoverLetterPath);
                    }
                    $candidate->setCoverLetter($target_file_cover);
                }

                $em = $this->getDoctrine()->getManager();
                $em->merge($candidate);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.editedClient');
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('show_candidate', array('id' => $candidate->getId()) );
            }
        }

        $completion = 3;

        $title = $candidate->getTitle();
        if(isset($title)){
            $completion += 1;
        }
        $description = $candidate->getDescription();
        if(isset($description)){
            $completion += 1;
        }
        $age = $candidate->getAge();
        if(isset($age)){
            $completion += 1;
        }
        $experience = $candidate->getExperience();
        if(isset($experience)){
            $completion += 1;
        }
        $diploma = $candidate->getDiploma();
        if(isset($diploma)){
            $completion += 1;
        }
        $socialMedia = $candidate->getSocialMedia();
        if(isset($socialMedia)){
            $completion += 1;
        }
        $phone = $candidate->getPhone();
        if(isset($phone)){
            $completion += 1;
        }
        if(isset($candidate->getLicense()[0])){
            $completion += 1;
        }
        if(isset($candidate->getLanguage()[0])){
            $completion += 1;
        }
        if(isset($candidate->getTag()[0])){
            $completion += 1;
        }
        if(isset($candidate->getSearchedTag()[0])){
            $completion += 1;
        }

        $completion = $completion/14 * 100;

        return $this->render('ClientBundle:Client:edit.html.twig', array(
            'form' => $form->createView(),
            'completion' => $completion,
            'user' => $user,
            'cvTitle' => $titleCV,
            'coverLetterTitle' => $titleCoverLetter
        ));
    }

    public function dashboardAction(Request $request){
        $user = $this->getUser();

        $idClient = $request->get('id');
        $session = $request->getSession();

        $generateUrlService = $this->get('app.offer_generate_url');

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $candidate = $candidateRepository->findOneBy(isset($idClient)?array('id' => $idClient):array('user' => $user->getId()));

        if(!((isset($user) and in_array('ROLE_CANDIDATE', $user->getRoles())) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.candidate');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_candidate');
        }

        $employerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Pro')
        ;
        $favoriteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Favorite')
        ;
        $tagRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
        ;

        $notificationRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Notification')
        ;
        $notifications = $notificationRepository->findBy(array('candidate' => $candidate));
        $favorites = $favoriteRepository->findBy(array('candidate' => $candidate));

        foreach ($favorites as &$favorite){
            $favorite->getOffer()->setOfferUrl($generateUrlService->generateOfferUrl($favorite->getOffer()));
        }

        $notificationsArray = array();

        foreach ($notifications as $notification){
            $newNotification = array();
            $newNotification['uid'] = $notification->getUid();
            $newNotification['elementId'] = $notification->getElementId();
            $type = $notification->getTypeNotification();
            $newNotification['type'] = $type;
            if($type == 'notification.employer'){
                $employer = $employerRepository->findOneBy(array('id' => $notification->getElementId()));
                $newNotification['name'] = $employer->getName();
            }elseif ($type == 'notification.tag'){
                $tag = $tagRepository->findOneBy(array('id' => $notification->getElementId()));
                $newNotification['name'] = $tag->getName();
            }
            $notificationsArray[] = $newNotification;
        }

        $postulatedOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:PostulatedOffers')
        ;
        $postulatedOffers = $postulatedOfferRepository->findBy(array('candidate' => $candidate));

        $offerIdArray = $finalArray = array();

        foreach ($postulatedOffers as $postulatedOffer) {
            $offerIdArray[] = $postulatedOffer->getOffer()->getId();
            $finalArray[$postulatedOffer->getOffer()->getId()]['date'] = $postulatedOffer->getDate();
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offers = $offerRepository->findById($offerIdArray);

        $tagRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
        ;
        $tags = $tagRepository->findAll();

        foreach ($offers as &$offer){
            $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));
            $finalArray[$offer->getId()]['offer'] = $offer;
        }

        return $this->render('ClientBundle:Client:dashboard.html.twig',
            array(
                'appliedOffer' => $finalArray,
                'notifications' => $notificationsArray,
                'favorites' => $favorites,
                'tags' => $tags
            ));
    }

    public function deleteAction(Request $request){

        $session = $request->getSession();
        $user = $this->getUser();
        $idClient = $request->get('id');
        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $candidate = $candidateRepository->findOneBy(isset($idClient)?array('id' => $idClient):array('user' => $user->getId()));

        if(!((isset($user) and in_array('ROLE_CANDIDATE', $user->getRoles())) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.candidate');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_candidate');
        }
        $em = $this->getDoctrine()->getManager();

        if(in_array('ROLE_ADMIN', $user->getRoles())){
            $user = $candidate->getUser();
        }

        $postulatedRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:PostulatedOffers')
        ;
        $postulated = $postulatedRepository->findBy(array('candidate' => $candidate));
        foreach ($postulated as $offer){
            $em->remove($offer);
        }
        
        $favoriteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Favorite')
        ;
        $favorites = $favoriteRepository->findBy(array('candidate' => $candidate));
        foreach ($favorites as $favorite){
            $em->remove($favorite);
        }

        $mail = $user->getEmail();

        $cv = $candidate->getCv();

        if(isset($cv)){
            unlink($cv);
        }

        $em->remove($candidate);
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

        $translated = $this->get('translator')->trans('candidate.delete.deleted');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('rendezvous_home');
    }

    public function showAction(Request $request, $id){
        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and in_array('ROLE_EMPLOYER', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) || $user->getId() != $id)){
            $translated = $this->get('translator')->trans('redirect.employer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_employer');
        }

        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $candidate = $candidateRepository->findOneBy(array('id' => $id));

        return $this->render('ClientBundle:Client:show.html.twig', array(
            'candidate' => $candidate,
        ));
    }

    public function searchAction(){
        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $candidates = $candidateRepository->findAll();

        return $this->render('ClientBundle:Client:search.html.twig', array(
            'candidates' => $candidates,
        ));
    }

    //@TODO put in CRON
    public function eraseUnusedCvsAction(){
        $candidateRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Client')
        ;
        $postulatedOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:PostulatedOffers')
        ;
        $candidates = $candidateRepository->findAll();

        $now = new \datetime();
        $past = $now->modify( '- 2 month' );
        $em = $this->getDoctrine()->getManager();
        foreach ($candidates as $candidate){
            $recentOffers = $postulatedOfferRepository->getRecentPostulatedOffers($candidate);
            if(empty($recentOffers) && $candidate->getCreationDate() < $past){
                $cvLink = $candidate->getCv();
                $coverLink = $candidate->getCoverLetter();

                if(isset($cvLink) and $cvLink != ''){
                    if(file_exists($cvLink)){
                        unlink($cvLink);
                    }
                    $candidate->setCv('');
                }
                if(isset($coverLink) and $coverLink != ''){
                    if(file_exists($coverLink)){
                        unlink($coverLink);
                    }
                    $candidate->setCoverLetter('');
                }
                $em->merge($candidate);
            }
        }
        $em->flush();
        return new Response();
    }

}