<?php

namespace FunPro\UserBundle\Entity;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

class TokenListener
{
    /**
     * @ORM\PrePersist()
     */
    public function prePersist(Token $token, LifecycleEventArgs $event)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $event->getObjectManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->update('FunPro\UserBundle\Entity\Token', 't')
            ->set('t.expired', 1)
            ->where($queryBuilder->expr()->eq('t.user', ':user'))
            ->setParameter('user', $token->getUser())
            ->getQuery()
            ->execute();
    }
}
