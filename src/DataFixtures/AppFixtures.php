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
        // 1. Crear Usuarios
        $admin = new Usuario();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $usuario1 = new Usuario();
        $usuario1->setEmail('usuario1@test.com');
        $usuario1->setRoles(['ROLE_USER']);
        $usuario1->setPassword($this->passwordHasher->hashPassword($usuario1, 'user123'));
        $manager->persist($usuario1);

        $usuario2 = new Usuario();
        $usuario2->setEmail('usuario2@test.com');
        $usuario2->setRoles(['ROLE_USER']);
        $usuario2->setPassword($this->passwordHasher->hashPassword($usuario2, 'user123'));
        $manager->persist($usuario2);

        // 2. Crear Categorias
        $catTrabajo = new Categoria();
        $catTrabajo->setNombre('Trabajo');
        $manager->persist($catTrabajo);

        $catPersonal = new Categoria();
        $catPersonal->setNombre('Personal');
        $manager->persist($catPersonal);

        $catUrgente = new Categoria();
        $catUrgente->setNombre('Urgente');
        $manager->persist($catUrgente);

        // 3. Crear Tareas
        for ($i = 1; $i <= 10; $i++) {
            $tarea = new Tarea();
            $tarea->setTitulo('Tarea de prueba ' . $i);
            $tarea->setDescripcion('Esta es la descripción detallada de la tarea número ' . $i);
            
            // Alternar estados
            $estados = ['pendiente', 'en_progreso', 'completada'];
            $tarea->setEstado($estados[array_rand($estados)]);
            
            // Alternar prioridades
            $prioridades = ['baja', 'media', 'alta'];
            $tarea->setPrioridad($prioridades[array_rand($prioridades)]);
            
            // Fecha de vencimiento aleatoria (entre hace 5 días y los próximos 15 días)
            $dias = rand(-5, 15);
            $fecha = new \DateTime();
            $fecha->modify("$dias days");
            $tarea->setFechaVencimiento($fecha);

            // Asignar a un usuario aleatoriamente
            $usuarios = [$admin, $usuario1, $usuario2];
            $tarea->setUsuario($usuarios[array_rand($usuarios)]);

            // Asignar categorías aleatorias
            $categorias = [$catTrabajo, $catPersonal, $catUrgente];
            $numCategorias = rand(1, 2);
            $catsSeleccionadas = (array) array_rand($categorias, $numCategorias);
            foreach ($catsSeleccionadas as $indice) {
                $tarea->addCategoria($categorias[$indice]);
            }

            $manager->persist($tarea);
        }

        $manager->flush();
    }
}
