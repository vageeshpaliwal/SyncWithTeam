<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService
    ) {}

    #[OA\Get(
        path: "/v1/departments",
        summary: "List all departments",
        description: "Retrieve a list of departments for a specific organization.",
        tags: ["Departments"],
        parameters: [
            new OA\Parameter(
                name: "organization_id",
                in: "query",
                description: "The ID of the organization",
                required: false,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "organization_id", type: "integer", example: 1),
                            new OA\Property(property: "name", type: "string", example: "Engineering"),
                            new OA\Property(property: "description", type: "string", example: "Software development team"),
                            new OA\Property(property: "status", type: "boolean", example: true),
                            new OA\Property(property: "created_at", type: "string", format: "date-time"),
                            new OA\Property(property: "updated_at", type: "string", format: "date-time")
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid organization ID"
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $orgId = $request->query('organization_id');
        if (!$orgId) {
            // Default to first organization's departments if none provided to make it easy to test
            $orgId = \App\Models\Organization::first()?->id ?? 1;
        }

        $departments = $this->departmentService->getForOrganization((int)$orgId);

        return response()->json($departments);
    }
}
