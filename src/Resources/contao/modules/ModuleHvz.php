<?php

/**
 * Contao Open Source CMS
 *
 *
 * @package Hvz
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Chuckki\ContaoHvzBundle;

/**
 * Provide methods regarding HVZs.
 *
 * @author Dennis Esken
 */
class ModuleHvz extends \Frontend
{

	/**
	 * Add HVZs to the indexer
	 *
	 * @param array   $arrPages
	 * @param integer $intRoot
	 * @param boolean $blnIsSitemap
	 *
	 * @return array
	 */
	public function getSearchablePages($arrPages, $intRoot=0, $blnIsSitemap=false)
	{
		$arrRoot = array();

		if ($intRoot > 0)
		{
			$arrRoot = $this->Database->getChildRecords($intRoot, 'tl_page');
		}

		$arrProcessed = array();
		$time = \Date::floorToMinute();

		// Get all categories
		$objHvz = \HvzCategoryModel::findAll();

		// Walk through each category
		if ($objHvz !== null)
		{
			while ($objHvz->next())
			{
				// Skip HVZs without target page
				if (!$objHvz->jumpTo)
				{
					continue;
				}

				// Skip HVZs outside the root nodes
				if (!empty($arrRoot) && !in_array($objHvz->jumpTo, $arrRoot))
				{
					continue;
				}

				// Get the URL of the jumpTo page
				if (!isset($arrProcessed[$objHvz->jumpTo]))
				{
					$objParent = \PageModel::findWithDetails($objHvz->jumpTo);

					// The target page does not exist
					if ($objParent === null)
					{
						continue;
					}

					// The target page has not been published (see #5520)
					if (!$objParent->published || ($objParent->start != '' && $objParent->start > $time) || ($objParent->stop != '' && $objParent->stop <= ($time + 60)))
					{
						continue;
					}

					if ($blnIsSitemap)
					{
						// The target page is protected (see #8416)
						if ($objParent->protected)
						{
							continue;
						}

						// The target page is exempt from the sitemap (see #6418)
						if ($objParent->sitemap == 'map_never')
						{
							continue;
						}
					}

					// Generate the URL
					$arrProcessed[$objHvz->jumpTo] = $objParent->getAbsoluteUrl(\Config::get('useAutoItem') ? '/%s' : '/items/%s');

				}

				$strUrl = $arrProcessed[$objHvz->jumpTo];

				// Get the items
				$objItems = \HvzModel::findByPid($objHvz->id, array('order' => 'sorting'));

				if ($objItems !== null)
				{
					while ($objItems->next())
					{
						$arrPages[] = sprintf($strUrl, ($objItems->alias ?: $objItems->id));
					}
				}
			}
		}

		// add sites from BL and Kreis
		$objParent = \PageModel::findWithDetails(29);

		$bLand_alias = array(
			'baden-wuerttemberg',
			'bayern',
			'berlin',
			'brandenburg',
			'bremen',
			'hamburg',
			'hessen',
			'mecklenburg-vorpommern',
			'niedersachsen',
			'nordrhein-westfalen',
			'rheinland-Pfalz',
			'saarland',
			'sachsen',
			'sachsen-anhalt',
			'schleswig-holstein',
			'thueringen',
		);

		foreach ($bLand_alias as $site)
		{
			$arrPages[] =  $objParent->getAbsoluteUrl("/bundesland/" . $site);
		}

		$this->import('Database');

		$result_plz = $this->Database
			->prepare("SELECT kreis FROM tl_hvz where land like 'Deutschland' group by kreis order by kreis asc")
			->execute();

		while ($result_plz->next())
		{
			$stdKreis   = standardize($result_plz->kreis);
			$arrPages[] =  $objParent->getAbsoluteUrl("/kreis/" . $stdKreis);
		}

		return $arrPages;
	}

	public function mergeFamus()
	{
		// todo: mergeFamus Hvz´s
		$passw    = $GLOBALS['TL_CONFIG']['dbPass'];
		$host     = $GLOBALS['TL_CONFIG']['dbHost'];
		$user     = $GLOBALS['TL_CONFIG']['dbUser'];
		$database = $GLOBALS['TL_CONFIG']['dbDatabase'];

		$db = new \mysqli($host, $user, $passw, $database);
		if ($db->connect_errno > 0) {
			die('Unable to connect to database [' . $db->connect_error . ']');
		}
		$db->set_charset('utf8');

		$sql    = "select isFamus as myid , count(isFamus) as anzahl from tl_hvz group by isFamus order by isFamus";
		$result = $db->query($sql);

		$counter = 1;
		$log     = "";
		$allSql  = "";

		while ($group = $result->fetch_assoc()) {
			$sql    = "update tl_hvz set isFamus=" . $counter++ . " where isFamus=" . $group['myid'] . "; ";
			$allSql .= $sql;
			$log    .= $sql . "<br>";
		}

		$result2 = $db->multi_query($allSql);
		if ($result2) {
			$counter--;
		} else {
			$counter = "Error with merging";
		}
		$db->close();

		$myString = date('Ymd H:i') . "::merged::" . $counter . "\n";
		$file     = TL_ROOT . '/merge-log.txt';
		file_put_contents($file, $myString, FILE_APPEND);
	}

	public function saveFormData(&$arrSubmitted, $arrLabels, $objForm)
	{
		if (!empty($arrSubmitted['type']))
		{
			$this->import('Database');

			// todo: lookup vs xss-attacks
			if ($arrSubmitted['genehmigungVorhanden'] == 1)
			{
				$arrSubmitted['genehmigungVorhanden'] = 'ja';
			}else{
				$arrSubmitted['genehmigungVorhanden'] = 'nein';
			}

			$arrSubmitted['fullNetto'] = number_format(($arrSubmitted['EndPreis'] / 119 * 100), 2);

			if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$client_ip = $_SERVER['REMOTE_ADDR'];
			} else {
				$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			};

			$type = 0; // anfrage
			if ($arrSubmitted['Preis'] != 0) {
				$type = 1; // bestellung
			} else {
				$arrSubmitted['StrasseRechnung'] = '';
				$arrSubmitted['OrtRechnung']     = '';
				$arrSubmitted['Preis']           = 0;
				$arrSubmitted['hvzTagesPreis']   = 0;
				$arrSubmitted['agbakzeptiert']   = "";
				try {
					$returnValue         = preg_replace('/\\D/', '.', $arrSubmitted['vom'], -1);
					$curDate             = explode('.', $returnValue);
					$berechnungsTage     = intval($arrSubmitted['wievieleTage'], 10) - 1;
					$arrSubmitted['bis'] = date('d.m.Y', mktime(0, 0, 0, intval($curDate[1], 10), (intval($curDate[0], 10) + $berechnungsTage), intval($curDate[2], 10)));
				} catch (\Exception $e) {
					$arrSubmitted['bis'] = "-";
				};
			}

			$userId = 0;
			$this->import('FrontendUser', 'user');
			if (FE_USER_LOGGED_IN) {
				$userId = $this->user->id;
			}

			$genehmigungVorhanden = substr($arrSubmitted['genehmigungVorhanden'],0,1);

			$date = new \DateTime();
			$ts =  $date->format('Y-m-d H:i:s');

			$arrSubmitted['orderNumber'] = dechex(time());

			$formDatas = array();
			$formDatas['ort'] = $arrSubmitted['Ort'];
			$formDatas['auftragsNr'] = $arrSubmitted['orderNumber'];
			$formDatas['formAnrede'] = 'Sehr geehrte Frau '.$arrSubmitted['Name'];
			if($arrSubmitted['Geschlecht'] == 'Herr'){
				$formDatas['formAnrede'] = 'Sehr geehrter Herr '.$arrSubmitted['Name'];
			}

			if(empty($arrSubmitted['umstid'])){
			    $umstid = '';
            }else{
                $umstid = $arrSubmitted['umstid'];
            }

			$set           = array(
				'tstamp'            => time(),
				'user_id'           => $userId,
				'type'              => $type,
				'hvz_type'          => $arrSubmitted['type'],
				'hvz_type_name'     => $arrSubmitted['Genehmigung'],
				'hvz_preis'         => $arrSubmitted['Preis'],
				'hvz_ge_vorhanden'  => $genehmigungVorhanden,
				'hvz_ort'           => $arrSubmitted['Ort'],
				'hvz_plz'           => $arrSubmitted['PLZ'],
				'hvz_strasse_nr'    => $arrSubmitted['Strasse'],
				'hvzTagesPreis'		=> $arrSubmitted['hvzTagesPreis'],
				'hvz_vom'           => $arrSubmitted['vom'],
				'hvz_bis'           => $arrSubmitted['bis'],
				'hvz_vom_time'      => $arrSubmitted['vomUhrzeit'],
				'hvz_vom_bis'       => $arrSubmitted['bisUhrzeit'],
				'hvz_anzahl_tage'   => $arrSubmitted['wievieleTage'],
				'hvz_meter'         => $arrSubmitted['Meter'],
				'hvz_fahrzeugart'   => $arrSubmitted['Fahrzeug'],
				'hvz_zusatzinfos'   => $arrSubmitted['Zusatzinformationen'],
				'hvz_gutscheincode' => $arrSubmitted['gutscheincode'],
				'hvz_rabatt'        => $arrSubmitted['Rabatt'],
				'hvz_grund'         => $arrSubmitted['Grund'],
				're_anrede'         => $arrSubmitted['Geschlecht'],
				're_umstid'         => $umstid,
				're_firma'          => $arrSubmitted['firma'],
				're_name'           => $arrSubmitted['Name'],
				're_vorname'        => $arrSubmitted['Vorname'],
				're_strasse_nr'     => $arrSubmitted['strasse_rechnung'],
				're_ort_plz'        => $arrSubmitted['ort_rechnung'],
				're_email'          => $arrSubmitted['email'],
				're_telefon'        => $arrSubmitted['Telefon'],
				're_ip'             => $client_ip,
				're_agb_akzeptiert' => $arrSubmitted['agbakzeptiert'],
				'ts'				=> $ts,
				'orderNumber'		=> $arrSubmitted['orderNumber']
			);

			\System::getContainer()->get('session')->set('myform',$formDatas);

			$objInsertStmt = $this->Database->prepare("INSERT INTO tl_hvz_orders " . " %s")
				->set($set)->execute();
		}

	}


}
