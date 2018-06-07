<?php
namespace AppBundle\Service;
use AppBundle\Entity\Employer;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Charge;
use Stripe\Error\Base;
use Stripe\Stripe;
class StripeClient
{
    private $config;
    private $em;
    private $logger;
    public function __construct($secretKey, array $config, EntityManagerInterface $em, LoggerInterface $logger)
    {
        \Stripe\Stripe::setApiKey($secretKey);
        $this->config = $config;
        $this->em = $em;
        $this->logger = $logger;
    }
    public function createPremiumCharge(User $user, $token, $amount, Employer $employer, $pack)
    {
        try {
            $charge = \Stripe\Charge::create([
                'amount' => $this->config['decimal'] ? $amount * 100 : $amount,
                'currency' => $this->config['currency'],
                'description' => 'Premium blog access',
                'source' => $token,
                'receipt_email' => $user->getEmail(),
            ]);
        } catch (\Stripe\Error\Base $e) {
            $this->logger->error(sprintf('%s exception encountered when creating a premium payment: "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        $user->setChargeId($charge->id);

        $credit = $employer->getCredit();
        switch ($pack){
            case 1:
                $credit += 1;
                break;
            case 2:
                $credit += 10;
                break;
            case 3:
                $credit += 50;
                break;
        }

        $employer->setCredit($credit);

        $this->em->flush();
    }
}