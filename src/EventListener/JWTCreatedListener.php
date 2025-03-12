<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class JWTCreatedListener
{
    #[AsEventListener(event: 'JWTCreatedEvent')]
    public function onJWTCreatedEvent($event): void
    {
        $user = $event->getUser();
        $payload = $event->getData();

        // Ajouter les données personnalisées
        $payload['custom_data'] = [
            'user_id' => $user->getIdUser(),
            'userCompanyId' => $user->getCompany()->getId(),
        ];

        // Mettre à jour le payload du token
        $event->setData($payload);

    }
}
