<?php

namespace App\Http\Controllers;

use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Models\ValabilitateCursaImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SoferValabilitateCursaImageController extends Controller
{
    private const MAX_IMAGES_PER_CURSA = 10;
    private const MAX_FILE_SIZE_KB = 10240; // 10 MB
    private const ALLOWED_MIMES = ['jpeg', 'jpg', 'png', 'webp'];

    public function index(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): View
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);
        $this->ensureCursaBelongsToValabilitate($valabilitate, $cursa);

        $images = $cursa->images()
            ->latest()
            ->get();

        return view('sofer.valabilitati.curse.imagini.index', [
            'valabilitate' => $valabilitate,
            'cursa' => $cursa,
            'images' => $images,
            'maxImages' => self::MAX_IMAGES_PER_CURSA,
        ]);
    }

    public function store(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);
        $this->ensureCursaBelongsToValabilitate($valabilitate, $cursa);

        abort_if($cursa->images()->count() >= self::MAX_IMAGES_PER_CURSA, 422, 'Limita de imagini a fost atinsă.');

        $validated = $request->validate([
            'image' => ['required', 'file', 'max:' . self::MAX_FILE_SIZE_KB, 'mimes:' . implode(',', self::ALLOWED_MIMES)],
        ]);

        $file = $validated['image'];
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $directory = "valabilitati_curse/{$cursa->id}";
        $path = $file->storeAs($directory, $filename);

        [$width, $height] = $this->extractDimensions($file);

        $cursa->images()->create([
            'uploaded_by_user_id' => $request->user()?->id,
            'path' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => $file->getSize() ?? 0,
            'width' => $width,
            'height' => $height,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('status', 'Imaginea a fost încărcată.');
    }

    public function update(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa, ValabilitateCursaImage $imagine): RedirectResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);
        $this->ensureCursaBelongsToValabilitate($valabilitate, $cursa);
        $this->ensureImageBelongsToCursa($cursa, $imagine);

        $validated = $request->validate([
            'image' => ['required', 'file', 'max:' . self::MAX_FILE_SIZE_KB, 'mimes:' . implode(',', self::ALLOWED_MIMES)],
        ]);

        $file = $validated['image'];
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $directory = "valabilitati_curse/{$cursa->id}";
        $path = $file->storeAs($directory, $filename);

        [$width, $height] = $this->extractDimensions($file);

        $this->removeFileIfExists($imagine->path);

        $imagine->update([
            'path' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => $file->getSize() ?? 0,
            'width' => $width,
            'height' => $height,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('status', 'Imaginea a fost recropată.');
    }

    public function destroy(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa, ValabilitateCursaImage $imagine): RedirectResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);
        $this->ensureCursaBelongsToValabilitate($valabilitate, $cursa);
        $this->ensureImageBelongsToCursa($cursa, $imagine);

        $this->removeFileIfExists($imagine->path);
        $imagine->delete();

        return back()->with('status', 'Imaginea a fost ștearsă.');
    }

    public function stream(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa, ValabilitateCursaImage $imagine): StreamedResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);
        $this->ensureCursaBelongsToValabilitate($valabilitate, $cursa);
        $this->ensureImageBelongsToCursa($cursa, $imagine);

        abort_if($imagine->trashed(), 404);

        abort_unless(Storage::exists($imagine->path), 404);

        $stream = Storage::readStream($imagine->path);
        abort_if($stream === false, 404);
        $mime = $imagine->mime_type ?: 'application/octet-stream';

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) $imagine->size_bytes,
            'Content-Disposition' => 'inline; filename="' . addslashes($imagine->original_name) . '"',
            'Cache-Control' => 'private, max-age=0, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    private function ensureDriverOwnsValabilitate(Request $request, Valabilitate $valabilitate): Valabilitate
    {
        abort_unless((int) $valabilitate->sofer_id === (int) $request->user()?->id, 403);

        return $valabilitate;
    }

    private function ensureCursaBelongsToValabilitate(Valabilitate $valabilitate, ValabilitateCursa $cursa): void
    {
        abort_unless((int) $cursa->valabilitate_id === (int) $valabilitate->id, 404);
    }

    private function ensureImageBelongsToCursa(ValabilitateCursa $cursa, ValabilitateCursaImage $imagine): void
    {
        abort_unless((int) $imagine->valabilitate_cursa_id === (int) $cursa->id, 404);
    }

    private function extractDimensions($file): array
    {
        $dimensions = @getimagesize($file->getRealPath());

        return [
            $dimensions[0] ?? null,
            $dimensions[1] ?? null,
        ];
    }

    private function removeFileIfExists(?string $path): void
    {
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
        }
    }
}
