<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 03/08/2018
 * Time: 08:25
 */

namespace AppBundle\Repository;


class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function getAdmins()
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM AppBundle:User u WHERE u.roles LIKE :role'
            )->setParameter('role', '%"ROLE_ADMIN"%');

        return $query->execute();
    }

}