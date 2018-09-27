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

    public function findPro($validated)
    {
        $query = $this->createQueryBuilder('u')->select();
        $query->innerJoin('u.pro', 'p')->andWhere('p.id is not null')
            ->orderBy('p.id', 'asc');

        if(isset($validated)){
            $query->andWhere('p.validated is null');
        }

        $user = $query->getQuery()->getResult();

        return $user;
    }

}