<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdminAuditLogFilterRequest;
use App\Services\AdminActivityLogService;
use Illuminate\View\View;

class AdminAuditLogController
{
    public function __construct(private readonly AdminActivityLogService $activity) {}

    public function index(AdminAuditLogFilterRequest $request): View
    {
        $filters = $request->validated();

        return view('admin.audit-logs.index', [
            'activities' => $this->activity->paginate($filters),
            'summary' => $this->activity->summary($filters),
            'sourceHealth' => $this->activity->sourceHealth(),
            'actions' => $this->activity->availableActions(),
            'actors' => $this->activity->actors(),
            'filters' => $filters,
        ]);
    }
}
