<?php

namespace App;

use KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();
        date_default_timezone_set($this->getContainer()->getParameter('timezone'));
    }

}

