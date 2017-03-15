<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware Statistics Plugin
 */
class Shopware_Plugins_Frontend_Statistics_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $form = $this->Form();

        $parent = $this->Forms()->findOneBy(['name' => 'Core']);
        $form->setParent($parent);

        $form->setElement('text', 'blockIp', [
            'label' => 'IP von Statistiken ausschließen', 'value' => null,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
        ]);

        $form->setElement('textarea', 'botBlackList', [
                'label' => 'Bot-Liste',
                'value' => 'antibot;appie;architext;bjaaland;digout4u;echo;fast-webcrawler;ferret;googlebot;
gulliver;harvest;htdig;ia_archiver;jeeves;jennybot;linkwalker;lycos;mercator;moget;muscatferret;
myweb;netcraft;nomad;petersnews;scooter;slurp;unlost_web_crawler;voila;voyager;webbase;weblayers;
wget;wisenutbot;acme.spider;ahoythehomepagefinder;alkaline;arachnophilia;aretha;ariadne;arks;
aspider;atn.txt;atomz;auresys;backrub;bigbrother;blackwidow;blindekuh;bloodhound;brightnet;bspider;
cactvschemistryspider;cassandra;cgireader;checkbot;churl;cmc;collective;combine;conceptbot;coolbot;
core;cosmos;cruiser;cusco;cyberspyder;deweb;dienstspider;digger;diibot;directhit;dnabot;
download_express;dragonbot;dwcp;e-collector;ebiness;eit;elfinbot;emacs;emcspider;esther;
evliyacelebi;nzexplorer;fdse;felix;fetchrover;fido;finnish;fireball;fouineur;francoroute;
freecrawl;funnelweb;gama;gazz;gcreep;getbot;geturl;golem;grapnel;griffon;gromit;hambot;
havindex;hometown;htmlgobble;hyperdecontextualizer;iajabot;ibm;iconoclast;ilse;imagelock;
incywincy;informant;infoseek;infoseeksidewinder;infospider;inspectorwww;intelliagent;irobot;
israelisearch;javabee;jbot;jcrawler;jobo;jobot;joebot;jubii;jumpstation;katipo;kdd;kilroy;
ko_yappo_robot;labelgrabber.txt;larbin;legs;linkidator;linkscan;lockon;logo_gif;macworm;
magpie;marvin;mattie;mediafox;merzscope;meshexplorer;mindcrawler;momspider;monster;motor;
mwdsearch;netcarta;netmechanic;netscoop;newscan-online;nhse;northstar;occam;octopus;openfind;
orb_search;packrat;pageboy;parasite;patric;pegasus;perignator;perlcrawler;phantom;piltdownman;
pimptrain;pioneer;pitkow;pjspider;pka;plumtreewebaccessor;poppi;portalb;puu;python;raven;rbse;
resumerobot;rhcs;roadrunner;robbie;robi;robofox;robozilla;roverbot;rules;safetynetrobot;search_au;
searchprocess;senrigan;sgscout;shaggy;shaihulud;sift;simbot;site-valet;sitegrabber;sitetech;
slcrawler;smartspider;snooper;solbot;spanner;speedy;spider_monkey;spiderbot;spiderline;
spiderman;spiderview;spry;ssearcher;suke;suntek;sven;tach_bw;tarantula;tarspider;techbot;
templeton;teoma_agent1;titin;titan;tkwww;tlspider;ucsd;udmsearch;urlck;valkyrie;victoria;
visionsearch;vwbot;w3index;w3m2;wallpaper;wanderer;wapspider;webbandit;webcatcher;webcopy;
webfetcher;webfoot;weblinker;webmirror;webmoose;webquest;webreader;webreaper;websnarf;webspider;
webvac;webwalk;webwalker;webwatch;whatuseek;whowhere;wired-digital;wmir;wolp;wombat;worm;wwwc;
wz101;xget;awbot;bobby;boris;bumblebee;cscrawler;daviesbot;ezresult;gigabot;gnodspider;internetseer;
justview;linkbot;linkchecker;nederland.zoek;perman;pompos;pooodle;redalert;shoutcast;slysearch;
ultraseek;webcompass;yandex;robot;yahoo;bot;psbot;crawl;RSS;larbin;ichiro;Slurp;msnbot;bot;Googlebot;
ShopWiki;Bot;WebAlta;;abachobot;architext;ask jeeves;frooglebot;googlebot;lycos;spider;HTTPClient',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
            ]);

        return true;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return [
            'label' => 'Statistiken',
        ];
    }

    /**
     * @param Enlight_Controller_Request_Request $request
     * @param $response
     */
    public function updateLog($request, $response)
    {
        $container = Shopware()->Container();

        $context = $container->get('shopware_storefront.context_service')->getShopContext();

        $container->get('shopware.statistics.tracer')
            ->trace($request, $context);

        $this->refreshReferer($request);
        $this->refreshArticleImpression($request);
        $this->refreshCurrentUsers($request);
        $this->refreshPartner($request, $response);
    }

    /**
     * Refresh current users
     *
     * @param \Enlight_Controller_Request_Request $request
     */
    public function refreshCurrentUsers(Enlight_Controller_Request_Request $request)
    {
        $sql = '
        INSERT INTO s_statistics_currentusers (remoteaddr, page, `time`, userID, deviceType)
        VALUES (?, ?, NOW(), ?, ?)';
        Shopware()->Db()->query($sql, [
            $request->getClientIp(),
            $request->getParam('requestPage', $request->getRequestUri()),
            empty(Shopware()->Session()->sUserId) ? 0 : (int) Shopware()->Session()->sUserId,
            $request->getDeviceType(),
        ]);
    }

    /**
     * Refresh referrer log
     *
     * @param \Enlight_Controller_Request_Request $request
     */
    public function refreshReferer($request)
    {
        $referer = $request->getParam('referer');
        $partner = $request->getParam('partner', $request->getParam('sPartner'));

        if (empty($referer)
            || strpos($referer, 'http') !== 0
            || strpos($referer, $request->getHttpHost()) !== false
            || !empty(Shopware()->Session()->Admin)
        ) {
            return;
        }

        Shopware()->Session()->sReferer = $referer;

        if ($partner !== null) {
            $referer .= '$' . $partner;
        }

        $sql = 'INSERT INTO s_statistics_referer (datum, referer) VALUES (NOW(), ?)';
        Shopware()->Db()->query($sql, [$referer]);
    }

    /**
     * Refresh article impressions
     *
     * @param \Enlight_Controller_Request_Request $request
     *
     * @return null|object|\Shopware\Models\Tracking\Banner
     */
    public function refreshArticleImpression($request)
    {
        $articleId = $request->getParam('articleId');
        $deviceType = $request->getDeviceType();
        if (empty($articleId)) {
            return;
        }
        $shopId = Shopware()->Shop()->getId();
        /** @var $repository \Shopware\Models\Tracking\Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Tracking\ArticleImpression');
        $articleImpressionQuery = $repository->getArticleImpressionQuery($articleId, $shopId, null, $deviceType);
        /** @var $articleImpression \Shopware\Models\Tracking\ArticleImpression */
        $articleImpression = $articleImpressionQuery->getOneOrNullResult();

        // If no Entry for this day exists - create a new one
        if ($articleImpression === null) {
            $articleImpression = new \Shopware\Models\Tracking\ArticleImpression($articleId, $shopId, null, 1, $deviceType);
            Shopware()->Models()->persist($articleImpression);
        } else {
            $articleImpression->increaseImpressions();
        }
        Shopware()->Models()->flush();
    }

    /**
     * Refresh partner log
     *
     * @param \Enlight_Controller_Request_Request       $request
     * @param \Enlight_Controller_Response_ResponseHttp $response
     */
    public function refreshPartner($request, $response)
    {
        $partner = $request->getParam('partner', $request->getParam('sPartner'));
        if ($partner !== null) {
            if (strpos($partner, 'sCampaign') === 0) {
                $campaignID = (int) str_replace('sCampaign', '', $partner);
                if (!empty($campaignID)) {
                    Shopware()->Session()->sPartner = 'sCampaign' . $campaignID;
                    $sql = '
                        UPDATE s_campaigns_mailings
                        SET clicked = clicked + 1
                        WHERE id = ?
                    ';
                    Shopware()->Db()->query($sql, [$campaignID]);
                }
            } else {
                $sql = 'SELECT * FROM s_emarketing_partner WHERE active=1 AND idcode=?';
                $row = Shopware()->Db()->fetchRow($sql, [$partner]);
                if (!empty($row)) {
                    if ($row['cookielifetime']) {
                        $valid = time() + $row['cookielifetime'];
                    } else {
                        $valid = 0;
                    }
                    $response->setCookie('partner', $row['idcode'], $valid, '/');
                }
                Shopware()->Session()->sPartner = $partner;
            }
        } elseif ($request->getCookie('partner') !== null) {
            $sql = 'SELECT idcode FROM s_emarketing_partner WHERE active=1 AND idcode=?';
            $partner = Shopware()->Db()->fetchOne($sql, [$request->getCookie('partner')]);
            if (empty($partner)) {
                unset(Shopware()->Session()->sPartner);
            } else {
                Shopware()->Session()->sPartner = $partner;
            }
        }
    }
}
