<?php

namespace App\Command;

use App\Entity\Tarea;
use App\Service\ReportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:reporte-diario',
    description: 'Genera un reporte diario de las tareas pendientes y en progreso.',
)]
class ReporteDiarioCommand extends Command
{
    private EntityManagerInterface $em;
    private ReportService $reportService;
    private string $projectDir;

    public function __construct(
        EntityManagerInterface $em,
        ReportService $reportService,
        #[Autowire('%kernel.project_dir%')] string $projectDir
    ) {
        parent::__construct();
        $this->em = $em;
        $this->reportService = $reportService;
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Recopilando tareas pendientes y en progreso...');

        $qb = $this->em->getRepository(Tarea::class)->createQueryBuilder('t')
            ->where("t.estado IN ('pendiente', 'en_progreso')")
            ->orderBy('t.prioridad', 'DESC');

        $tareas = $qb->getQuery()->getResult();

        if (empty($tareas)) {
            $io->success('No hay tareas pendientes ni en progreso hoy. No se generará reporte.');
            return Command::SUCCESS;
        }

        $pdfContent = $this->reportService->generarPdf($tareas, 'Reporte Diario de Tareas Activas');

        $fs = new Filesystem();
        $reportDir = $this->projectDir . '/var/reportes';
        if (!$fs->exists($reportDir)) {
            $fs->mkdir($reportDir);
        }

        $fileName = 'reporte_diario_' . date('Y-m-d') . '.pdf';
        $fs->dumpFile($reportDir . '/' . $fileName, $pdfContent);

        $io->success(sprintf('Reporte generado exitosamente en %s', $reportDir . '/' . $fileName));

        return Command::SUCCESS;
    }
}
