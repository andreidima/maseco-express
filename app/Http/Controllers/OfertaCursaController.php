<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OfertaCursa;
use App\Http\Requests\OfertaCursaRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Webklex\IMAP\Facades\Client;
use Illuminate\Database\Eloquent\Builder;


class OfertaCursaController extends Controller
{
    /**
     * Renders the index page with initial data.
     * - Reads filters once (extractFilters)
     * - Applies them to the query (applyFilters)
     * - Paginates (25/pg)
     * - Passes filters back to Blade under the same keys your view already expects
     */
    public function index(Request $request)
    {
        // Reset any stored return URL (your existing behavior)
        $request->session()->forget('returnUrl');

        // 1) Read all filters (centralized)
        $filters = $this->extractFilters($request);

        // 2) Build the base query and apply filters
        $query = OfertaCursa::query();
        $query = $this->applyFilters($query, $filters);

        // 3) Sort + paginate (server-rendered first page)
        $oferte = $query->latest('data_primirii')->simplePaginate(25);

        // 4) Return the view, mapping the internal $filters back to the names your Blade expects
        return view('oferte_curse.index', [
            'oferte'                     => $oferte,
            'searchIncarcareCodPostal'   => $filters['incarcare_cp'],
            'searchIncarcareLocalitate'  => $filters['incarcare_loc'],
            'searchIncarcareDataOra'     => $filters['incarcare_data'],
            'searchDescarcareCodPostal'  => $filters['descarcare_cp'],
            'searchDescarcareLocalitate' => $filters['descarcare_loc'],
            'searchDescarcareDataOra'    => $filters['descarcare_data'],
            'searchGreutateMin'          => $filters['greutate_min'],
            'searchGreutateMax'          => $filters['greutate_max'],
        ]);
    }

    /**
     * Lightweight endpoint polled every 10s by the client.
     * Returns ONLY:
     *   - rows changed since ?since=… (as <tr> HTML snippets),
     *   - IDs soft-deleted since ?since=…,
     *   - 'now' (server clock) so the client can advance the window.
     *
     * Also:
     *   - Supports a one-time ?bootstrap=1 call (returns just 'now')
     *   - Uses ETag so when nothing changed, server can reply 304 (no body)
     */
    public function changes(Request $request)
    {
        // --- 0) Bootstrap fast path: on first call, client asks only for server time.
        if ($request->boolean('bootstrap')) {
            $serverNow = now()->toIso8601String();

            return response()
                ->json([
                    'now'          => $serverNow,
                    'rows'         => [],
                    'deleted_ids'  => [],
                ])
                ->header('X-Server-Now', $serverNow);
        }

        // --- 1) Inputs: "since" window + filters
        $sinceIso  = $request->query('since', '1970-01-01T00:00:00Z');
        $since     = Carbon::parse($sinceIso);
        $serverNow = now()->toIso8601String();

        $filters = $this->extractFilters($request);

        // --- 2) Query UPDATED/NEW rows since $since (select only needed cols)
        $changedQuery = OfertaCursa::query()->select([
            'id',
            'data_primirii',
            'incarcare_cod_postal','incarcare_localitate','incarcare_data_ora',
            'descarcare_cod_postal','descarcare_localitate','descarcare_data_ora',
            'greutate','detalii_cursa','gmail_link',
            'updated_at',
        ]);

        $changedQuery = $this->applyFilters($changedQuery, $filters)
            ->where('updated_at', '>', $since)
            ->orderBy('updated_at', 'asc') // stable order
            ->limit(200);

        $changed = $changedQuery->get();

        // Render ONE <tr> per Oferta using your existing partial (keeps markup identical)
        $rowsPayload = $changed->map(function ($oferta) {
            $html = view('oferte_curse._rows', ['oferte' => collect([$oferta])])->render();
            return ['id' => $oferta->id, 'html' => trim($html)];
        });

        // --- 3) Query SOFT-DELETES since $since (requires SoftDeletes on model)
        $deletedQuery = OfertaCursa::onlyTrashed()->select(['id','deleted_at']);
        $deletedQuery = $this->applyFilters($deletedQuery, $filters)
            ->where('deleted_at', '>', $since)
            ->orderBy('deleted_at', 'asc')
            ->limit(500);

        $deleted     = $deletedQuery->get();
        $deletedIds  = $deleted->pluck('id')->values(); // array-like for JSON

        // --- 4) Build the payload BEFORE computing ETag
        $payload = [
            'now'          => $serverNow,   // client will use this as next ?since
            'rows'         => $rowsPayload, // changed/inserted rows as HTML <tr>
            'deleted_ids'  => $deletedIds,  // IDs to remove client-side
        ];

        // --- 5) ETag: must be stable when nothing changed.
        // Use the set of updated IDs + their max(updated_at) AND deleted IDs + their max(deleted_at).
        $maxUpdatedAt = $changed->max('updated_at');
        $maxDeletedAt = $deleted->max('deleted_at');

        $maxUpdatedStr = $maxUpdatedAt
            ? (is_string($maxUpdatedAt) ? $maxUpdatedAt : $maxUpdatedAt->toDateTimeString())
            : 'none';

        $maxDeletedStr = $maxDeletedAt
            ? (is_string($maxDeletedAt) ? $maxDeletedAt : $maxDeletedAt->toDateTimeString())
            : 'none';

        $idsUpdated = $changed->pluck('id')->all();
        $idsDeleted = $deletedIds->all();

        $etag = md5(json_encode([$idsUpdated, $maxUpdatedStr, $idsDeleted, $maxDeletedStr]));

        // --- 6) Build the response with ETag and an explicit clock header.
        $response = response()
            ->json($payload)
            ->setEtag($etag)
            ->header('X-Server-Now', $serverNow);

        // If client sent the same ETag in If-None-Match -> 304 Not Modified (no body)
        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response; // 200 OK with JSON body
    }

    /**
     * Reads ALL filter values from the request in one place.
     * This keeps index() and changes() in sync and avoids duplicated code.
     */
    private function extractFilters(Request $r): array
    {
        return [
            // Loading (pickup) filters
            'incarcare_cp'    => trim($r->searchIncarcareCodPostal    ?? ''),
            'incarcare_loc'   => trim($r->searchIncarcareLocalitate   ?? ''),
            'incarcare_data'  => trim($r->searchIncarcareDataOra      ?? ''),

            // Unloading (drop-off) filters
            'descarcare_cp'   => trim($r->searchDescarcareCodPostal   ?? ''),
            'descarcare_loc'  => trim($r->searchDescarcareLocalitate  ?? ''),
            'descarcare_data' => trim($r->searchDescarcareDataOra     ?? ''),

            // Weight range
            'greutate_min'    => trim($r->searchGreutateMin           ?? ''),
            'greutate_max'    => trim($r->searchGreutateMax           ?? ''),
        ];
    }

    /**
     * Applies the filters to ANY Eloquent Builder and returns the same Builder.
     * – “Digits-only postal code” uses REGEXP_SUBSTR to match the first run of digits (prefix).
     * – All other filters use LIKE contains.
     * – Weight is applied as numeric range when provided.
     */
    private function applyFilters(Builder $q, array $f): Builder
    {
        // Helper for the “digits-only postal code” behavior you had
        $postal = function (Builder $q, string $column, string $v) {
            if ($v === '') return;
            if (preg_match('/^\d+$/', $v)) {
                // Compare against the FIRST run of digits and treat input as prefix
                $q->whereRaw("REGEXP_SUBSTR($column, '[0-9]+') LIKE ?", [$v.'%']);
            } else {
                // Fallback to a regular contains match
                $q->where($column, 'LIKE', "%{$v}%");
            }
        };

        // Apply each filter only when a value is present
        $q->when($f['incarcare_cp'],   fn ($qb, $v) => $postal($qb, 'incarcare_cod_postal',   $v));
        $q->when($f['descarcare_cp'],  fn ($qb, $v) => $postal($qb, 'descarcare_cod_postal',  $v));

        $q->when($f['incarcare_loc'],  fn ($qb, $v) => $qb->where('incarcare_localitate',   'LIKE', "%{$v}%"));
        $q->when($f['incarcare_data'], fn ($qb, $v) => $qb->where('incarcare_data_ora',     'LIKE', "%{$v}%"));

        $q->when($f['descarcare_loc'], fn ($qb, $v) => $qb->where('descarcare_localitate',  'LIKE', "%{$v}%"));
        $q->when($f['descarcare_data'],fn ($qb, $v) => $qb->where('descarcare_data_ora',    'LIKE', "%{$v}%"));

        $q->when($f['greutate_min'],   fn ($qb)     => $qb->where('greutate', '>=', $f['greutate_min']));
        $q->when($f['greutate_max'],   fn ($qb)     => $qb->where('greutate', '<=', $f['greutate_max']));

        return $q;
    }






    public function create(Request $request)
    {
        // Store current URL so we can return here after store/update:
        $request->session()->get('returnUrl')
            ?: $request->session()->put('returnUrl', url()->previous());

        return view('oferte_curse.save');
    }

    public function store(OfertaCursaRequest $request)
    {
        $oferta = OfertaCursa::create($request->validated());

        return redirect(
            $request->session()->get('returnUrl', route('oferte-curse.index'))
        )
        ->with('success', "Oferta „{$oferta->email_subiect}” a fost adăugată cu succes.");
    }

    public function show(Request $request, OfertaCursa $oferta)
    {
        // Preserve return URL:
        $request->session()->get('returnUrl')
            ?: $request->session()->put('returnUrl', url()->previous());

        return view('oferte_curse.show', ['oferta' => $oferta]);
    }

    public function edit(Request $request, OfertaCursa $oferta)
    {
        $request->session()->get('returnUrl')
            ?: $request->session()->put('returnUrl', url()->previous());

        return view('oferte_curse.save', ['oferta' => $oferta]);
    }

    public function update(OfertaCursaRequest $request, OfertaCursa $oferta)
    {
        $oferta->update($request->validated());

        return redirect(
            $request->session()->get('returnUrl', route('oferte-curse.index'))
        )
        ->with('success', "Oferta „{$oferta->email_subiect}” a fost actualizată cu succes.");
    }

    public function destroy(Request $request, OfertaCursa $oferta)
    {
        $oferta->delete();

        return back()->with('success', "Oferta „{$oferta->email_subiect}” a fost ștearsă cu succes.");
    }

    public function citireAutomataEmailuri(Request $request)
    {
        // ── Configurație ───────────────────────────────────────────────────────
        $batchSize = 1;

        $parsingOptions = [
            'mandatoryFields'   => [
                'incarcare_cod_postal',
                'incarcare_localitate',
                'incarcare_data_ora',
                'descarcare_cod_postal',
                'descarcare_localitate',
                'descarcare_data_ora',
                'detalii_cursa',
            ],
            // 'maxMissingAllowed' => 1,
            'maxMissingAllowed' => 7,
            'enableValidation'  => true,
        ];

        $senders = [
            'ekurier'         => 'no-reply@e-kurier.net',
            'couriernet'      => 'noreply@couriernet.email',
            'priorityfreight' => 'ops@priorityfreight.com',
        ];

        // ── Conectare & preluare mesaje necitite, cele mai noi primele ─────────
        $client = Client::account('default');
        $client->connect();

        // 1) Collect INBOX + subfolders
        // $inbox   = $client->getFolder('INBOX');

// 1) Fetch exactly the same top‑level list
$folders = $client->getFolders(false)

// 2) Filter only the Standard container + its children
    ->filter(fn($f) => str_starts_with($f->full_name, 'INBOX.Standard'));

// 3) (Optional) verify the paths
// dd($folders->pluck('full_name'));

        $totalChecked = 0;
        $totalSaved   = 0;

        // 2) Loop each folder
        foreach ($folders as $folder) {
            $mesaje = $folder->query()
                            ->unseen()
                            ->setFetchOrderDesc()
                            ->limit($batchSize)
                            ->get();

            echo $folder->name . "\n";
            echo "Mesaje citite:" . $mesaje->count() . "\n\n";
            // force PHP/webserver to send this chunk now:
            // flush();

            foreach ($mesaje as $msg) {
                $totalChecked++;

                // ── Metadate comune ───────────────────────────────────────────
                $rawSubject = $msg->getSubject();
                $unfolded   = preg_replace('/\r?\n[ \t]+/', '', $rawSubject);
                $subject    = mb_decode_mimeheader($unfolded);
                $subject    = preg_replace('/\s+/', ' ', trim($subject));
                $subject    = preg_replace('/(\d)\.\s+(\d)/', '$1.$2', $subject);

                // 1) Convert the Attribute to a Carbon instance
                /** @var \Carbon\Carbon $dateCarbon */
                $dateCarbon    = $msg->getDate()->toDate();
                // 2) Shift it into your app tz (Europe/Bucharest)
                $localDate     = $dateCarbon->setTimezone(config('app.timezone'));
                // 3) Format for DB & Gmail‑link
                $data_primirii = $localDate->format('Y-m-d H:i:s');
                $dateYmd       = $localDate->format('Y/m/d');

                $gmail_link   = "https://mail.google.com/mail/u/0/#search/"
                            . rawurlencode("subject:\"{$subject}\" after:{$dateYmd} before:{$dateYmd}");

                // curățare HTML → text simplu
                $html      = $msg->getHTMLBody() ?? '';
                $step1     = preg_replace('/<br\s*\/?>/i', "\n", $html);
                $email_text = html_entity_decode(strip_tags($step1));

                // expeditor
                $email_expeditor = strtolower($msg->getFrom()[0]->mail);

                if (in_array($email_expeditor, $senders, true)) {
                    // ── Inițializare câmpuri românești ─────────────────────────
                    $campuri = [
                        'incarcare_cod_postal'   => null,
                        'incarcare_localitate'   => null,
                        'incarcare_data_ora'     => null,
                        'descarcare_cod_postal'  => null,
                        'descarcare_localitate'  => null,
                        'descarcare_data_ora'    => null,
                        'detalii_cursa'          => null,
                    ];

                    // care sursă?
                    $sursa = array_search($email_expeditor, $senders, true);

                    switch ($sursa) {
                        case 'ekurier':
                            $dom   = new \DOMDocument();
                            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
                            $xp    = new \DOMXPath($dom);
                            $nodes = $xp->query('//div[contains(., "Neues Frachtangebot")]');
                            $text  = $nodes->length
                                    ? trim($nodes->item(0)->textContent)
                                    : '';
                            $linii = array_values(array_filter(
                                preg_split('/\r?\n/', $text),
                                fn($l) => trim($l) !== ''
                            ));
                            foreach ($linii as $linie) {
                                if (preg_match('/^ab:\s*(\S+)\s+(.+?),\s*(.+)$/i', $linie, $m)) {
                                    $campuri['incarcare_cod_postal'] = $m[1];
                                    $campuri['incarcare_localitate'] = $m[2];
                                    $campuri['incarcare_data_ora']   = trim($m[3]);
                                }
                                if (preg_match('/^an:\s*(\S+)\s+(.+?),\s*(.+)$/i', $linie, $m)) {
                                    $campuri['descarcare_cod_postal']  = $m[1];
                                    $campuri['descarcare_localitate']  = $m[2];
                                    $campuri['descarcare_data_ora']    = trim($m[3]);
                                    $rest  = substr($text, strpos($text, $linie) + strlen($linie));
                                    $parts = preg_split(
                                        '/Direkter Link zum Angebot:/i',
                                        $rest
                                    );
                                    $campuri['detalii_cursa'] = trim($parts[0]);
                                    break;
                                }
                            }
                            break;

                        case 'couriernet':
                            if (preg_match(
                                '/Ladedaten:\s*(.*?)\s*Entladedaten:/is',
                                $email_text, $m
                            )) {
                                $block = trim($m[1]);
                                if (preg_match('/^([A-Z0-9-]+)\s+(.+)$/m', $block, $m2)) {
                                    $campuri['incarcare_cod_postal'] = $m2[1];
                                    $campuri['incarcare_localitate'] = trim($m2[2]);
                                }
                                if (preg_match('/von:\s*(.+?)\s+bis\s+(.+)/i', $block, $m3)) {
                                    $campuri['incarcare_data_ora'] =
                                        trim("{$m3[1]} bis {$m3[2]}");
                                }
                            }
                            if (preg_match(
                                '/Entladedaten:\s*(.*?)(?:\n{2,}|benötigtes|Bemerkung)/is',
                                $email_text, $m
                            )) {
                                $block = trim($m[1]);
                                if (preg_match('/^([A-Z0-9-]+)\s+(.+)$/m', $block, $m2)) {
                                    $campuri['descarcare_cod_postal'] = $m2[1];
                                    $campuri['descarcare_localitate'] = trim($m2[2]);
                                }
                                if (preg_match('/von:\s*(.+?)\s+bis\s+(.+)/i', $block, $m3)) {
                                    $campuri['descarcare_data_ora'] =
                                        trim("{$m3[1]} bis {$m3[2]}");
                                }
                            }
                            if (preg_match(
                                '/Entladedaten:.*?bis\s+.+?\n(.*?)(?=Bitte antworten)/is',
                                $email_text, $m
                            )) {
                                $campuri['detalii_cursa'] = trim($m[1]);
                            }
                            break;

                        case 'priorityfreight':
                            $dom  = new \DOMDocument();
                            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
                            $xp   = new \DOMXPath($dom);
                            $rows = $xp->query(
                                '//table[contains(@class,"course")]//tbody/tr'
                            );
                            foreach (['incarcare','descarcare'] as $i => $key) {
                                if (!$rows->item($i)) {
                                    continue;
                                }
                                $td       = $xp
                                    ->query('./td', $rows->item($i))
                                    ->item(0);
                                $interval = trim(
                                    $xp->evaluate('string(.//strong)', $td)
                                );
                                $full     = trim($td->textContent);
                                $after    = preg_replace('/^.*\]\s*/', '', $full);
                                [$cod, $loc] = preg_split('/\s+/', $after, 2)
                                            + [null, null];

                                $campuri["{$key}_data_ora"]      = $interval;
                                $campuri["{$key}_cod_postal"]    = $cod;
                                $campuri["{$key}_localitate"]    = $loc;
                            }
                            if (preg_match(
                                '~<table[^>]*class="[^"]*course[^"]*"[^>]*>'
                                . '.*?</table>(.*?)<div[^>]+class="button"~is',
                                $html,
                                $m
                            )) {
                                $tmp = preg_replace('/<br\s*\/?>/i', "\n", $m[1]);
                                $campuri['detalii_cursa'] =
                                    trim(html_entity_decode(strip_tags($tmp)));
                            }
                            break;
                    }

                    // ── Validare ────────────────────────────────────────────────────
                    $valid = true;
                    if ($parsingOptions['enableValidation']) {
                        $missing = 0;
                        foreach ($parsingOptions['mandatoryFields'] as $field) {
                            if (empty($campuri[$field])) {
                                $missing++;
                            }
                        }
                        if ($missing > $parsingOptions['maxMissingAllowed']) {
                            $valid = false;
                        }
                    }

                    // ── Salvare & ștergere email ────────────────────────────────────
                    if ($valid) {
                        OfertaCursa::updateOrCreate(
                            ['gmail_link' => $gmail_link],
                            array_merge([
                                'email_subiect'   => Str::limit($subject, 255),
                                'email_expeditor' => $email_expeditor,
                                'data_primirii'   => $data_primirii,
                                // 'email_text'      => $email_text,
                                'gmail_link'      => $gmail_link,
                            ], $campuri)
                        );
                        $totalSaved++;
                    }

                    // delete fără backslash
                    // $msg->setFlag('Deleted');
                } else {
                    // doar marchează ca citit
                    $msg->setFlag('Seen');
                }
            }

            // aplică DELETE
            // $inbox->expunge();
        }

        return response()->json([
            'status'  => 'ok',
            'checked' => $totalChecked,
            'saved'   => $totalSaved,
        ]);
    }

}
