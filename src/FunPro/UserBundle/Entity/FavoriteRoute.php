<?php

namespace FunPro\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FavoriteRoute
 *
 * @ORM\Table(name="favorite_route")
 * @ORM\Entity(repositoryClass="FunPro\UserBundle\Repository\FavoriteRouteRepository")
 */
class FavoriteRoute
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
