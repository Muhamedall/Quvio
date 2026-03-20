<?php

namespace App\Http\Controllers\Api;

// ============================================================
// ClientController.php  —  Laravel 13
//
// ENDPOINTS:
//   GET    /api/clients           → index()   list all
//   POST   /api/clients           → store()   create
//   GET    /api/clients/{client}  → show()    get one
//   PUT    /api/clients/{client}  → update()  update
//   DELETE /api/clients/{client}  → destroy() delete
//
// DATA ISOLATION:
//   EVERY query is scoped to auth()->user()->clients()
//   This means a user can NEVER access another user's clients
//   even if they guess the ID in the URL.
//
// ROUTE MODEL BINDING:
//   Laravel 13 automatically resolves {client} in the URL
//   to a Client model instance. But we STILL scope to user
//   because binding alone doesn't check ownership.
//
//   WRONG: public function show(Client $client)
//          → anyone with a valid token can see any client by ID
//
//   RIGHT: We manually find via user scope (see show method)
//          → only the owner can access their own clients
// ============================================================

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    // GET /api/clients
    // Returns all clients belonging to the logged-in user
    // Ordered by name alphabetically
    public function index(Request $request): AnonymousResourceCollection
    {
        $clients = $request->user()
            ->clients()
            ->orderBy('name')
            ->get();

        // ClientResource::collection() transforms each Client model
        // Response shape: { "data": [ {...}, {...} ] }
        return ClientResource::collection($clients);
    }

    // POST /api/clients
    // Creates a new client for the logged-in user
    public function store(StoreClientRequest $request): JsonResponse
    {
        // validated() = only the fields that passed validation rules
        // merge(['user_id' => ...]) = add user_id before creating
        // We never trust user_id from the request — we set it ourselves
        $client = Client::create(
            array_merge(
                $request->validated(),
                ['user_id' => $request->user()->id]
            )
        );

        // 201 Created — standard HTTP status for successful creation
        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/clients/{id}
    // Returns one client with their quotes and invoices counts
    public function show(Request $request, int $id): JsonResponse
    {
        // Find client — scoped to this user (prevents unauthorized access)
        // firstOrFail() = 404 if not found or belongs to another user
        $client = $request->user()
            ->clients()
            ->findOrFail($id);

        return (new ClientResource($client))
            ->response();
    }

    // PUT /api/clients/{id}
    // Updates a client — only the owner can update
    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $client = $request->user()
            ->clients()
            ->findOrFail($id);

        $client->update($request->validated());

        return (new ClientResource($client))
            ->response();
    }

    // DELETE /api/clients/{id}
    // Deletes a client — cascades to quotes and invoices (see migration)
    public function destroy(Request $request, int $id): JsonResponse
    {
        $client = $request->user()
            ->clients()
            ->findOrFail($id);

        $client->delete();

        // 204 No Content — standard for successful delete
        return response()->json(null, 204);
    }
}
