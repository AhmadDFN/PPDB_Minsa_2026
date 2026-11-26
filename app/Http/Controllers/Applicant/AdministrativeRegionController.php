<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeRegion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdministrativeRegionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $parentCode = $request->query('parent');
        $query = AdministrativeRegion::query()->orderBy('name');

        if ($parentCode === null) {
            $query->where('level', 1);
        } else {
            abort_unless(is_string($parentCode) && preg_match('/\A\d{2,6}\z/', $parentCode) === 1, 404);
            $parent = AdministrativeRegion::query()->where('code', $parentCode)->firstOrFail();
            abort_if($parent->level >= 4, 404);
            $query->where('parent_code', $parent->code);
        }

        return response()->json([
            'items' => $query->get(['code', 'name']),
        ])->header('Cache-Control', 'public, max-age=86400');
    }
}
