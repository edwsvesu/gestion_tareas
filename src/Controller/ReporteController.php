<?php

namespace App\Controller;

use App\Entity\Tarea;
use App\Service\ReportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reportes', name: 'api_reportes_')]
class ReporteController extends AbstractController
{
    #[Route('/tareas', name: 'tareas', methods: ['GET'])]
    public function report(Request $request, EntityManagerInterface $em, ReportService $reportService): Response
    {
        $qb = $em->getRepository(Tarea::class)->createQueryBuilder('t')
            ->leftJoin('t.usuario', 'u')
            ->addSelect('u');

        if ($estado = $request->query->get('estado')) {
            $qb->andWhere('t.estado = :estado')->setParameter('estado', $estado);
        }
        if ($prioridad = $request->query->get('prioridad')) {
            $qb->andWhere('t.prioridad = :prioridad')->setParameter('prioridad', $prioridad);
        }
        if ($usuarioId = $request->query->get('usuario_id')) {
            $qb->andWhere('u.id = :usuarioId')->setParameter('usuarioId', $usuarioId);
        }
        if ($fechaInicio = $request->query->get('fecha_inicio')) {
            $qb->andWhere('t.fechaCreacion >= :fechaInicio')->setParameter('fechaInicio', new \DateTime($fechaInicio));
        }
        if ($fechaFin = $request->query->get('fecha_fin')) {
            $qb->andWhere('t.fechaCreacion <= :fechaFin')->setParameter('fechaFin', new \DateTime($fechaFin . ' 23:59:59'));
        }

        $tareas = $qb->getQuery()->getResult();
        $formato = $request->query->get('formato', 'pdf');

        if ($formato === 'csv') {
            $csv = $reportService->generarCsv($tareas);
            return new Response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="reporte_tareas.csv"',
            ]);
        }

        $pdf = $reportService->generarPdf($tareas, 'Reporte de Tareas Personalizado');
        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reporte_tareas.pdf"',
        ]);
    }
}
