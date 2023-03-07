<?php

namespace App\Traits;
// use App\Student;

trait TrimiteSmsTrait {
    public function trimiteSms($categorie = null, $subcategorie = null, $referinta_id = null, $telefoane = null, $mesaj = null)
    {
        // foreach ($telefoane as $telefon) {
        //     echo $telefon . '<br>';
        // }
        // dd($categorie, $subcategorie, $referinta_id, $telefoane, $mesaj);

        // Referitor la diacritice, puteti face conversia unui string cu diacritice intr-unul fara diacritice, in mod automatizat cu aceasta functie PHP:
        $transliterator = \Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', \Transliterator::FORWARD);
        // $textFaraDiacritice = $transliterator->transliterate($textCuDiacritice);
        $mesaj = $transliterator->transliterate($mesaj);

        // Setarea trimiterii live sau testarea sms-ului
        $test = 1; // sms-ul nu se trimite
        // $test = 0; // sms-ul se trimite

        foreach ($telefoane as $telefon) {

            // ----------------------------------------------------------------------------
            //
            //    Exemplu minimal pentru trimiterea de SMS-uri (PHP)
            //    Serviciul SMS Gateway
            //    Versiunea 1.1 / 12.04.2010
            //    Distribuit gratuit
            //
            // ----------------------------------------------------------------------------

            // ----------------------------------------------------------------------------
            //  Pasul 1
            //  Interogam SMS Gateway si salvam rezultatul trimis de acesta in variabila
            //  pentru a putea interpreta statutul trimiterii
            //   - Pentru HTTPS utilizati https://secure.smslink.ro
            // ----------------------------------------------------------------------------
            $content = file_get_contents("http://www.smslink.ro/sms/gateway/communicate/?" .
                "connection_id=" . config('sms_link.connection_id') . "&password=" . config('sms_link.password') .
                "&to=" . urlencode($telefon) .
                "&message=" . urlencode($mesaj) .
                '&test=' . $test);
            // dd($content);
            // ----------------------------------------------------------------------------
            //  Pasul 2
            //  Interpretam rezultatul pentru a avea acces la tot continutul acestuia si
            //  a putea intelege rezultatul mesajului trimis spre SMS Gateway
            //
            //  Rezultatul transmis de SMS Gateway va fi intotdeauna de forma urmatoare:
            //  string Nivel;int ID Rezultat;string Mesaj;string[optional] Variabile
            // ----------------------------------------------------------------------------
            //  Pasul 2.1
            //  Extragem din rezultat toate variabilele separate prin punct si virgula
            // ----------------------------------------------------------------------------
            list($level, $id, $response, $variabiles) = explode(";", $content . ';');

            // ----------------------------------------------------------------------------
            //  Pasul 2.2
            //  Verificam daca mesajul trimis a fost transmis cu succes prin compararea
            //  Nivelului si ID Rezultat
            // ----------------------------------------------------------------------------
            //  Daca mesajul este transmis atunci Nivelul va fi MESSAGE si ID- rezultat
            //  va avea valoarea numerica 1
            // ----------------------------------------------------------------------------


            $smsTrimis = new \App\Models\MesajTrimisSms;
            $smsTrimis->categorie = $categorie;
            $smsTrimis->subcategorie = $subcategorie;
            $smsTrimis->referinta_id = $referinta_id;
            $smsTrimis->telefon = $telefon;
            $smsTrimis->mesaj = $mesaj;
            $smsTrimis->content = $content;


            if (($level == "MESSAGE") and ($id == 1)) {
                // ------------------------------------------------------------------------
                //  Variabilele optionale transmise optional sunt separate prin virgula
                //  si vor avea forma urmatoare:
                //  mixed Variabila 1,mixed Variabila 2 ... mixed Variabila 3
                // ------------------------------------------------------------------------
                $variabiles = explode(",", $variabiles);

                // ------------------------------------------------------------------------
                //  Extragem ID-ul Mesajului alocat de gateway pentru a il salva pentru
                //  utilizare ulterioara. Message ID  va fi intotdeauna prima variabila
                //  trimisa, restul fiind explicate complet in documentatia de pe site.
                // ------------------------------------------------------------------------
                $message_id = $variabiles[0];

                // ------------------------------------------------------------------------
                //  Pasul 3
                //  Afisam mesajul de confirmare si afisam Message ID-ul alocat
                // ------------------------------------------------------------------------
                // echo "Mesajul a fost trimis si are ID-ul " . $message_id . "!";


                $smsTrimis->trimis = 1;
                $smsTrimis->mesaj_id = $message_id;
                $smsTrimis->raspuns = $response;


            } else {
                if ($level == "ERROR") {
                    // --------------------------------------------------------------------
                    //  Pasul 3
                    //  Afisam mesajul de eroare si afisam ID-ul erorii si descrierea
                    // --------------------------------------------------------------------
                    // echo "A intervenit eroarea ID " . $id . ", Descriere " . $response;


                    $smsTrimis->trimis = 0;
                    $smsTrimis->eroare_id = $id;
                    $smsTrimis->raspuns = $response;
                }
            }
            $smsTrimis->save();
        }
    }
}
