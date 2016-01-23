<?php

namespace FunPro\AgentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Agency
 *
 * @ORM\Table(name="agency")
 * @ORM\Entity(repositoryClass="FunPro\AgentBundle\Repository\AgencyRepository")
 */
class Agency extends Agent
{
}
