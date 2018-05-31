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

        $email = $request->get('email');
        $form = $this->get('form.factory')->create(ContactType::class);

        if ($request->isMethod('POST')) {
            // Refill the fields in case the form is not valid.
            $form->handleRequest($request);

            if($form->isValid()){
                // Send mail
                if($this->sendEmail($email,$form->getData())){

                    // Everything OK, redirect to wherever you want ! :

                    return $this->redirectToRoute('redirect_to_somewhere_now');
                }else{
                    // An error ocurred, handle
                    var_dump("Errooooor :(");
                }
            }
        }

        return $this->render('AppBundle:Contact:contact.html.twig', array(
            'form' => $form->createView(),
            'email' => $email
        ));
    }

    private function sendEmail($email, $data){
        $message = (new \Swift_Message('Contact'))
            ->setFrom($data['email'])
            ->setTo($email)
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                    'Emails/contact.html.twig',
                    array('name' => $data['name'],
                          'message' => $data['message']
                        )
                ),
                'text/html'
            )
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        return $this->get('mailer')->send($message);
    }


}