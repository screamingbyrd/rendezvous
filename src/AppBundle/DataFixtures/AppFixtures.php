<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 05/06/2018
 * Time: 15:05
 */

namespace AppBundle\DataFixtures;

use AppBundle\Entity\ContractType;
use AppBundle\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;


class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $array = array('freelance','temporary','parttime','fulltime','internship');

        $mainTags = array(
            'Accountancy / Finance',
            'Analysis / Project Management',
            'Architecture / Design',
            'Aviation',
            'Banking',
            'Call-Centre',
            'Construction / Trades',
            'Consulting / Audit / Fiscality',
            'Education / Training',
            'Environement / Renewable energy',
            'Financial Services',
            'Fitness and Leisure',
            'Freelance',
            'Graduate',
            'Healthcare / Childcare / Nursing',
            'Hotels / Restaurants / Cafés',
            'HR / Recruitment',
            'Industry',
            'Insurance',
            'Investments funds',
            'IT / Programming',
            'Legal',
            'Manufacturing / Engineering',
            'Marketing / Market research',
            'Media / New Media',
            'Miscellaneous',
            'Multi-lingual / Linguistic Services',
            'Operative / Manual / Labouring',
            'Pharmaceutical / Science',
            'Property / Auctioneering',
            'Purchasing',
            'Sales',
            'Sales Management',
            'Secretarial / Admin / Clerical',
            'Security',
            'Senior Management / Executive',
            'Technical Support',
            'Telecom',
            'Travel / Tourism',
            'Warehouse / Logistics / Shipping',
            'Work Experience / Internship'
        );

        $subTag = array();

















        foreach ($array as $name){
            $contract = new ContractType();
            $contract->setName($name);
            $manager->persist($contract);
        }

        foreach ($mainTags as $name){
            $tag = new Tag();
            $tag->setName($name);
            $manager->persist($tag);
        }


        $manager->flush();
    }
}