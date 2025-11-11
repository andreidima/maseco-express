<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValabilitateRequest;
use App\Models\Masini\Masina;
use App\Models\Valabilitate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ValabilitateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Valabilitate::class, 'valabilitate');
    }

    public function index(): View
    {
        /** @var LengthAwarePaginator $valabilitati */
        $valabilitati = Valabilitate::query()
            ->with('masina')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('valabilitati.index', [
            'valabilitati' => $valabilitati,
        ]);
    }

    public function create(): View
    {
        return view('valabilitati.create', [
            'valabilitate' => new Valabilitate(),
            'masini' => $this->masinaOptions(),
        ]);
    }

    public function store(ValabilitateRequest $request): RedirectResponse
    {
        $valabilitate = Valabilitate::create(
            $request->validated() + ['total_curse' => 0]
        );

        return redirect()
            ->route('valabilitati.show', $valabilitate)
            ->with('status', 'Valabilitatea a fost creată cu succes.');
    }

    public function show(Valabilitate $valabilitate): View
    {
        $valabilitate->load(['masina', 'curse' => fn ($query) => $query->orderByDesc('plecare_la')->orderByDesc('created_at')]);

        return view('valabilitati.show', [
            'valabilitate' => $valabilitate,
        ]);
    }

    public function edit(Valabilitate $valabilitate): View
    {
        return view('valabilitati.edit', [
            'valabilitate' => $valabilitate,
            'masini' => $this->masinaOptions(),
        ]);
    }

    public function update(ValabilitateRequest $request, Valabilitate $valabilitate): RedirectResponse
    {
        $valabilitate->update($request->validated());

        return redirect()
            ->route('valabilitati.show', $valabilitate)
            ->with('status', 'Valabilitatea a fost actualizată cu succes.');
    }

    public function destroy(Valabilitate $valabilitate): RedirectResponse
    {
        $valabilitate->delete();

        return redirect()
            ->route('valabilitati.index')
            ->with('status', 'Valabilitatea a fost ștearsă.');
    }

    protected function resourceAbilityMap(): array
    {
        return [
            'index' => 'viewAny',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'destroy' => 'delete',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function masinaOptions(): array
    {
        return Masina::query()
            ->orderBy('numar_inmatriculare')
            ->pluck('numar_inmatriculare', 'id')
            ->map(fn (string $value) => trim($value))
            ->all();
    }
}
