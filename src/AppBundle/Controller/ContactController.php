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
use AppBundle\Form\ContactType;

class ContactController extends Controller
{

    public function contactAction(Request $request)
    {
        $session = $request->getSession();
        $name = $request->get('name');
        $emailSender = $request->get('email');
        $message = $request->get('message');

        $data = array('name' => $name, 'emailSender' => $emailSender, 'message' => $message);

        if ($request->isMethod('POST')) {
                // Send mail
                if($this->sendEmail('',$data, 'contactUs')){

                    $translated = $this->get('translator')->trans('email.sent');
                    $session->getFlashBag()->add('info', $translated);

                    return $this->redirectToRoute('contact_us_page');
                }else{
                    // An error ocurred, handle
                    var_dump("Errooooor :(");
                }

        }

        return $this->render('AppBundle:Contact:contact.html.twig');
    }

    private function sendEmail($email, $data, $template){
        $mailer = $this->container->get('swiftmailer.mailer');

        $message = (new \Swift_Message('Someone contacted us'))
            ->setFrom('test@test.com')
            ->setTo('test@test.com')
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:'.$template.'.html.twig',
                    array('data' => $data)
                ),
                'text/html'
            )
        ;

        $mailer->send($message);

        return $this->get('mailer')->send($message);
    }


    public function contactUsPageAction(Request $request)
    {

        return $this->render('AppBundle:Contact:contactUs.html.twig');

    }
}