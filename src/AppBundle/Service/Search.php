<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 10/07/2018
 * Time: 11:41
 */

namespace AppBundle\Service;


class Search
{

    public function __construct($finder)
    {
        $this->finder = $finder;
    }

    public function searchPro($searchParam)
    {
        $searchParam = (array)json_decode($searchParam);
        $boolQuery = new \Elastica\Query\BoolQuery();

        if($searchParam['keywords'] != ''){
            $keywordBool = new \Elastica\Query\BoolQuery();
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('name', $searchParam['keywords']);
            $keywordBool->addShould($fieldQuery);
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('description', $searchParam['keywords']);
            $keywordBool->addShould($fieldQuery);
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('location', $searchParam['keywords']);
            $keywordBool->addShould($fieldQuery);
            $boolQuery->addMust($keywordBool);
        }

        if($searchParam['location'] != ''){
            $locationBool = new \Elastica\Query\BoolQuery();
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('location', $searchParam['location']);
            $locationBool->addShould($fieldQuery);
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('city', $searchParam['location']);
            $locationBool->addShould($fieldQuery);
            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('zipcode', $searchParam['location']);
            $locationBool->addShould($fieldQuery);
            $boolQuery->addMust($locationBool);
        }

        $query = new \Elastica\Query($boolQuery);

        $data = $this->finder->find($query,3000);

        return $data;
    }

}