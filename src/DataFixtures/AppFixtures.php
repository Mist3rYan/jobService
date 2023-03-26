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
        $recruteurs = [];
        //Recruteur 
        for ($i = 0; $i < 1; $i++) {
            $recruteur = new User();
            $recruteur->setEmail($this->faker->email())
                ->setRoles(['ROLE_RECRUTEUR'])
                ->setPlainPassword('password');
            $recruteurs[] = $recruteur;
            $manager->persist($recruteur);
        }
        //Candidat
        for ($i = 0; $i < 2; $i++) {
            $candidat = new User();
            $candidat->setEmail($this->faker->email())
                ->setRoles(['ROLE_CANDIDAT'])
                ->setPlainPassword('password');

            $manager->persist($candidat);
        }
        //Consultant 
        for ($i = 0; $i < 1; $i++) {
            $consultant = new User();
            $consultant->setEmail($this->faker->email())
                ->setRoles(['ROLE_CONSULTANT'])
                ->setPlainPassword('password');

            $manager->persist($consultant);
        }
        //Annonce
        for ($i = 0; $i < 2; $i++) {
            $annonce = new Annonce();
            $annonce->setPoste($this->faker->jobTitle())
                ->setLieuDeTravail($this->faker->address())
                ->setDescription($this->faker->realText(200))
                ->setRecruteur($recruteurs[mt_rand(0, count($recruteurs) - 1)]);
            $manager->persist($annonce);
        }
        $admin = new User();
        $admin->setEmail('admin@admin.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setPlainPassword('password');

        $manager->persist($admin);
        $manager->flush();
    }
}
