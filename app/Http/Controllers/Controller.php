<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "SyncWithTeam API Documentation",
    description: "API specifications for SyncWithTeam backend services",
    contact: new OA\Contact(email: "support@syncwithteam.com")
)]
#[OA\Server(
    url: "/api",
    description: "Primary API Gateway"
)]
abstract class Controller
{
    //
}

