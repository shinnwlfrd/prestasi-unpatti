<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DashboardAggregation extends Model
{
    protected $table = 'dashboard_aggregations';

    protected $fillable = [
        'academic_period_id',
        'validation_status',
        'faculty_id',
        'department_id',
        'program_study_id',
        'level',
        'category_id',
        'total_count',
    ];

    protected $casts = [
        'academic_period_id' => 'integer',
        'category_id' => 'integer',
        'total_count' => 'integer',
    ];

    /**
     * Rebuild the aggregation table from scratch in a safe transaction.
     */
    public static function rebuild(): void
    {
        DB::transaction(function () {
            // Delete all records (truncate or delete)
            self::truncate();

            // Run aggregated query
            $data = DB::table('student_achievements')
                ->join('students', 'student_achievements.student_id', '=', 'students.student_id')
                ->join('achievements', 'student_achievements.achievement_id', '=', 'achievements.id')
                ->whereNull('student_achievements.deleted_at')
                ->whereNull('students.deleted_at')
                ->select([
                    'student_achievements.academic_period_id',
                    'student_achievements.validation_status',
                    'students.faculty_id',
                    'students.department_id',
                    'students.program_study_id',
                    'student_achievements.level',
                    'achievements.category_id',
                    DB::raw('count(student_achievements.sa_id) as total_count'),
                ])
                ->groupBy([
                    'student_achievements.academic_period_id',
                    'student_achievements.validation_status',
                    'students.faculty_id',
                    'students.department_id',
                    'students.program_study_id',
                    'student_achievements.level',
                    'achievements.category_id',
                ])
                ->get();

            $now = now();
            $records = [];
            foreach ($data as $row) {
                $records[] = [
                    'academic_period_id' => $row->academic_period_id,
                    'validation_status' => $row->validation_status,
                    'faculty_id' => $row->faculty_id,
                    'department_id' => $row->department_id,
                    'program_study_id' => $row->program_study_id,
                    'level' => $row->level,
                    'category_id' => $row->category_id,
                    'total_count' => $row->total_count,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($records)) {
                // Chunk the insertion to be friendly to various databases limits
                foreach (array_chunk($records, 500) as $chunk) {
                    self::insert($chunk);
                }
            }
        });
    }

    /**
     * Relationships
     */
    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function category()
    {
        return $this->belongsTo(AchievementCategory::class, 'category_id');
    }
}
