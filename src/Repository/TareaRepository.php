<?php

namespace App\Repository;

use App\Entity\Tarea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tarea>
 */
class TareaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarea::class);
    }

    public function findTareasByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.usuario', 'u')
            ->leftJoin('t.categorias', 'c')
            ->addSelect('u', 'c');

        if (!empty($filters['estado'])) {
            $qb->andWhere('t.estado = :estado')->setParameter('estado', $filters['estado']);
        }
        if (!empty($filters['prioridad'])) {
            $qb->andWhere('t.prioridad = :prioridad')->setParameter('prioridad', $filters['prioridad']);
        }
        if (!empty($filters['usuario_id'])) {
            $qb->andWhere('u.id = :usuarioId')->setParameter('usuarioId', $filters['usuario_id']);
        }
        if (!empty($filters['search'])) {
            $qb->andWhere('t.titulo LIKE :search OR t.descripcion LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['fecha_inicio'])) {
            $qb->andWhere('t.fechaCreacion >= :fechaInicio')->setParameter('fechaInicio', new \DateTime($filters['fecha_inicio']));
        }
        if (!empty($filters['fecha_fin'])) {
            $qb->andWhere('t.fechaCreacion <= :fechaFin')->setParameter('fechaFin', new \DateTime($filters['fecha_fin'] . ' 23:59:59'));
        }

        $sortBy = $filters['sort_by'] ?? 'fechaCreacion';
        $sortOrder = strtoupper($filters['sort_order'] ?? 'DESC');
        if (in_array($sortBy, ['fechaCreacion', 'fechaVencimiento', 'prioridad', 'estado', 'titulo'])) {
            $qb->orderBy('t.' . $sortBy, in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}
