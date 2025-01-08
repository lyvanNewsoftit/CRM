<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Couchbase\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
       $user = new Users();
       $user->setFirstname('Lyvan');
       $user->setLastname('Galonde');
       $user->setEmail('lyvan.galonde@newsoftit.com');
       $user->setPassword('newsoftit');
       $manager->flush();
    }
}
