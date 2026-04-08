<?php

namespace App\Http\Controllers\Api;

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
    public function index(Request $request): AnonymousResourceCollection
    {
        $clients = $request->user()
            ->clients()
            ->latest()          // ← Fix #3: newest first
            ->get();

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create(
            array_merge(
                $request->validated(),
                ['user_id' => $request->user()->id]
            )
        );

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $client = $request->user()
            ->clients()
            ->findOrFail($id);

        return (new ClientResource($client))->response();
    }

    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $client = $request->user()
            ->clients()
            ->findOrFail($id);

        // validated() only contains fields that passed rules
        // array_filter removes null values so we don't overwrite
        // existing data with nulls for optional fields
        $client->update(array_filter(
            $request->validated(),
            fn($v) => $v !== null
        ));

        // Reload fresh from DB before returning
        $client->refresh();

        return (new ClientResource($client))->response();
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $client = $request->user()
            ->clients()
            ->findOrFail($id);

        $client->delete();

        return response()->json(null, 204);
    }
}
