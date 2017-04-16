<?php

/**
 * Contao Open Source CMS
 *
 * @package Hvz
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


namespace Contao;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Patchwork\Utf8;


/**
 * Class ModuleHvzList
 *
 * @property array    $hvz_categories
 *
 * @author Dennis Esken
 */
class ModuleHvzList extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_hvzlist';

    /**
     * Target pages
     * @var array
     */
    protected $arrTargets = array();


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
			/** @var BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['hvzlist'][0]) . ' ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }


        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && $GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item']))
        {
            \Input::setGet('items', \Input::get('auto_item'));
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {

        $bLand = array(
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

        $pageTitle = "Halteverbote und Absperrungen deutschlandweit";
        $pageDisc = "Halteverbote deutschlandweit in allen Bundesländern verfügbar. Wir stellen für Sie Ihre Schilder auf und schaffen Ihnen Platz.";
        $headline = 'Halteverbote und Absperrungen deutschlandweit';
        $teaser = '<p>Nachfolgend finden Sie alle Bundesländer in denen wir tätig sind. Falls Ihre Stadt in der unten aufgeführten Liste nicht enthalten ist, schicken Sie uns eine <a href="halteverbot-anfrage.html" title="Halteverbot Anfrage">Anfrage</a>. Nach dem Erhalt Ihrer Anfrage, stellen wir Ihnen umgehend ein Angebot zusammen. Abhängig von der Gemeinde entstehen unterschiedliche Kosten bezgüglich der Genehmigung, so dass wir vorab keine pauschalen Preisangaben geben können.</p>';

        $teaser = '<p>Halteverbote für Ihren Umzug, Ihre Anlieferung oder Baustelle können wir in allen Bundesländern für Sie fachgerecht einrichten. Natürlich kümmern wir uns in den einzelnen Bundesländern und dessen Städten auch um die behördlichen Genehmigungen. Untenstehenden erhalten Sie eine Zusammenfassung aller Kreise und dessen Städten in welchen wir Ihr Halteverbot deutschlandweit einrichten können.</p><p>Sollte Sie ein Halteverbot in einer Stadt benötigen, welche sich nicht in unsere Auflistung befindet, so senden Sie uns einfach eine <a href="halteverbot-anfrage.html" title="Halteverbotsanfrage">Halteverbotsanfrage</a>. Sie erhalten dann zeitnah von uns ein entsprechendes Angebot.</p>';


        $this->import('Database');

        $bundesland = Input::get('bundesland');
        $kreis = Input::get('kreis');

        $myReturn = array();

        // no request => Bundesland
        if (empty($bundesland) and empty($kreis))
        {
            $myReturn['title'] = "Haltevebote in allen Bundesländern bestellen";
            $myReturn['items'] = array();

            // Add HVZs
            foreach ($bLand as $key => $value)
            {
                $arrTemp = array();
                $arrTemp['title'] = StringUtil::specialchars($value, true);
                $arrTemp['href'] = $this->generateHvzLink('bundesland/'.$bLand_alias[$key]);
                $myReturn['items'][] = $arrTemp;
            }
        }

        if (!empty($bundesland))
        {

            $blParm = array_search($bundesland, $bLand_alias);
            $myReturn['title'] = $bLand[$blParm];

            $blParm += 1;
            $result_plz = $this->Database
                ->prepare("SELECT kreis FROM tl_hvz where bundesland = ? group by kreis order by kreis asc")
                ->execute($blParm);

            $tmpArray = array();
            while ($result_plz->next())
			{
                $tmpArray[] = $result_plz->kreis;
                $arrTemp = array();
                $arrTemp['title'] = StringUtil::specialchars($result_plz->kreis, true);
                $stdKreis = StringUtil::standardize($result_plz->kreis);
                $arrTemp['href'] = $this->generateHvzLink('kreis/'.$stdKreis);
                $myReturn['items'][] = $arrTemp;
            }
            $blParm -= 1;
            $pageTitle = "Halteverbot in ".$bLand[$blParm]." bestellen";
            $pageDisc = "Halteverbot in ".$bLand[$blParm]." bestellen. Wir stellen für Sie Ihre Schilder in ".$bLand[$blParm]." auf und schaffen Ihnen Platz.";
            $headline = "Halteverbot in ".$bLand[$blParm]." bestellen";
            $teaser = '<p>Nachfolgend finden Sie alle Kreise in '.$bLand[$blParm].', in denen wir Halteverbotzonen anbieten. Falls Ihr Kreis in der unten aufgeführten Liste nicht enthalten ist, schicken Sie uns eine <a href="halteverbot-anfrage.html" title="Halteverbot Anfrage">Anfrage</a>. Nach dem Erhalt Ihrer Anfrage, stellen wir Ihnen umgehend ein Angebot zusammen.</p>';

            switch ($blParm)
			{
                case 0:
                    $teaser = '<p>Nachfolgend finden Sie eine Auflistung aller Kreise in Baden-Württemberg. Halteverbote in den zugehörigen Städten können Sie bei uns schnell und unkompliziert bestellen. Die Kosten bei einem Halteverbot variieren nicht nur von Bundesland zu Bundesland, sondern auch von Stadt zu Stadt. Dies liegt daran, dass die Städte in Baden-Württemberg unterschiedliche Gebühren erheben. Eine genaue Preisübersicht entnehmen Sie bitte unserer Halteverbot-Preisliste.  Bedingt durch die Bevölkerung in diesem Bundeland von ca. 10 Millionen Menschen ist das Verkehrsaufkommen hier hoch und damit die Parkplatzsituation begrenzt. Um ausreichend Platz zur Verfügung zu stellen ist es unabdingbar zu buchen!</p>';
                    break;
                case 1:
                    $teaser = '<p>Untenstehend finden Sie eine Zusammenfassung aller Kreise im Bundeland Bayern. Halteverbote in allen Kreisen und Städten in Bayern sind für uns kein Problem. Egal ob Sie ein Halteverbot in München, Nürnberg oder Aschaffenburg benötigen. Wir helfen Ihnen in ganz Bayern bei Ihren Absperrungen. Bayern im Südosten von Deutschland hat bedingt durch seine knapp 13 Millionen Einwohner und ca. 1.300 zugelassenen Fahrzeugen geraden in den Ballungszentren ein Platzproblem. Um Ihren Platz sicherzustellen, sollten Sie in Bayer in jedem fall ein Halteverbot buchen.</p>';
                    break;
                case 2:
                    $teaser = '<p>Ein Halteverbot in Berlin ist unabdingbar. Gerade in Berlin ist die Platzsituation sehr angespannt. Parkplätze sind Mangelware und auch das Halten in zweiter Reihe wird nicht gerne gesehen und sorgt für Chaos. Eine suche nach einem Parkplatz vor Ihrer Wunschadresse für einen Umzug oder zum Beispiel ein Event ist Glückspiel! Stellen Sie Ihren Patz in dem gewünschten Zeitraum in Berlin durch ein Halteverbot sicher und bestellen Sie unkompliziert ein Halteverbot. Alle Stadtteile von Berlin können durch uns bedient werden.</p>';
                    break;
                case 3:
                    $teaser = '<p>Hier erhalten Sie einen Überblick für Halteverbote in allen Kreisen im Bundesland Brandenburg. Das Bundesland Brandenburg besteht aus 18 Kreisen. Ein Halteverbot in allen zugehörigen Städten kann durch das Team von Halteverbot-beantragen kompetent und terminsicher gewährleistet werden. Egal ob in der Landeshauptstadt Potsdam oder Cottbus, machen Sie die Sicherstellung einer Haltezone nicht zum Glückspiel. Stellen Sie schon vor Ihrem Umzug oder Ihrer Anlieferung ausreichend Platz durch ein Halteverbot sicher. So können andere Anwohner sich an dieses für Sie eingereichtes Halteverbot halten und Ihren Arbeiten steht nichts mehr im Weg.</p>';
                    break;
            }

        }

        // request = bl => Kreise
        if (!empty($kreis))
        {
            $currentKreis = '';
            $firstLetterKreis = substr($kreis, 0, 1).'%';

            $result_plz = $this->Database
                ->prepare("SELECT kreis FROM tl_hvz where lower(kreis) like ? group by kreis order by kreis asc")
                ->execute($firstLetterKreis);

            while ($result_plz->next())
			{
                if (StringUtil::standardize($result_plz->kreis) == $kreis)
                {
                    $currentKreis = $result_plz->kreis;
                    break;
                }
            }

            if (empty($currentKreis))
            {
				throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
            }

            $myReturn['title'] = $currentKreis;


            $result_plz = $this->Database
                ->prepare("SELECT question,alias FROM tl_hvz where kreis = ? group by alias order by question asc")
                ->execute($currentKreis);


            $tmpArray = array();
            while ($result_plz->next())
			{
                $arrTemp = array();
                $arrTemp['title'] = StringUtil::specialchars($result_plz->question, true);
                $arrTemp['href'] = '/halteverbot/'.$result_plz->alias.'.html';
                $myReturn['items'][] = $arrTemp;
            }

            $pageTitle = "Halteverbot in ".$currentKreis." bestellen";
            $pageDisc = "Halteverbot in ".$currentKreis." bestellen. Wir stellen für Sie Ihre Schilder in ".$currentKreis." auf und schaffen Ihnen Platz.";
            $headline = "Halteverbot in ".$currentKreis." bestellen";
            $teaser = '<p>Nachfolgend finden Sie alle Städte und Dörfer im Kreis '.$currentKreis.', in denen wir Halteverbotzonen anbieten. Falls Ihr Kreis in der unten aufgeführten Liste nicht enthalten ist, schicken Sie uns eine <a href="halteverbot-anfrage.html" title="Halteverbot Anfrage">Anfrage</a>. Nach dem Erhalt Ihrer Anfrage, stellen wir Ihnen umgehend ein Angebot zusammen.</p>';


        }

        // request = kreis => Orte
        global $objPage;

        $objPage->pageTitle = $pageTitle;
        $newDescription = $pageDisc;
        $objPage->description = $newDescription;

        $this->Template->headline = $headline;
        $this->Template->teaser = $teaser;
        $this->Template->hvz = $myReturn;
    }


    /**
     * Create links and remember pages that have been processed
     * @param object
     * @return string
     * @throws \Exception
     */
    protected function generateHvzLink($objHvz)
    {
		/** @var PageModel $objPage */
        global $objPage;
        $myUrl = '/'.$objPage->alias.'/'.$objHvz.'.html';

        return $myUrl;
    }
}
