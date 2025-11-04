<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\DocumentWordRequest;
use PDF; // Barryvdh\DomPDF\Facade

use App\Models\DocumentWord;
use App\Models\DocumentWordIstoric;

class DocumentWordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('documentWordReturnUrl');

        $searchNume = $request->searchNume;

        $query = DocumentWord::
            when($searchNume, function ($query, $searchNume) {
                return $query->where('nume', 'like', '%' . $searchNume . '%');
            })
            ->when(! $request->user()?->hasPermission('documente-word-manage'), function ($query) {
                // Users without the admin document permission can see only "operator" documents
                return $query->where('nivel_acces', 2);
            })
            ->orderBy('nume')
            ->latest();

        $documenteWord = $query->simplePaginate(25);

        return view('documenteWord.index', compact('documenteWord', 'searchNume'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('create', DocumentWord::class);

        $request->session()->get('documentWordReturnUrl') ?? $request->session()->put('documentWordReturnUrl', url()->previous());

        $documentWord = new DocumentWord;
        $documentWord->nivel_acces = 2;

        return view('documenteWord.create', compact('documentWord'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentWordRequest $request)
    {
        $this->authorize('create', DocumentWord::class);

        $data = $request->validated();

        if (! $request->user()?->hasPermission('documente-word-manage')) {
            $data['nivel_acces'] = 2;
        }

        $documentWord = DocumentWord::create($data);

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DocumentWord  $documentWord
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, DocumentWord $documentWord)
    {
        // $this->authorize('update', $documentWord);

        // $request->session()->get('documentWordReturnUrl') ?? $request->session()->put('documentWordReturnUrl', url()->previous());

        // return view('documenteWord.show', compact('documentWord'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DocumentWord  $documentWord
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, DocumentWord $documentWord)
    {
        // This will throw an authorization exception if the user is not allowed
        $this->authorize('update', $documentWord);

        // Lock the record
        $documentWord->update([
            'locked_by' => auth()->id(),
            'locked_at' => now(),
        ]);

        $request->session()->get('documentWordReturnUrl') ?? $request->session()->put('documentWordReturnUrl', url()->previous());

        return view('documenteWord.edit', compact('documentWord'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DocumentWord  $documentWord
     * @return \Illuminate\Http\Response
     */
    public function update(DocumentWordRequest $request, DocumentWord $documentWord)
    {
        // This will throw an authorization exception if the user is not allowed
        $this->authorize('update', $documentWord);

        $data = $request->validated();

        $existingImagePaths = $this->extractImagePaths($documentWord->continut);

        if (! $request->user()?->hasPermission('documente-word-manage')) {
            $data['nivel_acces'] = 2;
        }

        // Add the lock release fields to the update data
        $data['locked_by'] = null;
        $data['locked_at'] = null;

        $documentWord->update($data);

        $updatedImagePaths = $this->extractImagePaths($data['continut'] ?? null);
        $pathsToDelete = array_diff($existingImagePaths, $updatedImagePaths);

        $this->deleteImagesFromDisk($pathsToDelete);

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DocumentWord  $documentWord
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, DocumentWord $documentWord)
    {
        // This will throw an authorization exception if the user is not allowed
        $this->authorize('delete', $documentWord);

        $imagePaths = $this->extractImagePaths($documentWord->continut);

        $documentWord->delete();

        $this->deleteImagesFromDisk($imagePaths);

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost șters cu succes!');
    }

    public function unlock(Request $request, DocumentWord $documentWord)
    {
        $this->authorize('unlock', $documentWord);

        // Unlock the record
        $documentWord->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost deblocat cu succes!');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $this->authorize('create', DocumentWord::class);

        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $file = $request->file('image');
        $path = $file->store('', 'documente_word_images');

        return response()->json([
            'url' => route('documente-word.images.show', ['path' => $path], false),
            'path' => $path,
            'disk' => 'documente_word_images',
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ], 201);
    }

    public function showImage(string $path)
    {
        $path = rawurldecode($path);

        abort_if(str_contains($path, '..'), 404);

        $disk = Storage::disk('documente_word_images');

        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path);
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function deleteImagesFromDisk(array $paths): void
    {
        if (empty($paths)) {
            return;
        }

        $disk = Storage::disk('documente_word_images');

        foreach (array_unique(array_filter($paths, fn ($path) => is_string($path) && $path !== '')) as $path) {
            $disk->delete($path);
        }
    }

    private function extractImagePaths(?string $content): array
    {
        if (blank($content)) {
            return [];
        }

        try {
            $document = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return [];
        }

        $paths = [];

        $this->gatherImagePaths($document, $paths);

        return array_values(array_unique($paths));
    }

    private function gatherImagePaths(mixed $node, array &$paths): void
    {
        if (! is_array($node)) {
            return;
        }

        if (($node['type'] ?? null) === 'image') {
            $path = $this->resolveImagePathFromNode($node);

            if ($path !== null) {
                $paths[] = $path;
            }
        }

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $child) {
                $this->gatherImagePaths($child, $paths);
            }
        }
    }

    private function resolveImagePathFromNode(array $node): ?string
    {
        $attributes = $node['attrs'] ?? [];

        if (! is_array($attributes)) {
            return null;
        }

        $path = $attributes['path'] ?? null;

        if (is_string($path) && $path !== '') {
            return $path;
        }

        $src = $attributes['src'] ?? null;

        if (! is_string($src) || $src === '') {
            return null;
        }

        $parsedPath = parse_url($src, PHP_URL_PATH) ?? '';

        if (! is_string($parsedPath) || $parsedPath === '') {
            return null;
        }

        $prefix = '/documente-word/images/';

        if (! str_starts_with($parsedPath, $prefix)) {
            return null;
        }

        $relativePath = rawurldecode(substr($parsedPath, strlen($prefix)));

        return $relativePath !== '' ? $relativePath : null;
    }
}
