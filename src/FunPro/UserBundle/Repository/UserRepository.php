<?php

namespace FunPro\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    public function getQueryBuilder()
    {
        return $this->createQueryBuilder('u');
    }
}
