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
            'tag.accounting',
            'tag.admin',
            'tag.advertising',
            'tag.automotive',
            'tag.banking',
            'tag.biotech',
            'tag.customerService',
            'tag.education',
            'tag.engineering',
            'tag.environmental',
            'tag.events',
            'tag.facilities',
            'tag.gov',
            'tag.hr',
            'tag.hospitality',
            'tag.insurance',
            'tag.legal',
            'tag.marketing',
            'tag.nonprofit',
            'tag.oil',
            'tag.real',
            'tag.retail',
            'tag.salon',
            'tag.telecommunications',
            'tag.tv',
            'tag.vet',
            'tag.work',
            'tag.analysis',
            'tag.aviation',
            'tag.call',
            'tag.consulting',
            'tag.financial',
            'tag.freelance',
            'tag.graduate',
            'tag.investments',
            'tag.media',
            'tag.miscellaneous',
            'tag.multi',
            'tag.property',
            'tag.purchasing',
            'tag.security',
            'tag.senior',
            'tag.travel',
            'tag.warehouse',
            'tag.internship',
            'tag.building',
            'tag.business',
            'tag.customerSupport',
            'tag.creative',
            'tag.editorial',
            'tag.it',
            'tag.installation',
            'tag.medical',
            'tag.production',
            'tag.project',
            'tag.quality',
            'tag.rd',
            'tag.sales',
            'tag.security',
            'tag.training',
            'tag.other',
            'tag.architecture',
            'tag.arts',
            'tag.businessFinance',
            'tag.community',
            'tag.farming',
            'tag.life',
            'tag.personal',
            'tag.technology',
            'tag.transportation'
        );

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