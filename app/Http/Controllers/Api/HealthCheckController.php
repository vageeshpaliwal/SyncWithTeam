<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthCheckController extends Controller
{
    #[OA\Get(
        path: "/v1/health",
        summary: "Get API service health status",
        description: "Returns the health and operational status of the API services.",
        tags: ["System"],
        responses: [
            new OA\Response(
                response: 200,
                description: "API is healthy",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "healthy"),
                        new OA\Property(property: "timestamp", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error"
            )
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
