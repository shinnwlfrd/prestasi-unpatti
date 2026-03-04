<?php

namespace App\Http\Traits;

use App\Models\StudentAchievement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait AnomalyAlertsTrait
{
    /**
     * Get all anomalies based on context (active/archive/global)
     * 
     * @param string $context 'active', 'archive', or 'global'
     * @param int|null $periodId
     * @return array
     */
    protected function getAnomalies(string $context = 'active', ?int $periodId = null): array
    {
        return [
            'sla_breach' => $this->getSLABreaches($context, $periodId),
            'missing_documents' => $this->getMissingDocuments($context, $periodId),
            'duplicates' => $this->getDuplicates($context, $periodId),
            'abandoned_drafts' => $this->getAbandonedDrafts($context, $periodId),
            'total_count' => 0, // Will be calculated
            'context' => $context,
            'period_id' => $periodId,
        ];
    }

    /**
     * Get SLA Breach anomalies
     * Active: Pending submissions > 7 working days
     * Archive: Validated submissions that took > 7 working days
     */
    protected function getSLABreaches(string $context, ?int $periodId): array
    {
        $query = StudentAchievement::query()
            ->with(['student', 'achievement', 'academicPeriod']);

        // Apply period filter
        if ($context === 'active' && $periodId) {
            $query->where('academic_period_id', $periodId);
        } elseif ($context === 'archive' && $periodId) {
            $query->where('academic_period_id', $periodId);
        }

        // SLA Breach: Only count PENDING submissions that are overdue (> 7 days)
        // Exclude rejected submissions as they are no longer in the validation pipeline
        $query->whereIn('validation_status', [
            'Menunggu',
            'submitted',
            'faculty_review',
            'faculty_revision',
            'university_review',
            'university_revision',
            'appeal_submitted'
        ])
        ->whereNotIn('validation_status', [
            'faculty_rejected',
            'university_rejected',
            'appeal_rejected'
        ])
        ->where(function ($q) {
            $q->whereRaw('submitted_at IS NOT NULL')
                ->whereRaw('submitted_at < ?', [
                    Carbon::now()->subDays(7)->toDateTimeString()
                ]);
        });

        $results = $query->get();

        return [
            'count' => $results->count(),
            'items' => $results->map(function ($item) {
                $daysElapsed = (int) Carbon::parse($item->submitted_at)->diffInDays(Carbon::now());

                return [
                    'id' => $item->sa_id,
                    'student_name' => $item->student->name ?? 'N/A',
                    'student_nim' => $item->student->student_id ?? 'N/A',
                    'achievement_name' => $item->achievement->name ?? $item->event_name,
                    'submitted_at' => $item->submitted_at,
                    'updated_at' => $item->updated_at,
                    'working_days_elapsed' => $daysElapsed,
                    'status' => $item->validation_status,
                    'period' => $item->academicPeriod->name ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    /**
     * Get Missing Documents anomalies
     * Exclude drafts, find records with no certificate AND no alternative document
     */
    protected function getMissingDocuments(string $context, ?int $periodId): array
    {
        $query = StudentAchievement::query()
            ->with(['student', 'achievement', 'academicPeriod'])
            ->whereNotIn('validation_status', ['draft', 'Draft'])
            ->whereNull('certificate'); // Fixed: use 'certificate' not 'certificate_path'

        // Apply period filter
        if ($periodId && in_array($context, ['active', 'archive'])) {
            $query->where('academic_period_id', $periodId);
        }

        $results = $query->get();

        return [
            'count' => $results->count(),
            'items' => $results->map(function ($item) {
                return [
                    'id' => $item->sa_id,
                    'student_name' => $item->student->name ?? 'N/A',
                    'student_nim' => $item->student->student_id ?? 'N/A',
                    'achievement_name' => $item->achievement->name ?? $item->event_name,
                    'status' => $item->validation_status,
                    'submitted_at' => $item->submitted_at,
                    'period' => $item->academicPeriod->name ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    /**
     * Get Duplicate anomalies
     * Group by student_id, event_name, AND level
     */
    protected function getDuplicates(string $context, ?int $periodId): array
    {
        $dbDriver = DB::getDriverName();
        $concatSql = "GROUP_CONCAT(sa_id)";
        if ($dbDriver === 'pgsql') {
            $concatSql = "string_agg(CAST(sa_id AS TEXT), ',')";
        }

        $query = StudentAchievement::query()
            ->select(
                'student_id',
                'event_name',
                'level',
                DB::raw('COUNT(*) as duplicate_count'),
                DB::raw($concatSql . ' as ids'),
                DB::raw('MAX(academic_period_id) as period_id')
            )
            ->whereNotNull('event_name')
            ->whereNotNull('level')
            ->groupBy('student_id', 'event_name', 'level')
            ->havingRaw('COUNT(*) > 1'); // PostgreSQL compatible - use aggregate function directly

        // Apply period filter
        if ($periodId && in_array($context, ['active', 'archive'])) {
            $query->where('academic_period_id', $periodId);
        }

        $results = $query->get();

        return [
            'count' => $results->count(),
            'items' => $results->map(function ($item) {
                $firstRecord = StudentAchievement::with(['student', 'academicPeriod'])
                    ->find(explode(',', $item->ids)[0]);

                return [
                    'student_id' => $item->student_id,
                    'student_name' => $firstRecord->student->name ?? 'N/A',
                    'student_nim' => $firstRecord->student->student_id ?? 'N/A',
                    'event_name' => $item->event_name,
                    'level' => $item->level,
                    'duplicate_count' => $item->duplicate_count,
                    'ids' => explode(',', $item->ids),
                    'period' => $firstRecord->academicPeriod->name ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    /**
     * Get Abandoned Drafts
     * Drafts not updated for > 30 calendar days
     */
    protected function getAbandonedDrafts(string $context, ?int $periodId): array
    {
        $query = StudentAchievement::query()
            ->with(['student', 'achievement', 'academicPeriod'])
            ->where('validation_status', 'draft')
            ->where('updated_at', '<=', Carbon::now()->subDays(30));

        // Apply period filter
        if ($periodId && in_array($context, ['active', 'archive'])) {
            $query->where('academic_period_id', $periodId);
        }

        $results = $query->get();

        return [
            'count' => $results->count(),
            'items' => $results->map(function ($item) {
                return [
                    'id' => $item->sa_id,
                    'student_name' => $item->student->name ?? 'N/A',
                    'student_nim' => $item->student->student_id ?? 'N/A',
                    'achievement_name' => $item->achievement->name ?? $item->event_name ?? 'Draft Baru',
                    'updated_at' => $item->updated_at,
                    'days_abandoned' => Carbon::parse($item->updated_at)->diffInDays(Carbon::now()),
                    'period' => $item->academicPeriod->name ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    /**
     * Calculate working days between two dates (excluding weekends)
     */
    protected function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($currentDate->isWeekday()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Get total anomaly count
     */
    protected function getTotalAnomalyCount(array $anomalies): int
    {
        return $anomalies['sla_breach']['count'] +
            $anomalies['missing_documents']['count'] +
            $anomalies['duplicates']['count'] +
            $anomalies['abandoned_drafts']['count'];
    }
}
