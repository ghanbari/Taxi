<?php

namespace FunPro\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return array('title' => 'test');
    }
} 