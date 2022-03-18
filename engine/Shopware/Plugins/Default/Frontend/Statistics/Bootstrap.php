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

use Doctrine\DBAL\Connection;
use Shopware\Components\Privacy\IpAnonymizerInterface;
use Shopware\Models\Config\Element;
use Shopware\Models\Tracking\ArticleImpression;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

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
            'scope' => Element::SCOPE_SHOP,
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
ko_yappo_robot;labelgrabber.txt;larbin;linkidator;linkscan;lockon;logo_gif;macworm;
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
            'scope' => Element::SCOPE_SHOP,
        ]);

        return true;
    }

    public function getInfo()
    {
        return [
            'label' => 'Statistiken',
        ];
    }

    /**
     * Check user is bot
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public function checkIsBot($userAgent)
    {
        $userAgent = preg_replace('/[^a-z]/', '', strtolower($userAgent));
        if (empty($userAgent)) {
            return false;
        }
        $result = false;
        $bots = preg_replace('/[^a-z;]/', '', strtolower($this->Config()->get('botBlackList')));
        $bots = explode(';', $bots);
        if (str_replace($bots, '', $userAgent) !== $userAgent) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param Enlight_Controller_Request_Request       $request
     * @param Enlight_Controller_Response_ResponseHttp $response
     *
     * @return void
     */
    public function updateLog($request, $response)
    {
        if ($this->shouldRefreshLog($request)) {
            $this->cleanupStatistic();
            $this->refreshBasket($request);
            $this->refreshLog($request);
            $this->refreshReferer($request);
            $this->refreshArticleImpression($request);
            $this->refreshBlog($request);
            $this->refreshCurrentUsers($request);
            $this->refreshPartner($request, $response);
        }
    }

    /**
     * @param Enlight_Controller_Request_Request $request
     *
     * @return void
     */
    public function refreshBasket(Enlight_Controller_Request_RequestHttp $request)
    {
        $currentController = $request->getParam('requestController', $request->getControllerName());
        $sessionId = (string) $request->getSession()->getId();

        if (!empty($currentController) && !empty($sessionId)) {
            $userId = (int) $request->getSession()->get('sUserId');
            $userAgent = (string) $request->getServer('HTTP_USER_AGENT');
            $sql = '
                UPDATE s_order_basket
                SET lastviewport = ?,
                    useragent = ?,
                    userID = ?
                WHERE sessionID=?
            ';
            Shopware()->Db()->query($sql, [
                $currentController, $userAgent,
                $userId, $sessionId,
            ]);
        }
    }

    /**
     * @return bool
     */
    public function shouldRefreshLog(Enlight_Controller_Request_Request $request)
    {
        if ($request->getClientIp() === null
            || !empty(Shopware()->Session()->get('Bot'))
        ) {
            return false;
        }
        if (!empty($this->Config()->get('blockIp'))
            && strpos($this->Config()->get('blockIp'), $request->getClientIp()) !== false
        ) {
            return false;
        }

        return true;
    }

    /**
     * Cleanup statistic
     *
     * @return void
     */
    public function cleanupStatistic()
    {
        if ((rand() % 10) === 0) {
            $sql = 'DELETE FROM s_statistics_currentusers WHERE time < DATE_SUB(NOW(), INTERVAL 3 MINUTE)';
            Shopware()->Db()->query($sql);
        }
    }

    /**
     * Refresh current users
     *
     * @throws Exception
     * @throws Zend_Db_Adapter_Exception
     *
     * @return void
     */
    public function refreshCurrentUsers(Enlight_Controller_Request_Request $request)
    {
        $sql = '
        INSERT INTO s_statistics_currentusers (remoteaddr, page, `time`, userID, deviceType)
        VALUES (?, ?, NOW(), ?, ?)';

        $ip = $this->get(IpAnonymizerInterface::class)->anonymize($request->getClientIp());

        Shopware()->Db()->query($sql, [
            $ip,
            $request->getParam('requestPage', $request->getRequestUri()),
            empty(Shopware()->Session()->get('sUserId')) ? 0 : (int) Shopware()->Session()->get('sUserId'),
            $request->getDeviceType(),
        ]);
    }

    /**
     * Refresh visitor log
     *
     * @throws Exception
     *
     * @return void
     */
    public function refreshLog(Enlight_Controller_Request_Request $request)
    {
        $deviceType = $request->getDeviceType();
        $shopId = Shopware()->Shop()->getId();
        $isNewRecord = false;

        $sql = '
            SELECT 1
            FROM s_statistics_visitors
            WHERE datum = CURDATE()
            AND shopID = :shopId
            AND deviceType = :deviceType';
        $result = Shopware()->Db()->fetchOne(
            $sql,
            [
                'shopId' => $shopId,
                'deviceType' => $deviceType,
            ]
        );
        if (empty($result)) {
            $sql = '
                INSERT INTO s_statistics_visitors
                (datum, shopID, pageimpressions, uniquevisits, deviceType)
                VALUES(NOW(), :shopId, 1, 1, :deviceType)
            ';
            Shopware()->Db()->query(
                $sql,
                [
                    'shopId' => $shopId,
                    'deviceType' => $deviceType,
                ]
            );
            $isNewRecord = true;
        }

        // IP is being hashed in a way to not be easily revertible
        $userHash = md5($request->getClientIp() . $request->getHttpHost());

        $result = Shopware()->Db()->fetchOne('SELECT 1 FROM s_statistics_pool WHERE datum = CURDATE() AND remoteaddr = ?', [$userHash]);
        if (empty($result)) {
            $sql = 'INSERT INTO s_statistics_pool (`remoteaddr`, `datum`) VALUES (?, NOW())';
            Shopware()->Db()->query($sql, [$userHash]);

            if ($isNewRecord === false) {
                $sql = 'UPDATE s_statistics_visitors SET pageimpressions=pageimpressions+1, uniquevisits=uniquevisits+1 WHERE datum=CURDATE() AND shopID = ? AND deviceType = ?';
                Shopware()->Db()->query($sql, [$shopId, $deviceType]);
            }
        } else {
            $sql = 'UPDATE s_statistics_visitors SET pageimpressions=pageimpressions+1 WHERE datum=CURDATE() AND shopID = ? AND deviceType = ?';
            Shopware()->Db()->query($sql, [$shopId, $deviceType]);
        }
    }

    /**
     * Refresh referrer log
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @return void
     */
    public function refreshReferer($request)
    {
        $referer = $request->getParam('referer');
        $partner = $request->getParam('partner', $request->getParam('sPartner'));

        if (empty($referer)
            || strpos($referer, 'http') !== 0
            || strpos($referer, $request->getHttpHost()) !== false
            || !empty(Shopware()->Session()->get('Admin'))
        ) {
            return;
        }

        Shopware()->Session()->set('sReferer', $referer);

        if ($partner !== null) {
            $referer .= '$' . $partner;
        }

        $sql = 'INSERT INTO s_statistics_referer (datum, referer) VALUES (NOW(), ?)';
        Shopware()->Db()->query($sql, [$referer]);
    }

    /**
     * Refresh article impressions
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @return void
     */
    public function refreshArticleImpression($request)
    {
        $articleId = $request->getParam('articleId');
        $deviceType = $request->getDeviceType();
        if (empty($articleId)) {
            return;
        }
        $shopId = Shopware()->Shop()->getId();
        $repository = Shopware()->Models()->getRepository(ArticleImpression::class);
        $articleImpressionQuery = $repository->getArticleImpressionQuery($articleId, $shopId, null, $deviceType);
        $articleImpression = $articleImpressionQuery->getOneOrNullResult();

        // If no Entry for this day exists - create a new one
        if ($articleImpression === null) {
            $articleImpression = new ArticleImpression($articleId, $shopId, null, 1, $deviceType);
            Shopware()->Models()->persist($articleImpression);
        } else {
            $articleImpression->increaseImpressions();
        }
        Shopware()->Models()->flush();
    }

    /**
     * Refresh partner log
     *
     * @param Enlight_Controller_Request_Request       $request
     * @param Enlight_Controller_Response_ResponseHttp $response
     *
     * @return void
     */
    public function refreshPartner($request, $response)
    {
        $partner = $request->getParam('partner', $request->getParam('sPartner'));
        if ($partner !== null) {
            if (strpos($partner, 'sCampaign') === 0) {
                $campaignID = (int) str_replace('sCampaign', '', $partner);
                if (!empty($campaignID)) {
                    Shopware()->Session()->set('sPartner', 'sCampaign' . $campaignID);
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
                    $basePath = $request->getBasePath();
                    if ($basePath === null || $basePath === '') {
                        $basePath = '/';
                    }
                    $response->headers->setCookie(
                        new Cookie('partner', $row['idcode'], $valid, $basePath, null, $request->isSecure())
                    );
                }
                Shopware()->Session()->set('sPartner', $partner);
            }
        } elseif ($request->getCookie('partner') !== null) {
            $sql = 'SELECT idcode FROM s_emarketing_partner WHERE active=1 AND idcode=?';
            $partner = Shopware()->Db()->fetchOne($sql, [$request->getCookie('partner')]);
            if (empty($partner)) {
                Shopware()->Session()->offsetUnset('sPartner');
            } else {
                Shopware()->Session()->set('sPartner', $partner);
            }
        }
    }

    private function refreshBlog(Request $request): void
    {
        $blogArticleId = $request->query->getInt('blogId');
        if (empty($blogArticleId)) {
            return;
        }
        /** @var Connection $connection */
        $connection = $this->get('dbal_connection');
        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');

        // Count the views of this blog item
        $visitedBlogItems = $session->get('visitedBlogItems', []);
        if (\in_array($blogArticleId, $visitedBlogItems, true)) {
            return;
        }
        // Use plain sql, because the http cache should no be cleared
        $query = $connection->createQueryBuilder()
            ->update('s_blog', 'b')
            ->set('b.views', 'IFNULL(b.views, 0) + 1')
            ->where('b.id = :id')
            ->setParameter('id', $blogArticleId);
        $query->execute();

        $visitedBlogItems[] = $blogArticleId;
        $session->offsetSet('visitedBlogItems', $visitedBlogItems);
    }
}
