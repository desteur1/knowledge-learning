<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $admin = new Role();
        $admin->setName('ROLE_ADMIN');
        $manager->persist($admin);

        $client = new Role();
        $client->setName('ROLE_CLIENT');
        $manager->persist($client);

        $manager->flush();
    }
}
