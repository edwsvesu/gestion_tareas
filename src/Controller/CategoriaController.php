<?php

namespace App\Controller;

use App\Entity\Categoria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/categorias', name: 'api_categorias_')]
class CategoriaController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $categorias = $em->getRepository(Categoria::class)->findAll();
        
        $data = [];
        foreach ($categorias as $categoria) {
            $data[] = [
                'id' => $categoria->getId(),
                'nombre' => $categoria->getNombre()
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nombre'])) {
            return $this->json(['error' => 'El nombre de la categoría es obligatorio'], 400);
        }

        $categoria = new Categoria();
        $categoria->setNombre($data['nombre']);

        $em->persist($categoria);
        $em->flush();

        return $this->json([
            'message' => 'Categoría creada exitosamente',
            'id' => $categoria->getId()
        ], 201);
    }
}
