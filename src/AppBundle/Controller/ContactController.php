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

        $form = $this->get('form.factory')->create(ContactType::class);

        if ($request->isMethod('POST')) {
            // Refill the fields in case the form is not valid.
            $form->handleRequest($request);

            if($form->isValid()){
                // Send mail
                if($this->sendEmail($form->getData())){

                    // Everything OK, redirect to wherever you want ! :

                    return $this->redirectToRoute('redirect_to_somewhere_now');
                }else{
                    // An error ocurred, handle
                    var_dump("Errooooor :(");
                }
            }
        }

        return $this->render('AppBundle:Contact:contact.html.twig', array(
            'form' => $form->createView()
        ));
    }

    private function sendEmail($data){
        $myappContactMail = 'mycontactmail@mymail.com';
        $myappContactPassword = 'yourmailpassword';

        // In this case we'll use the ZOHO mail services.
        // If your service is another, then read the following article to know which smpt code to use and which port
        // http://ourcodeworld.com/articles/read/14/swiftmailer-send-mails-from-php-easily-and-effortlessly
        $transport = \Swift_SmtpTransport::newInstance('smtp.zoho.com', 465,'ssl')
            ->setUsername($myappContactMail)
            ->setPassword($myappContactPassword);

        $mailer = \Swift_Mailer::newInstance($transport);

        $message = \Swift_Message::newInstance("Our Code World Contact Form ". $data["subject"])
            ->setFrom(array($myappContactMail => "Message by ".$data["name"]))
            ->setTo(array(
                $myappContactMail => $myappContactMail
            ))
            ->setBody($data["message"]."<br>ContactMail :".$data["email"]);

        return $mailer->send($message);
    }


}