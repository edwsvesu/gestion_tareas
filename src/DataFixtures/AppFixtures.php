<?php

namespace App\DataFixtures;

use App\Entity\Categoria;
use App\Entity\Tarea;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Usuarios de prueba
        $admin = new Usuario();
        $admin->setEmail('usuarioadmin@gmail.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, '123456'));
        $manager->persist($admin);

        $usuario1 = new Usuario();
        $usuario1->setEmail('usuario1@gmail.com');
        $usuario1->setRoles(['ROLE_USER']);
        $usuario1->setPassword($this->passwordHasher->hashPassword($usuario1, '123456'));
        $manager->persist($usuario1);

        // Categorias
        $catTrabajo = new Categoria();
        $catTrabajo->setNombre('Trabajo');
        $manager->persist($catTrabajo);

        $catPersonal = new Categoria();
        $catPersonal->setNombre('Personal');
        $manager->persist($catPersonal);

        $catUrgente = new Categoria();
        $catUrgente->setNombre('Urgente');
        $manager->persist($catUrgente);

        $manager->flush();
    }
}
