<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\PersonFace;
use App\Services\Crm\FaceStorage;
use App\Support\Crm\CrmRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Face samples (biometric, private). Images are never on a public disk; this
 * controller streams them to authenticated admins only.
 */
class PersonFaceController extends Controller
{
    public function __construct(private readonly FaceStorage $faces) {}

    public function image(Person $person, PersonFace $face): StreamedResponse
    {
        abort_unless($face->image_path && Storage::disk(PersonFace::disk())->exists($face->image_path), 404);

        return Storage::disk(PersonFace::disk())->response($face->image_path, null, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, max-age=600',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function store(Request $request, Person $person): RedirectResponse
    {
        // The admin form sends the embedding as JSON text; decode it before validating.
        $embeddingText = trim((string) $request->input('embedding_json', ''));
        if ($embeddingText !== '') {
            $decoded = json_decode($embeddingText, true);
            if (is_array($decoded) && isset($decoded['embedding']) && is_array($decoded['embedding'])) {
                $request->merge(array_intersect_key($decoded, array_flip(['embedding', 'embedding_model', 'bbox', 'landmarks', 'quality'])));
            } elseif (is_array($decoded) && array_is_list($decoded)) {
                $request->merge(['embedding' => $decoded, 'embedding_model' => 'unspecified']);
            } else {
                throw ValidationException::withMessages([
                    'embedding_json' => __('The face data must be a JSON array of numbers, or an object with an "embedding" array.'),
                ]);
            }
        }

        $data = $request->validate(CrmRules::face());
        $this->faces->create($person, $data, $request->file('image'));

        return to_route('admin.people.show', [$person, 'tab' => 'faces'])->with('success', __('Face sample added.'));
    }

    public function primary(Person $person, PersonFace $face): RedirectResponse
    {
        $this->faces->makePrimary($face);

        return to_route('admin.people.show', [$person, 'tab' => 'faces'])->with('success', __('Primary face updated.'));
    }

    public function destroy(Person $person, PersonFace $face): RedirectResponse
    {
        $this->faces->delete($face);

        return to_route('admin.people.show', [$person, 'tab' => 'faces'])->with('success', __('Face sample deleted.'));
    }
}
