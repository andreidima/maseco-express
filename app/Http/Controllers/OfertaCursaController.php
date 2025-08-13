<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OfertaCursa;
use App\Http\Requests\OfertaCursaRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Webklex\IMAP\Facades\Client;

class OfertaCursaController extends Controller
{
    public function index(Request $request)
    {
        // Reset any stored return URL:
        $request->session()->forget('returnUrl');

        // Pull in all filter values (trimmed)
        $searchIncarcareCodPostal    = trim($request->searchIncarcareCodPostal);
        $searchIncarcareLocalitate   = trim($request->searchIncarcareLocalitate);
        $searchIncarcareDataOra      = trim($request->searchIncarcareDataOra);
        $searchDescarcareCodPostal   = trim($request->searchDescarcareCodPostal);
        $searchDescarcareLocalitate  = trim($request->searchDescarcareLocalitate);
        $searchDescarcareDataOra     = trim($request->searchDescarcareDataOra);

        // Helper: if input is digits only, compare against the FIRST run of digits using REGEXP_SUBSTR
        $applyPostalFilter = function ($q, $column, $value) {
            if ($value === '') return;
            if (preg_match('/^\d+$/', $value)) {
                // First run of digits starts with $value
                $q->whereRaw("REGEXP_SUBSTR($column, '[0-9]+') LIKE ?", [$value.'%']);
            } else {
                // Fallback: regular contains search
                $q->where($column, 'LIKE', "%{$value}%");
            }
        };

        // Build query with conditional clauses
        $oferte = OfertaCursa::query()

            // Postal-code logic (first-number prefix if digits-only)
            ->when($searchIncarcareCodPostal, fn($q, $v) => $applyPostalFilter($q, 'incarcare_cod_postal', $v))
            ->when($searchDescarcareCodPostal, fn($q, $v) => $applyPostalFilter($q, 'descarcare_cod_postal', $v))

            // ->when($searchIncarcareCodPostal, fn($q, $v) =>
            //     $q->where('incarcare_cod_postal', 'LIKE', "%{$v}%")
            // )
            ->when($searchIncarcareLocalitate, fn($q, $v) =>
                $q->where('incarcare_localitate', 'LIKE', "%{$v}%")
            )
            ->when($searchIncarcareDataOra, fn($q, $v) =>
                $q->where('incarcare_data_ora', 'LIKE', "%{$v}%")
            )
            // ->when($searchDescarcareCodPostal, fn($q, $v) =>
            //     $q->where('descarcare_cod_postal', 'LIKE', "%{$v}%")
            // )
            ->when($searchDescarcareLocalitate, fn($q, $v) =>
                $q->where('descarcare_localitate', 'LIKE', "%{$v}%")
            )
            ->when($searchDescarcareDataOra, fn($q, $v) =>
                $q->where('descarcare_data_ora', 'LIKE', "%{$v}%")
            )
            ->latest('data_primirii')
            ->simplePaginate(25);

        // Pass all search terms back to the view
        return view('oferte_curse.index', [
            'oferte'                      => $oferte,
            'searchIncarcareCodPostal'    => $searchIncarcareCodPostal,
            'searchIncarcareLocalitate'   => $searchIncarcareLocalitate,
            'searchIncarcareDataOra'      => $searchIncarcareDataOra,
            'searchDescarcareCodPostal'   => $searchDescarcareCodPostal,
            'searchDescarcareLocalitate'  => $searchDescarcareLocalitate,
            'searchDescarcareDataOra'     => $searchDescarcareDataOra,
        ]);
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
