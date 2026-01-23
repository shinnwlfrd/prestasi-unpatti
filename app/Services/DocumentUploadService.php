<?php

namespace App\Services;

use App\Models\AchievementDocument;
use App\Models\DocumentRevision;
use App\Models\StudentAchievement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentUploadService
{
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

    const ALLOWED_MIMES = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    public function uploadDocument(
        StudentAchievement $achievement,
        UploadedFile $file,
        string $documentType,
        bool $asDraft = true
    ): AchievementDocument {
        $this->validateFile($file);

        $fileName = $this->generateFileName($file);
        $path = $file->storeAs(
            'achievements/'.$achievement->sa_id,
            $fileName,
            'public'
        );

        $document = AchievementDocument::create([
            'sa_id' => $achievement->sa_id,
            'document_type' => $documentType,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => $asDraft ? AchievementDocument::STATUS_DRAFT : AchievementDocument::STATUS_PENDING,
        ]);

        // Log initial upload
        $document->logRevision(DocumentRevision::ACTION_UPLOADED, 'Dokumen pertama kali diupload');

        return $document;
    }

    public function replaceDocument(
        AchievementDocument $document,
        UploadedFile $file
    ): AchievementDocument {
        if (! $document->canBeEdited()) {
            throw new \InvalidArgumentException('Dokumen tidak dapat diubah karena sudah diverifikasi.');
        }

        $this->validateFile($file);

        // Delete old file
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Upload new file
        $fileName = $this->generateFileName($file);
        $path = $file->storeAs(
            'achievements/'.$document->sa_id,
            $fileName,
            'public'
        );

        // Update document
        $document->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => $document->status === AchievementDocument::STATUS_REVISION
                ? AchievementDocument::STATUS_PENDING
                : $document->status,
            'revision_notes' => null,
        ]);

        // Log replacement
        $document->logRevision(DocumentRevision::ACTION_REPLACED, 'Dokumen diganti dengan file baru');

        return $document->fresh();
    }

    public function uploadMultipleDocuments(
        StudentAchievement $achievement,
        array $files,
        array $documentTypes,
        bool $asDraft = true
    ): array {
        $uploaded = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $type = $documentTypes[$index] ?? 'sertifikat';
                $uploaded[] = $this->uploadDocument($achievement, $file, $type, $asDraft);
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'uploaded' => $uploaded,
            'errors' => $errors,
        ];
    }

    public function addExternalLink(
        StudentAchievement $achievement,
        string $link,
        string $title = 'Link Publikasi',
        bool $asDraft = true
    ): AchievementDocument {
        $document = AchievementDocument::create([
            'sa_id' => $achievement->sa_id,
            'document_type' => AchievementDocument::TYPE_LINK_PUBLIKASI,
            'file_name' => $title,
            'external_link' => $link,
            'status' => $asDraft ? AchievementDocument::STATUS_DRAFT : AchievementDocument::STATUS_PENDING,
        ]);

        $document->logRevision(DocumentRevision::ACTION_UPLOADED, 'Link publikasi ditambahkan');

        return $document;
    }

    public function deleteDocument(AchievementDocument $document): bool
    {
        if (! $document->canBeDeleted()) {
            throw new \InvalidArgumentException('Dokumen tidak dapat dihapus karena sudah diverifikasi.');
        }

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        return $document->delete();
    }

    public function submitDocuments(StudentAchievement $achievement): int
    {
        $count = 0;
        $documents = $achievement->documents()->where('status', AchievementDocument::STATUS_DRAFT)->get();

        foreach ($documents as $document) {
            if ($document->submit()) {
                $count++;
            }
        }

        return $count;
    }

    public function submitSingleDocument(AchievementDocument $document): bool
    {
        return $document->submit();
    }

    protected function validateFile(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException(
                'Ukuran file melebihi batas maksimal '.(self::MAX_FILE_SIZE / 1024 / 1024).'MB'
            );
        }

        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES)) {
            throw new \InvalidArgumentException(
                'Format file tidak didukung. Gunakan: '.implode(', ', self::ALLOWED_EXTENSIONS)
            );
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \InvalidArgumentException(
                'Ekstensi file tidak didukung. Gunakan: '.implode(', ', self::ALLOWED_EXTENSIONS)
            );
        }
    }

    protected function generateFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        return Str::uuid().'.'.$extension;
    }
}
