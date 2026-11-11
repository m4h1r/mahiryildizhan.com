<?php

namespace App\Http\Controllers\Alice;

use App\Http\Requests\Alice\PersonFaceRequest;
use App\Http\Resources\Alice\PersonFaceResource;
use App\Models\Person;
use App\Models\PersonFace;
use App\Services\Crm\FaceStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exceptions\DecoderException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Face samples (biometric). Embeddings come back only on GET with ?with_embedding=1.
 */
class PersonFaceController extends AliceController
{
    public function __construct(private readonly FaceStorage $faces) {}

    public function index(int $person): JsonResponse
    {
        $model = Person::find($person);

        return $model ? $this->success(PersonFaceResource::collection($model->faces)) : $this->notFound();
    }

    public function show(int $person, int $face): JsonResponse
    {
        $model = PersonFace::where('person_id', $person)->find($face);

        return $model ? $this->success(new PersonFaceResource($model)) : $this->notFound();
    }

    public function image(int $person, int $face): Response
    {
        $model = PersonFace::where('person_id', $person)->find($face);
        if (! $model || ! $model->image_path || ! Storage::disk(PersonFace::disk())->exists($model->image_path)) {
            return $this->notFound();
        }

        return Storage::disk(PersonFace::disk())->response($model->image_path, null, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function store(PersonFaceRequest $request, int $person): JsonResponse
    {
        $this->auditTable('person_faces');
        $model = Person::find($person);
        if (! $model) {
            return $this->notFound();
        }

        $binary = $request->imageBinary();
        if ($request->filled('image_base64') && $binary === null) {
            return response()->json(['error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => ['image_base64' => ['Geçerli bir base64 görsel değil.']]]], 422);
        }

        $data = collect($request->validated())->except(['image_base64'])->all();
        $data['source'] ??= 'alice';

        try {
            $face = $this->faces->create($model, $data, $request->file('image'), $binary);
        } catch (DecoderException) {
            return response()->json(['error' => ['code' => 'validation_failed', 'message' => 'Doğrulama hatası', 'fields' => ['image' => ['Görsel okunamadı.']]]], 422);
        }

        // A dry run rolls the row back; the file on disk must go too.
        if ($request->attributes->get('is_dry_run')) {
            $face->deleteImageFile();
        }

        return $this->success(new PersonFaceResource($face), 201);
    }

    public function destroy(Request $request, int $person, int $face): JsonResponse
    {
        $this->auditTable('person_faces');
        $model = PersonFace::where('person_id', $person)->find($face);
        if (! $model) {
            return $this->notFound();
        }

        $this->storeAuditOldData($request, $model);
        $this->faces->delete($model);

        return response()->json(['data' => ['id' => $face, 'deleted' => true]]);
    }
}
