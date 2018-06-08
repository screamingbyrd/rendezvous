<?php

namespace AppBundle\Repository;

/**
 * FeaturedEmployerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FeaturedEmployerRepository extends \Doctrine\ORM\EntityRepository
{

    public function getCurrentFeaturedEmployer()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT fe FROM AppBundle:featuredEmployer fe WHERE fe.archived = 0 AND  fe.startDate < CURRENT_TIMESTAMP() '
            )->execute();
    }

}