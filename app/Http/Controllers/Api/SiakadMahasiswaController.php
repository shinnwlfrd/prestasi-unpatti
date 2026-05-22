<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiakadApiService;
use Illuminate\Http\Request;

class SiakadMahasiswaController extends Controller
{
    public function __construct(
        protected SiakadApiService $siakadService
    ) {}

    /**
     * Search mahasiswa for dropdown (AJAX)
     */
    public function search(Request $request)
    {
        $search = $request->input('search', '');
        $idProdi = $request->input('id_prodi');
        $idFakultas = $request->input('id_fakultas');
        $page = $request->input('page', 1);
        $pageSize = $request->input('page_size', 15);

        $result = $this->siakadService->searchMahasiswa(
            $search,
            $idProdi,
            $idFakultas,
            $page,
            $pageSize
        );

        // Transform for Select2 format
        $data = collect($result['data'] ?? [])->map(function ($mhs) {
            // Extract NIM from registrasi or direct field
            $nim = $mhs['registrasi']['nim'] ?? $mhs['nim'] ?? null;

            return [
                'id' => $mhs['id_mahasiswa'], // Use id_mahasiswa as ID for detail fetch
                'nim' => $nim, // Include NIM for display
                'text' => "{$nim} - {$mhs['nama_mahasiswa']}",
                'nama' => $mhs['nama_mahasiswa'],
                'prodi' => $mhs['program_studi']['nama'] ?? null,
                'fakultas' => $mhs['fakultas']['nama'] ?? null,
            ];
        });

        $status = $this->siakadService->getLastOperationStatus();
        $payload = [
            'success' => $status['success'] ?? true,
            'source' => $status['source'] ?? 'unknown',
            'message' => $status['message'] ?? null,
            'meta' => $status['meta'] ?? [],
            'results' => $data,
            'pagination' => [
                'more' => ($result['meta']['page'] ?? 1) < ($result['meta']['total_pages'] ?? 1),
            ],
        ];

        return response()->json($payload, ($status['success'] ?? true) ? 200 : 502);
    }

    /**
     * Get mahasiswa detail by ID
     */
    public function show(string $idMahasiswa)
    {
        $mahasiswa = $this->siakadService->getMahasiswaById($idMahasiswa);

        if (! $mahasiswa) {
            $status = $this->siakadService->getLastOperationStatus();

            return response()->json([
                'success' => false,
                'source' => $status['source'] ?? 'unknown',
                'message' => $status['message'] ?? 'Mahasiswa tidak ditemukan',
                'meta' => $status['meta'] ?? [],
            ], ($status['success'] ?? false) ? 404 : 502);
        }

        $status = $this->siakadService->getLastOperationStatus();

        return response()->json([
            'success' => true,
            'source' => $status['source'] ?? 'unknown',
            'message' => $status['message'] ?? 'Detail mahasiswa berhasil dimuat.',
            'meta' => $status['meta'] ?? [],
            'data' => $mahasiswa,
        ]);
    }
}
