<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class GenerateSeederPdf extends Command
{
    protected $signature = 'pdf:generate-seeder {--split : Generate separate PDF files for each document type}';
    protected $description = 'Generate sample PDF documents for seeder data (certificates, SK, supporting docs)';

    public function handle()
    {
        $this->info('Generating sample PDF documents...');

        // Ensure storage directories exist
        $directories = [
            'app/public/sample-documents',
            'app/public/sample-documents/certificates',
            'app/public/sample-documents/sk',
            'app/public/sample-documents/supporting',
        ];

        foreach ($directories as $dir) {
            $path = storage_path($dir);
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }

        if ($this->option('split')) {
            $this->generateSeparateFiles();
        } else {
            $this->generateCombinedFile();
        }

        $this->info('');
        $this->info('✅ PDF documents generated successfully!');
        $this->info('');
        $this->info('Files location: storage/app/public/sample-documents/');
        $this->info('');
        $this->info('Run "php artisan storage:link" if you haven\'t already to make files accessible via URL.');

        return 0;
    }

    protected function generateCombinedFile()
    {
        $pdf = Pdf::loadView('pdf.seeder-documents');
        $pdf->setPaper('a4', 'portrait');
        
        $path = storage_path('app/public/sample-documents/prestasi_mahasiswa_dokumen_seeder.pdf');
        File::put($path, $pdf->output());
        
        $this->info('📄 Combined PDF: prestasi_mahasiswa_dokumen_seeder.pdf');
    }

    protected function generateSeparateFiles()
    {
        // Certificate 1 - Academic
        $this->generateSinglePdf(
            'pdf.sample.certificate-academic',
            'sample-documents/certificates/sertifikat_akademik_sample.pdf',
            'Sertifikat Akademik'
        );

        // Certificate 2 - Non-Academic
        $this->generateSinglePdf(
            'pdf.sample.certificate-non-academic',
            'sample-documents/certificates/sertifikat_non_akademik_sample.pdf',
            'Sertifikat Non-Akademik'
        );

        // SK Approved
        $this->generateSinglePdf(
            'pdf.sample.sk-approved',
            'sample-documents/sk/sk_disetujui_sample.pdf',
            'SK Disetujui'
        );

        // SK Rejected
        $this->generateSinglePdf(
            'pdf.sample.sk-rejected',
            'sample-documents/sk/sk_ditolak_sample.pdf',
            'SK Ditolak'
        );

        // Supporting Document
        $this->generateSinglePdf(
            'pdf.sample.supporting-doc',
            'sample-documents/supporting/surat_keterangan_sample.pdf',
            'Surat Keterangan'
        );

        // Also generate combined
        $this->generateCombinedFile();
    }

    protected function generateSinglePdf(string $view, string $filename, string $label)
    {
        // Check if view exists, if not use combined view
        if (!view()->exists($view)) {
            $this->warn("⚠️  View {$view} not found, skipping {$label}");
            return;
        }

        $pdf = Pdf::loadView($view);
        $pdf->setPaper('a4', 'portrait');
        
        $path = storage_path('app/public/' . $filename);
        File::put($path, $pdf->output());
        
        $this->info("📄 {$label}: {$filename}");
    }
}
