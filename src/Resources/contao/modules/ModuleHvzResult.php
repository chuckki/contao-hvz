<?php
namespace Chuckki\ContaoHvzBundle;

class ModuleHvzResult extends \Module
{

    protected $bLand = array(
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
    );

    public function __construct()
    {
    }

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_hvzresult';
    protected $error = null;
    protected $zahlen = '';

    /**
     * @return string
     */
    public function generate()
    {
        return parent::generate();
    }

    protected function compile()
    {
        $this->import('FrontendUser', 'User');
        $this->Template->userGender = $this->User->gender;
        $this->import('Database');
        $request = trim($this->Input->get('suche'));
        $request = mb_strtolower($request, 'UTF-8');
        //$request = htmlspecialchars($request, ENT_QUOTES, 'UTF-8');

        $myResults = $this->searchMe($request);

        $this->logRequest($request, count($myResults), '');

        if (!empty($myResults)) {
            $myResults = array_unique($myResults, SORT_REGULAR);
            /***    Redirect to unique result  *****/
            if (sizeof($myResults) == 1) {
                $url = "halteverbot/".$myResults[0]['alias'].".html";
                $this->redirect($url, 301);
            }

            for ($i = 0; $i < count($myResults); $i++) {
                $myResults[$i]['bundesland'] = $this->bLand[$myResults[$i]['bundesland']];
            }
        }
        if ($_REQUEST['suche'] != "" and $_REQUEST['suche'] != null) {
            $this->Template->suche = $_REQUEST['suche'];
        }
        $this->Template->searchResult = $myResults;
        $this->Template->ergAnzahl = count($myResults);
        $this->Template->error = $this->error;
    }

    public function searchMe($request, $logging = true)
    {
        $searchPLZ = array();
        $cleanPLZ = null;
        $havePlz = false;
        $myResults = array();
        $this->import('Database');

        //	Prepare REQUEST
        if (!empty($request)) {
            // clean up request
            $request = strtolower(html_entity_decode($request));
            $request = trim($request);
            $danger = '/^([a-zA-Z0-9öäüßÖÄÜß,.() \n\r-]+)$/is';
            if (!preg_match($danger, $request)) {
                $this->error = "Bitte benutzen Sie keine Sonderzeichen bei Ihrer Eingabe.";
                if ($logging) {
                    $this->logRequest($request, -1, 'Sonderzeichen enthalten');
                }
                return null;
            }

            // replace shortcut
            $umlautev = array("st.", '(', ')');
            $umlaute = array("sankt", ' ', ' ');
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
            if (count($searchPLZ) > 1 AND $havePlz) {
                $mulitPLZ = "";
                foreach ($searchPLZ as $plz) {
                    $mulitPLZ .= $plz." vs ";
                }
                $mulitPLZ = substr($mulitPLZ, 0, -4);
                if ($logging) {
                    $this->logRequest($request, -1, 'Doppelte PLZ gefunden');
                }
                $this->error = "Das System konnte keine eindeutige Postleitzahl bestimmen (".$mulitPLZ.")";
                die($this->error);
            }
        }

        // get Result for PLZ
        if ($havePlz) {
            $result_plz = $this->Database
                ->prepare(
                    "SELECT alias,question,bundesland,kreis,hvz_single_og,isFamus  FROM tl_hvz as a inner join tl_plz as b on a.id = b.ortid where b.plzS=? group by alias order by isFamus desc, question asc LIMIT 0, 15"
                )
                ->execute($cleanPLZ);
            $tmpArray = array();
            while ($result_plz->next()) {
                $tmpArray[] = $result_plz->row();
            }
            $myResults = array_unique($tmpArray, SORT_REGULAR);
        }

        // get single Result for PLZ
        if (count($myResults) == 1) {
            return $myResults;
        }

        // build new Results based on PLZ with string parts
        if (count($myResults) > 1) {

            // split request
            $splitAnfrage = explode($searchPLZ[0], $request);

            // clean up splits
            $tmp = array();
            foreach ($splitAnfrage as $part) {
                $part = trim($part);
                if (empty($part)) {
                    continue;
                }
                $umlautev = array(" ");
                $umlaute = array('%');
                $part = str_replace($umlautev, $umlaute, $part);
                $tmp[] = $part;
            }
            $splitAnfrage = $tmp;

            $tmpArray = array();

            foreach ($splitAnfrage as $anfrage) {
                $tmpArray = array_merge($this->lookupPLZandString($cleanPLZ, $anfrage.'%'), $tmpArray);
            }
            if (!empty($tmpArray)) {
                return $tmpArray;
            }

            return $myResults;

        }

        // KEINE PLZ VORHANDEN
        // ORT ONLY but EXACT
        $request = preg_replace("/[0-9]|\.|,|-|\(|\)/", " ", $request);
        $res = explode(' ', $request);
        $cleanParts = array();
        foreach ($res as $part) {
            $part = trim($part);
            if (!empty($part)) {
                $cleanParts[] = $part;
            }
        }
        $res = $cleanParts;

        // 2 oder mehr strings *****/
        if (count($res) > 1) {
            $tmpArray = array();
            $srequest = implode('%', $cleanParts);
            foreach ($this->getAlternate($srequest) as $anfrage) {
                $tmpArray = array_merge($this->lookupPLZandString('', $anfrage.'%'), $tmpArray);
            }
            $myResults = array_unique($tmpArray, SORT_REGULAR);
            if (count($myResults) == 1) {
                return $myResults;
            }

        }

        // ORT ONLY but EXACT
        $requestFirst = $res[0];
        $tmpArray = array();
        // first check original request with replacements for results
        foreach ($this->getAlternate($requestFirst) as $anfrage) {
            $tmpArray = array_merge($this->lookupPLZandString('', $anfrage), $tmpArray);
        }

        $myResults = array_merge($myResults, $tmpArray);
        $myResults = array_unique($myResults, SORT_REGULAR);

        if (count($myResults) == 0) {
            foreach ($this->getAlternate($requestFirst) as $anfrage) {
                $tmpArray = array_merge($this->lookupPLZandString('', $anfrage.'%'), $tmpArray);
            }
            $myResults = array_merge($myResults, $tmpArray);
            $myResults = array_unique($myResults, SORT_REGULAR);
        }
        if (count($myResults) != 0) {
            return $myResults;
        }

        // replace whitespaces with %
        if (count($myResults) == 0)
        {
            $tmpArray = array();
            $danger = '/^([a-zA-ZöäüßÖÄÜß,.() \n\r-]+)$/is';
            if (preg_match($danger, $request)) {
                $such = array('-', ',', '.');
                $request = str_replace($such, '%', $request);
                $myparts = explode(' ', $request);
                foreach ($myparts as $part) {
                    foreach ($this->getAlternate($part) as $anfrage) {
                        $tmpArray = array_merge($this->lookupPLZandString('', $anfrage), $tmpArray);
                    }
                }
                $myResults = array_unique($tmpArray, SORT_REGULAR);
                if (count($myResults) == 0) {
                    foreach ($myparts as $part) {
                        foreach ($this->getAlternate($part) as $anfrage) {
                            $tmpArray = array_merge($this->lookupPLZandString('', $anfrage.'%'), $tmpArray);
                        }

                        $myResults = array_unique($tmpArray, SORT_REGULAR);
                    }
                }
            } else {
                $this->zahlen .= $request."<br>";
            }
		}

        // split request and try alone
        // comibine all results
        for ($i = 0; $i < sizeof($myResults); $i++) {
            $myResults[$i]['bundesland'] = $this->bLand[$myResults[$i]['bundesland']];
        }

        return $myResults;
    }

	// sqp with plz and string
    private function lookupPLZandString($plz, $string)
    {
        $anfrage = strtolower($string);
        if (empty($plz))
        {
            $result_plz = $this->Database
                ->prepare("SELECT alias,question,bundesland,kreis,hvz_single_og,isFamus FROM tl_hvz where LOWER(question) like ? group by alias order by isFamus desc, question asc LIMIT 0, 15")
                ->execute($anfrage);
        } else {
            $result_plz = $this->Database
                ->prepare("SELECT * from (SELECT alias,question,bundesland,kreis,hvz_single_og,isFamus  FROM tl_hvz as a inner join tl_plz as b on a.id = b.ortid where b.plzS=? group by alias order by isFamus desc, question asc LIMIT 0, 15) as a where LOWER(question) like ?")
                ->execute($plz, $anfrage);
        }

        $tmpArray = array();
        while ($result_plz->next()) {
            $tmpArray[] = $result_plz->row();
        }
        return $tmpArray;
    }

     // Log Request
    protected function logRequest($request, $results, $msg = '')
    {
        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        };

        $thisAgent = $_SERVER['HTTP_USER_AGENT'];

        $userId = 0;
        $this->import('FrontendUser', 'User');
        if (FE_USER_LOGGED_IN) {
            $userId = $this->User->id;
        }

        $isbot = (strpos(strtolower($thisAgent), 'bot') !== false) ? 1 : 0;

        if (!empty($_GET["suche"])) {
            $set = array(
                'tstamp' => time(),
                're_ip' => $client_ip,
                'hits' => $results,
                'user_id' => $userId,
                'agent' => $thisAgent,
                'isbot' => $isbot,
                'msg' => $msg,
                'anfrage' => $_GET["suche"],
				'ts' => date("Y-m-d H:i:s")
            );
            $objInsertStmt = $this->Database->prepare("INSERT INTO tl_hvz_request "." %s")
                ->set($set)
                ->execute();
        }
    }

     // Alternativen für Request
    private function getAlternate($request)
    {
        $retArray = array();
        $mainRes = $request;
        $retArray[] = $mainRes;
        $umlaute = array("ü", "ö", "ä");
        $umlautev = array("ue", "oe", "ae");
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
