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
        $tareas = $em->getRepository(Tarea::class)->findTareasByFilters($request->query->all());
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
