<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 30/05/2018
 * Time: 16:21
 */

namespace AppBundle\Controller;

use AppBundle\Entity\LogCredit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Spipu\Html2Pdf\Html2Pdf;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;

class CreditController extends Controller
{

    public function creditAction(Request $request, $id = 0)
    {

        $creditInfo = $this->container->get('app.credit_info');

        $user = $this->getUser();

        $session = $request->getSession();

        if($id != 0 && !(isset($user) and  in_array('ROLE_EMPLOYER', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $withVat = true;
        if($id != 0){
            $vatNumber = $user->getPro()->getVatNumber();
            $countryCode = substr($vatNumber, 0, 2);

            $withVat = $countryCode == 'LU';
        }



        switch ($id) {

            case 1: {
                $price = $withVat ? $creditInfo->getOneCredit() : $creditInfo->getOneCreditWithoutVAT();
                return $this->buyPack($request,$price,1);
                break;
            }
            case 2: {
                $price = $withVat ? $creditInfo->getFiveCredit() : $creditInfo->getFiveCreditWithoutVAT();
                return $this->buyPack($request,$price,5);
                break;

            }
            case 3: {
                $price = $withVat ? $creditInfo->getTenCredit() : $creditInfo->getTenCreditWithoutVAT();
                return $this->buyPack($request,$price,10);
                break;

            }
            default: {
                $creditPro = 0;
                $logsCredit = 0;
                $user =$this->getUser();
                if(isset($user)){
                    $pro = $this->getUser()->getPro();

                    if(isset($pro)){
                        $creditPro = $pro->getCredit();

                        $repository = $this
                            ->getDoctrine()
                            ->getManager()
                            ->getRepository('AppBundle:LogCredit')
                        ;
                        $logsCredit = $repository->findBy(array('pro' => $pro));
                    }

                }

                return $this->render('AppBundle:Credit:credit.html.twig', array(
                    'credit' => $creditPro,
                    'logsCredit' => $logsCredit,
                    'creditService' => $creditInfo
                ));
            }
        }
    }

    public function billsAction(Request $request){
        $currentPage = $request->get('row');
        $sort = $request->get('sort');
        $currentPage = isset($currentPage)?$currentPage:1;
        $sort = isset($sort)?$sort:'DESC';

        $numberOfItem = 50;

        $user = $this->getUser();

        $session = $request->getSession();

        if(!((isset($user) and in_array('ROLE_EMPLOYER', $user->getRoles())) ||  (isset($user) and in_array('ROLE_ADMIN', $user->getRoles())))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $pro = $this->getUser()->getPro();

        $idPro = $request->get('id');

        if(isset($idPro) && in_array('ROLE_ADMIN', $user->getRoles())){
            $repository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('AppBundle:Pro')
            ;
            $pro = $repository->findOneBy(array('id' => $idPro));
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:LogCredit')
        ;
        $logsCredit = $repository->findBy(array('pro' => $pro),array('date' => $sort));

        $countResult = count($logsCredit);

        $finalArray = array_slice($logsCredit, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('AppBundle:Credit:bills.html.twig', [
            'logsCredit' => $finalArray,
            'page' => $currentPage,
            'total' => $totalPage,
            'sort' => $sort,
            'id' => $idPro
        ]);

    }

    private function buyPack(Request $request, $price, $nbrCredit ){

        $user = $this->getUser();

        $session = $request->getSession();

        $vatNumber = $user->getPro()->getVatNumber();
        $countryCode = substr($vatNumber, 0, 2);

        $withVat = $countryCode == 'LU';

        $form = $this->get('form.factory')
            ->createNamedBuilder('payment-form')
            ->add('token', HiddenType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('name',      TextType::class, array(
                'required' => true,
                'label' => 'dashboard.candidate.name'
            ))
            ->add('phone',      TextType::class, array(
                'required' => true,
                'label' => 'form.registration.phone'
            ))
            ->add('location',      TextType::class, array(
                'required' => true,
                'label' => 'offer.location',
            ))
            ->add('zipcode',      TextType::class, array(
                'required' => true,
                'label' => 'price.payment.zipcode'
            ))
            ->add('submit', SubmitType::class)
            ->getForm();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {

                    $data = $form->getData();

                    $this->get('app.client.stripe')->createPremiumCharge($this->getUser(), $data['token'] ,$price,$this->getUser()->getPro(),$nbrCredit);

                    //Logging credit purchase in db
                    $logCredit = new LogCredit();
                    $em = $this->getDoctrine()->getManager();

                    $logCredit->setDate(new \DateTime());
                    $logCredit->setCredit($nbrCredit);
                    $logCredit->setPro($this->getUser()->getPro());
                    $logCredit->setPrice($price);
                    $logCredit->setName($data['name']);
                    $logCredit->setPhone($data['phone']);
                    $logCredit->setLocation($data['location']);
                    $logCredit->setZipcode($data['zipcode']);

                    $em->persist($logCredit);
                    $em->flush();

                    $html2pdf = new Html2Pdf();

                    $html = $this->renderView('AppBundle:Credit:billsPdf.html.twig', array(
                        'logCredit' => $logCredit,
                        'vatNumber' => $logCredit->getPro()->getVatNumber(),
                        'withVat' => $withVat
                    ));
                    $html2pdf->writeHTML($html);
                    $pdfContent = $html2pdf->output('facture.pdf', 'S');

                    $mailer = $this->container->get('swiftmailer.mailer');
                    $message = (new \Swift_Message('achat de crédit'))
                        ->setFrom('rendezvouslu@noreply.lu')
                        ->setTo('accounting@rendezvous.lu')
                        ->setBody(
                            'Someone bought something'
                        )
                        ->attach(\Swift_Attachment::newInstance($pdfContent, 'document.pdf','application/pdf'))
                    ;

                    $message->getHeaders()->addTextHeader(
                        CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
                    );
                    $mailer->send($message);

                    $translated = $this->get('translator')->trans('price.payment.success', array('%credits%' => $nbrCredit));
                    $session->getFlashBag()->add('info', $translated);

                    return $this->redirectToRoute('rendezvous_credit');

                } catch (\Stripe\Error\Base $e) {
                    $this->addFlash('warning', sprintf($this->get('translator')->trans('price.payment.unable'), $e instanceof \Stripe\Error\Card ? lcfirst($e->getMessage()) : $this->get('translator')->trans('price.payment.please')));
                    return $this->redirectToRoute('rendezvous_payment', array('id' => 1));
                }

            }
        }
        return $this->render('AppBundle:Credit:stripePayment.html.twig', [
            'form' => $form->createView(),
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'price' => $price,
            'nbrCredit' => $nbrCredit,
            'withVat' => $withVat
        ]);

    }

    public function generateBillAction(Request $request)
    {
        $id = $request->get('id');
        $session = $request->getSession();
        $user = $this->getUser();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:LogCredit')
        ;
        $logsCredit = $repository->findOneBy(array('id' => $id));

        if(!(isset($user) and  (in_array('ROLE_EMPLOYER', $user->getRoles()) and $logsCredit->getPro() == $user->getPro()) or in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $vatNumber = $user->getPro()->getVatNumber();
        $countryCode = substr($vatNumber, 0, 2);

        $withVat = $countryCode == 'LU';

        $html2pdf = new Html2Pdf();

        $html = $this->renderView('AppBundle:Credit:billsPdf.html.twig', array(
            'logCredit' => $logsCredit,
            'vatNumber' => $vatNumber,
            'withVat' => $withVat
        ));

//        return $this->render('AppBundle:Credit:billsPdf.html.twig', array(
//            'logCredit' => $logsCredit,
//            'vatNumber' => $user->getPro()->getVatNumber(),
//            'withVat' => $withVat
//        ));

        $html2pdf->writeHTML($html);

        return new Response($html2pdf->output(), 200, array(
            'Content-Type' => 'application/pdf'));
    }

    public function generateEstimationAction(Request $request)
    {
        $numberCredit = $request->get('credits');
        $price = $request->get('price');
        $session = $request->getSession();
        $user = $this->getUser();

        if(!(isset($user) and  (in_array('ROLE_EMPLOYER', $user->getRoles())) or in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.pro');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_pro');
        }

        $vatNumber = $user->getPro()->getVatNumber();
        $countryCode = substr($vatNumber, 0, 2);

        $withVat = $countryCode == 'LU';

        $html2pdf = new Html2Pdf();

        $html = $this->renderView('AppBundle:Credit:estimationPdf.html.twig', array(
            'numberCredit' => $numberCredit,
            'price' => $price,
            'vatNumber' => $vatNumber,
            'withVat' => $withVat,
            'name' => $user->getPro()->getName()
        ));

//        return $this->render('AppBundle:Credit:estimationPdf.html.twig', array(
//            'numberCredit' => $numberCredit,
//            'price' => $price,
//            'vatNumber' => $vatNumber,
//            'withVat' => $withVat
//        ));

        $html2pdf->writeHTML($html);

        return new Response($html2pdf->output(), 200, array(
            'Content-Type' => 'application/pdf'));
    }

}