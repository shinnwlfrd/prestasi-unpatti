<?php

namespace App\Services;

use App\Models\StudentAchievement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AchievementService
{
    /**
     * Delete a student achievement record and its associated files
     */
    public function deleteAchievement($id): array
    {
        try {
            $achievement = StudentAchievement::where('sa_id', $id)->firstOrFail();

            // Store info for logging
            $studentName = $achievement->student->name ?? 'Unknown';
            $eventName = $achievement->event_name;

            // Delete associated documents
            if ($achievement->certificate && Storage::disk('public')->exists($achievement->certificate)) {
                Storage::disk('public')->delete($achievement->certificate);
            }

            if ($achievement->alternative_document_path && Storage::disk('public')->exists($achievement->alternative_document_path)) {
                Storage::disk('public')->delete($achievement->alternative_document_path);
            }

            // Also delete any other related documents in achievement_documents table
            foreach ($achievement->documents as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $document->delete();
            }

            // Delete the achievement record
            $achievement->delete();

            // Log the deletion
            Log::info("Achievement deleted", [
                'sa_id' => $id,
                'student' => $studentName,
                'event' => $eventName,
                'deleted_by' => auth()->user()->name ?? 'System'
            ]);

            return [
                'success' => true,
                'message' => 'Data prestasi berhasil dihapus'
            ];

        } catch (ModelNotFoundException $e) {
            return [
                'success' => false,
                'status_code' => 404,
                'message' => 'Data prestasi tidak ditemukan'
            ];

        } catch (\Exception $e) {
            Log::error("Failed to delete achievement", [
                'sa_id' => $id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'status_code' => 500,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ];
        }
    }
}
