<?php
namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AzureOAuthController extends AbstractController
{



    #[Route('/oauth/azure', name: 'azure_oauth')]
    public function login()
    {
        return 'test';
    }
}
