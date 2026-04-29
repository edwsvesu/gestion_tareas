<?php

namespace App\Controller;

use App\Entity\Tarea;
use App\Entity\Categoria;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/tareas', name: 'api_tareas_')]
class TareaController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $tareas = $em->getRepository(Tarea::class)->findTareasByFilters($request->query->all());

        $data = [];
        foreach ($tareas as $tarea) {
            $data[] = $this->formatTarea($tarea);
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Tarea $tarea): JsonResponse
    {
        return $this->json($this->formatTarea($tarea));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['titulo'])) {
            return $this->json(['error' => 'El título es obligatorio.'], 400);
        }

        $tarea = new Tarea();
        $tarea->setTitulo($data['titulo']);
        $tarea->setDescripcion($data['descripcion'] ?? null);
        $tarea->setEstado($data['estado'] ?? 'pendiente');
        $tarea->setPrioridad($data['prioridad'] ?? 'media');

        if (!empty($data['fechaVencimiento'])) {
            try {
                $tarea->setFechaVencimiento(new \DateTime($data['fechaVencimiento']));
            } catch (\Exception $e) {
                return $this->json(['error' => 'Formato de fecha inválido.'], 400);
            }
        }

        $usuario = $this->getUser();
        if (!empty($data['usuario_id']) && $this->isGranted('ROLE_ADMIN')) {
            $usuario = $em->getRepository(Usuario::class)->find($data['usuario_id']);
        }
        $tarea->setUsuario($usuario);

        if (!empty($data['categorias']) && is_array($data['categorias'])) {
            foreach ($data['categorias'] as $catId) {
                $categoria = $em->getRepository(Categoria::class)->find($catId);
                if ($categoria) {
                    $tarea->addCategoria($categoria);
                }
            }
        }

        $em->persist($tarea);
        $em->flush();

        return $this->json($this->formatTarea($tarea), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Tarea $tarea, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['titulo'])) {
            $tarea->setTitulo($data['titulo']);
        }
        if (isset($data['descripcion'])) {
            $tarea->setDescripcion($data['descripcion']);
        }
        if (isset($data['estado'])) {
            $tarea->setEstado($data['estado']);
        }
        if (isset($data['prioridad'])) {
            $tarea->setPrioridad($data['prioridad']);
        }
        if (isset($data['fechaVencimiento'])) {
            try {
                $tarea->setFechaVencimiento(new \DateTime($data['fechaVencimiento']));
            } catch (\Exception $e) {
                return $this->json(['error' => 'Formato de fecha inválido.'], 400);
            }
        }

        if (isset($data['categorias']) && is_array($data['categorias'])) {
            foreach ($tarea->getCategorias() as $cat) {
                $tarea->removeCategoria($cat);
            }
            foreach ($data['categorias'] as $catId) {
                $categoria = $em->getRepository(Categoria::class)->find($catId);
                if ($categoria) {
                    $tarea->addCategoria($categoria);
                }
            }
        }

        $em->flush();

        return $this->json($this->formatTarea($tarea));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Tarea $tarea, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($tarea);
        $em->flush();

        return $this->json(['message' => 'Tarea eliminada exitosamente.']);
    }

    private function formatTarea(Tarea $tarea): array
    {
        $categorias = [];
        foreach ($tarea->getCategorias() as $categoria) {
            $categorias[] = [
                'id' => $categoria->getId(),
                'nombre' => $categoria->getNombre()
            ];
        }

        return [
            'id' => $tarea->getId(),
            'titulo' => $tarea->getTitulo(),
            'descripcion' => $tarea->getDescripcion(),
            'estado' => $tarea->getEstado(),
            'prioridad' => $tarea->getPrioridad(),
            'fechaVencimiento' => $tarea->getFechaVencimiento() ? $tarea->getFechaVencimiento()->format('Y-m-d H:i:s') : null,
            'fechaCreacion' => $tarea->getFechaCreacion()->format('Y-m-d H:i:s'),
            'fechaModificacion' => $tarea->getFechaModificacion()->format('Y-m-d H:i:s'),
            'usuario' => [
                'id' => $tarea->getUsuario()->getId(),
                'email' => $tarea->getUsuario()->getEmail()
            ],
            'categorias' => $categorias
        ];
    }
}
