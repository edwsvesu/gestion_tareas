<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class ReportService
{
    public function generarCsv(array $tareas): string
    {
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, ['ID', 'Titulo', 'Estado', 'Prioridad', 'Fecha Vencimiento', 'Usuario Asignado']);

        foreach ($tareas as $tarea) {
            fputcsv($fp, [
                $tarea->getId(),
                $tarea->getTitulo(),
                $tarea->getEstado(),
                $tarea->getPrioridad(),
                $tarea->getFechaVencimiento() ? $tarea->getFechaVencimiento()->format('Y-m-d') : 'N/A',
                $tarea->getUsuario() ? $tarea->getUsuario()->getEmail() : 'No asignado'
            ]);
        }

        rewind($fp);
        $csvData = stream_get_contents($fp);
        fclose($fp);

        return $csvData;
    }

    public function generarPdf(array $tareas, string $tituloReporte = 'Reporte de Tareas'): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = '<h1>' . $tituloReporte . '</h1>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<thead><tr><th>ID</th><th>Título</th><th>Estado</th><th>Prioridad</th><th>Vencimiento</th><th>Usuario</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($tareas as $tarea) {
            $vencimiento = $tarea->getFechaVencimiento() ? $tarea->getFechaVencimiento()->format('Y-m-d') : 'N/A';
            $usuario = $tarea->getUsuario() ? $tarea->getUsuario()->getEmail() : 'N/A';
            $html .= "<tr>
                        <td>{$tarea->getId()}</td>
                        <td>{$tarea->getTitulo()}</td>
                        <td>{$tarea->getEstado()}</td>
                        <td>{$tarea->getPrioridad()}</td>
                        <td>{$vencimiento}</td>
                        <td>{$usuario}</td>
                      </tr>";
        }

        $html .= '</tbody></table>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }
}
