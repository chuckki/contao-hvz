<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

use Contao\Input;

class ModuleHvzResult extends \Module
{
    protected $bLand = [
        '',
        'Baden-Württemberg',
        'Bayern',
        'Berlin',
        'Brandenburg',
        'Bremen',
        'Hamburg',
        'Hessen',
        'Mecklenburg-Vorpommern',
        'Niedersachsen',
        'Nordrhein-Westfalen',
        'Rheinland-Pfalz',
        'Saarland',
        'Sachsen',
        'Sachsen-Anhalt',
        'Schleswig-Holstein',
        'Thüringen',
    ];

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_hvzresult';
    protected $error;
    protected $zahlen = '';

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function generate()
    {
        return parent::generate();
    }

    public function searchMe($request, $country='gr')
    {

        $searchPLZ = [];
        $cleanPLZ = null;
        $havePlz = false;
        $myResults = [];
        $this->import('Database');

        //	Prepare REQUEST
        if (!empty($request)) {
            // clean up request
            $request = strtolower(html_entity_decode($request));
            $request = trim($request);
            $danger = '/^([\p{L}a-zA-Z0-9öäüßÖÄÜß,.() \n\r-]+)$/is';
            if (!preg_match($danger, $request)) {
                $this->error = 'Bitte benutzen Sie keine Sonderzeichen bei Ihrer Eingabe.';
                if ($logging) {
                    $this->logRequest($request, -1, 'Sonderzeichen enthalten');
                }

                return null;
            }

            // replace shortcut
            $umlautev = ['st.', '(', ')'];
            $umlaute = ['sankt', ' ', ' '];
            $request = str_replace($umlautev, $umlaute, $request);

            // get possible PLZ
            $musterVollPLZ = '/[1-9]{1}[0-9]{4}|[0]{1}[1-9]{4}|[1-9]{1}[0-9]{3}/';
            $havePlz = preg_match($musterVollPLZ, $request, $searchPLZ);

            // clean up PLZ
            if ($havePlz) {
                $suchmuster_plz = '/^([0])/';
                if (preg_match($suchmuster_plz, $searchPLZ[0])) {
                    $cleanPLZ = substr($searchPLZ[0], 1);
                } else {
                    $cleanPLZ = $searchPLZ[0];
                }
            }

            // multi PLZ -> die()
            if (\count($searchPLZ) > 1 and $havePlz) {
                $mulitPLZ = '';
                foreach ($searchPLZ as $plz) {
                    $mulitPLZ .= $plz.' vs ';
                }
                $mulitPLZ = substr($mulitPLZ, 0, -4);
                if ($logging) {
                    $this->logRequest($request, -1, 'Doppelte PLZ gefunden');
                }
                $this->error = 'Das System konnte keine eindeutige Postleitzahl bestimmen ('.$mulitPLZ.')';
                die($this->error);
            }
        }

        // get Result for PLZ
        if ($havePlz) {
            $result_plz = $this->Database
                ->prepare(
                    'SELECT alias,question,bundesland,kreis,hvz_single_og,isFamus  FROM tl_hvz as a inner join tl_plz as b on a.id = b.ortid where b.plzS=? AND a.lk=? group by alias order by isFamus desc, question asc LIMIT 0, 15'
                )
                ->execute($cleanPLZ, $country);
            $tmpArray = [];
            while ($result_plz->next()) {
                $tmpArray[] = $result_plz->row();
            }
            $myResults = array_unique($tmpArray, SORT_REGULAR);
        }

        // get single Result for PLZ
        if (1 === \count($myResults)) {
            return $myResults;
        }

        // build new Results based on PLZ with string parts
        if (\count($myResults) > 1) {
            // split request
            $splitAnfrage = explode($searchPLZ[0], $request);

            // clean up splits
            $tmp = [];
            foreach ($splitAnfrage as $part) {
                $part = trim($part);
                if (empty($part)) {
                    continue;
                }
                $umlautev = [' '];
                $umlaute = ['%'];
                $part = str_replace($umlautev, $umlaute, $part);
                $tmp[] = $part;
            }
            $splitAnfrage = $tmp;

            $tmpArray = [];

            foreach ($splitAnfrage as $anfrage) {
                $tmpArray = array_merge($this->lookupPLZandString($cleanPLZ, $anfrage.'%', $country), $tmpArray);
            }
            if (!empty($tmpArray)) {
                return $tmpArray;
            }

            return $myResults;
        }

        // KEINE PLZ VORHANDEN
        // ORT ONLY but EXACT
        $request = preg_replace("/[0-9]|\.|,|-|\(|\)/", ' ', $request);
        $res = explode(' ', $request);
        $cleanParts = [];
        foreach ($res as $part) {
            $part = trim($part);
            if (!empty($part)) {
                $cleanParts[] = $part;
            }
        }
        $res = $cleanParts;

        // 2 oder mehr strings *****/
        if (\count($res) > 1) {
            $tmpArray = [];
            $srequest = implode('%', $cleanParts);
            foreach ($this->getAlternate($srequest) as $anfrage) {
                $tmpArray = array_merge($this->lookupPLZandString('', $anfrage.'%', $country), $tmpArray);
            }
            $myResults = array_unique($tmpArray, SORT_REGULAR);
            if (1 === \count($myResults)) {
                return $myResults;
            }
        }

        // ORT ONLY but EXACT
        $requestFirst = $res[0];
        $tmpArray = [];
        // first check original request with replacements for results
        foreach ($this->getAlternate($requestFirst) as $anfrage) {
            $tmpArray = array_merge($this->lookupPLZandString('', $anfrage, $country), $tmpArray);
        }

        $myResults = array_merge($myResults, $tmpArray);
        $myResults = array_unique($myResults, SORT_REGULAR);

        if (0 === \count($myResults)) {
            foreach ($this->getAlternate($requestFirst) as $anfrage) {
                $tmpArray = array_merge($this->lookupPLZandString('', $anfrage.'%', $country), $tmpArray);
            }
            $myResults = array_merge($myResults, $tmpArray);
            $myResults = array_unique($myResults, SORT_REGULAR);
        }
        if (0 !== \count($myResults)) {
            return $myResults;
        }

        // replace whitespaces with %
        if (0 === \count($myResults)) {
            $tmpArray = [];
            $danger = '/^([a-zA-ZöäüßÖÄÜß,.() \n\r-]+)$/is';
            if (preg_match($danger, $request)) {
                $such = ['-', ',', '.'];
                $request = str_replace($such, '%', $request);
                $myparts = explode(' ', $request);
                foreach ($myparts as $part) {
                    foreach ($this->getAlternate($part) as $anfrage) {
                        $tmpArray = array_merge($this->lookupPLZandString('', $anfrage, $country), $tmpArray);
                    }
                }
                $myResults = array_unique($tmpArray, SORT_REGULAR);
                if (0 === \count($myResults)) {
                    foreach ($myparts as $part) {
                        foreach ($this->getAlternate($part) as $anfrage) {
                            $tmpArray = array_merge($this->lookupPLZandString('', $anfrage.'%', $country), $tmpArray);
                        }

                        $myResults = array_unique($tmpArray, SORT_REGULAR);
                    }
                }
            } else {
                $this->zahlen .= $request.'<br>';
            }
        }

        // split request and try alone
        // comibine all results
        for ($i = 0; $i < \count($myResults); ++$i) {
            $myResults[$i]['bundesland'] = $this->bLand[$myResults[$i]['bundesland']];
        }

        return $myResults;
    }

    protected function compile()
    {
        $this->import('FrontendUser', 'User');
        $this->Template->userGender = $this->User->gender;
        $this->import('Database');
        $request = trim($this->Input->get('suche'));
        $lkz = trim($this->Input->get('c'));
        $request = mb_strtolower($request, 'UTF-8');
        //$request = htmlspecialchars($request, ENT_QUOTES, 'UTF-8');

        if(empty($lkz)){
            $lkz =  trim($this->Input->get('lkz'));
        }

        $myResults = $this->searchMe($request, $lkz);

        $this->logRequest($request, ($myResults) ? \count($myResults) : 0, '');


        $myResults = $this->searchMe($request, $lkz);
        $hits = $myResults ?  \count($myResults) :0;
        $this->logRequest($request, $hits, '');

        if (!empty($myResults)) {
            $myResults = array_unique($myResults, SORT_REGULAR);
            /***    Redirect to unique result  *****/
            if (1 === \count($myResults)) {
                $url = 'halteverbot/'.$lkz.'/'.$myResults[0]['alias'].'.html';
                $this->redirect($url, 301);
            }

            for ($i = 0; $i < \count($myResults); ++$i) {
                $myResults[$i]['bundesland'] = $this->bLand[$myResults[$i]['bundesland']];
            }
        }

        $this->Template->suche = $request;
        $this->Template->searchResult = $myResults;
        $this->Template->ergAnzahl = $hits;
        $this->Template->error = $this->error;
    }

    // Log Request
    protected function logRequest($request, $results, $msg = '')
    {
        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        $thisAgent = $_SERVER['HTTP_USER_AGENT'];

        $userId = 0;
        $this->import('FrontendUser', 'User');
        if (FE_USER_LOGGED_IN) {
            $userId = $this->User->id;
        }

        $isbot = (false !== strpos(strtolower($thisAgent), 'bot')) ? 1 : 0;

        if (!empty($_GET['suche'])) {
            $set = [
                'tstamp' => time(),
                're_ip' => $client_ip,
                'hits' => $results,
                'user_id' => $userId,
                'agent' => $thisAgent,
                'isbot' => $isbot,
                'msg' => $msg,
                'anfrage' => $_GET['suche'],
                'ts' => date('Y-m-d H:i:s'),
            ];
            $objInsertStmt = $this->Database->prepare('INSERT INTO tl_hvz_request '.' %s')
                ->set($set)
                ->execute();
        }
    }

    // sqp with plz and string
    private function lookupPLZandString($plz, $string, $lkz = 'gr')
    {

        if($lkz === 'gr'){
            die("walter");
        }

        $anfrage = strtolower($string);
        if (empty($plz)) {
            $result_plz = $this->Database
                ->prepare('SELECT alias,question,bundesland,kreis,hvz_single_og,isFamus FROM tl_hvz where LOWER(question) like ? AND lk=? group by alias order by isFamus desc, question asc LIMIT 0, 15')
                ->execute($anfrage, $lkz);
        } else {
            $result_plz = $this->Database
                ->prepare('SELECT * from (SELECT alias,question,bundesland,kreis,hvz_single_og,isFamus  FROM tl_hvz as a inner join tl_plz as b on a.id = b.ortid where b.plzS=? AND a.lk=? group by alias order by isFamus desc, question asc LIMIT 0, 15) as a where LOWER(question) like ?')
                ->execute($plz, $anfrage, $lkz);
        }

        $tmpArray = [];
        while ($result_plz->next()) {
            $tmpArray[] = $result_plz->row();
        }

        return $tmpArray;
    }

    // Alternativen für Request
    private function getAlternate($request)
    {
        $retArray = [];
        $mainRes = $request;
        $retArray[] = $mainRes;
        $umlaute = ['ü', 'ö', 'ä'];
        $umlautev = ['ue', 'oe', 'ae'];
        $alt0 = str_replace($umlautev, $umlaute, $mainRes);
        $alt1 = str_replace($umlaute, $umlautev, $mainRes);
        $retArray[] = $alt0;
        $retArray[] = $alt1;

        $retArray[] = str_replace('ss', 'ß', $alt0);
        $retArray[] = str_replace('ß', 'ss', $alt0);

        $retArray[] = str_replace('ss', 'ß', $alt1);
        $retArray[] = str_replace('ß', 'ss', $alt1);

        $retArray[] = str_replace('ss', 'ß', $mainRes);
        $retArray[] = str_replace('ß', 'ss', $mainRes);
        $retArray[] = $mainRes.'%(%)';

        return $retArray;
    }
}
