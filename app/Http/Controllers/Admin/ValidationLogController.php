<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexValidationLogRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\ValidationLogRepositoryInterface;

class ValidationLogController extends Controller
{
    public function __construct(
        protected ValidationLogRepositoryInterface $validationLogRepo,
        protected UserRepositoryInterface $userRepo
    ) {}

    public function index(IndexValidationLogRequest $request)
    {
        $perPage = $request->input('per_page', 15);
        $logs = $this->validationLogRepo->getWithFilters(
            $request->validated(),
            $perPage
        );

        $validators = $this->userRepo->getValidators();
        $stats = $this->validationLogRepo->getStatistics();

        return view('admin.validation-logs.index', compact('logs', 'validators', 'stats'));
    }
}
