<?php

namespace App\DataFixtures;

use App\Entity\Annonce;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }
    public function load(ObjectManager $manager): void
    {
        //Annonce
        for ($i = 0; $i < 25; $i++) {
            $annonce = new Annonce();
            $annonce->setPoste($this->faker->jobTitle())
                ->setLieuDeTravail($this->faker->address())
                ->setDescription($this->faker->realText(200))
                ->setIdRecruteurCreate($this->faker->numberBetween(1, 6));
            $manager->persist($annonce);
        }
        //Candidat
        for ($i = 0; $i < 25; $i++) {
            $candidat = new User();
            $candidat->setEmail($this->faker->email())
                ->setRoles(['ROLE_CANDIDAT'])
                ->setPlainPassword('password');

            $manager->persist($candidat);
        }
        //Recruteur 
        for ($i = 0; $i < 6; $i++) {
            $recruteur = new User();
            $recruteur->setEmail($this->faker->email())
                ->setRoles(['ROLE_RECRUTEUR'])
                ->setPlainPassword('password');

            $manager->persist($recruteur);
        }
        //Consultant 
        for ($i = 0; $i < 2; $i++) {
            $consultant = new User();
            $consultant->setEmail($this->faker->email())
                ->setRoles(['ROLE_CONSULTANT'])
                ->setPlainPassword('password');

            $manager->persist($consultant);
        }
        $admin = new User();
        $admin->setEmail('admin@admin.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setPlainPassword('password');

        $manager->persist($admin);
        $manager->flush();
    }
}
