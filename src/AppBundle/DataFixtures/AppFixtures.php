<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 05/06/2018
 * Time: 15:05
 */

namespace AppBundle\DataFixtures;

use AppBundle\Entity\ContractType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;


class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $array = array('freelance','temporary','parttime','fulltime','internship');

        foreach ($array as $name){
            $contract = new ContractType();
            $contract->setName($name);
            $manager->persist($contract);
        }

        $manager->flush();
    }
}