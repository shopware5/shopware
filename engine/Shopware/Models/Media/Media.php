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

namespace   Shopware\Models\Media;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * In Shopware all media resources are represented in the media model.
 * <br>
 * The uploaded media is assigned to albums. Each media can assigned to only one album.
 * The uploaded media can be different types such as images, PDF files or videos.
 * One media has the following associations:
 * <code>
 *   - Album  =>  Shopware\Models\Media\Album  [n:1] [s_media_album]
 * </code>
 * The s_media table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - KEY `Album` (`albumID`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_media")
 * @ORM\HasLifecycleCallbacks
 */
class Media extends ModelEntity
{
    /**
     * Flag for an image media
     */
    const TYPE_IMAGE = 'IMAGE';

    /**
     * Flag for a video media
     */
    const TYPE_VIDEO = 'VIDEO';

    /**
     * Flag for a music media
     */
    const TYPE_MUSIC = 'MUSIC';

    /**
     * Flag for an archive media
     */
    const TYPE_ARCHIVE = 'ARCHIVE';

    /**
     * Flag for a pdf media
     */
    const TYPE_PDF = 'PDF';

    /**
     * Flag for a 3D model media
     */
    const TYPE_MODEL = 'MODEL';

    /**
     * Flag for an unknown media
     */
    const TYPE_UNKNOWN = 'UNKNOWN';

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Media", mappedBy="media", orphanRemoval=true, cascade={"persist"})
     *
     * @var \Shopware\Models\Attribute\Media
     */
    protected $attribute;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Image", mappedBy="media")
     */
    protected $articles;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Media", mappedBy="media", orphanRemoval=true, cascade={"persist"})
     */
    protected $blogMedia;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Shopware\Models\Property\Value", mappedBy="media")
     */
    protected $properties;

    /**
     * Contains the default thumbnail sizes which used for backend modules.
     *
     * @var array
     */
    private $defaultThumbnails = [
        [140, 140],
    ];

    /**
     * All known file extensions and the mapped media type
     *
     * @var array
     */
    private $typeMapping = [
        '24b' => self::TYPE_IMAGE,
        'ai' => self::TYPE_IMAGE,
        'bmp' => self::TYPE_IMAGE,
        'cdr' => self::TYPE_IMAGE,
        'gif' => self::TYPE_IMAGE,
        'iff' => self::TYPE_IMAGE,
        'ilbm' => self::TYPE_IMAGE,
        'jpeg' => self::TYPE_IMAGE,
        'jpg' => self::TYPE_IMAGE,
        'pcx' => self::TYPE_IMAGE,
        'png' => self::TYPE_IMAGE,
        'tif' => self::TYPE_IMAGE,
        'tiff' => self::TYPE_IMAGE,
        'eps' => self::TYPE_IMAGE,
        'pbm' => self::TYPE_IMAGE,
        'psd' => self::TYPE_IMAGE,
        'wbm' => self::TYPE_IMAGE,
        '264' => self::TYPE_VIDEO,
        '3g2' => self::TYPE_VIDEO,
        '3gp' => self::TYPE_VIDEO,
        '3gp2' => self::TYPE_VIDEO,
        '3gpp' => self::TYPE_VIDEO,
        '3gpp2' => self::TYPE_VIDEO,
        '3mm' => self::TYPE_VIDEO,
        '3p2' => self::TYPE_VIDEO,
        '60d' => self::TYPE_VIDEO,
        '787' => self::TYPE_VIDEO,
        'aaf' => self::TYPE_VIDEO,
        'aep' => self::TYPE_VIDEO,
        'aepx' => self::TYPE_VIDEO,
        'aet' => self::TYPE_VIDEO,
        'aetx' => self::TYPE_VIDEO,
        'ajp' => self::TYPE_VIDEO,
        'ale' => self::TYPE_VIDEO,
        'amv' => self::TYPE_VIDEO,
        'amx' => self::TYPE_VIDEO,
        'anim' => self::TYPE_VIDEO,
        'arf' => self::TYPE_VIDEO,
        'asf' => self::TYPE_VIDEO,
        'asx' => self::TYPE_VIDEO,
        'avb' => self::TYPE_VIDEO,
        'avd' => self::TYPE_VIDEO,
        'avi' => self::TYPE_VIDEO,
        'avp' => self::TYPE_VIDEO,
        'avs' => self::TYPE_VIDEO,
        'axm' => self::TYPE_VIDEO,
        'bdm' => self::TYPE_VIDEO,
        'bdmv' => self::TYPE_VIDEO,
        'bik' => self::TYPE_VIDEO,
        'bin' => self::TYPE_VIDEO,
        'bix' => self::TYPE_VIDEO,
        'bmk' => self::TYPE_VIDEO,
        'bnp' => self::TYPE_VIDEO,
        'box' => self::TYPE_VIDEO,
        'bs4' => self::TYPE_VIDEO,
        'bsf' => self::TYPE_VIDEO,
        'byu' => self::TYPE_VIDEO,
        'camproj' => self::TYPE_VIDEO,
        'camrec' => self::TYPE_VIDEO,
        'clpi' => self::TYPE_VIDEO,
        'cmmp' => self::TYPE_VIDEO,
        'cmmtpl' => self::TYPE_VIDEO,
        'cmproj' => self::TYPE_VIDEO,
        'cmrec' => self::TYPE_VIDEO,
        'cpi' => self::TYPE_VIDEO,
        'cst' => self::TYPE_VIDEO,
        'cvc' => self::TYPE_VIDEO,
        'd2v' => self::TYPE_VIDEO,
        'd3v' => self::TYPE_VIDEO,
        'dat' => self::TYPE_VIDEO,
        'dav' => self::TYPE_VIDEO,
        'dce' => self::TYPE_VIDEO,
        'dck' => self::TYPE_VIDEO,
        'ddat' => self::TYPE_VIDEO,
        'dif' => self::TYPE_VIDEO,
        'dir' => self::TYPE_VIDEO,
        'divx' => self::TYPE_VIDEO,
        'dlx' => self::TYPE_VIDEO,
        'dmb' => self::TYPE_VIDEO,
        'dmsd' => self::TYPE_VIDEO,
        'dmsd3d' => self::TYPE_VIDEO,
        'dmsm' => self::TYPE_VIDEO,
        'dmsm3d' => self::TYPE_VIDEO,
        'dmss' => self::TYPE_VIDEO,
        'dnc' => self::TYPE_VIDEO,
        'dpa' => self::TYPE_VIDEO,
        'dpg' => self::TYPE_VIDEO,
        'dream' => self::TYPE_VIDEO,
        'dsy' => self::TYPE_VIDEO,
        'dv' => self::TYPE_VIDEO,
        'dv-avi' => self::TYPE_VIDEO,
        'dv4' => self::TYPE_VIDEO,
        'dvdmedia' => self::TYPE_VIDEO,
        'dvr' => self::TYPE_VIDEO,
        'dvr-ms' => self::TYPE_VIDEO,
        'dvx' => self::TYPE_VIDEO,
        'dxr' => self::TYPE_VIDEO,
        'dzm' => self::TYPE_VIDEO,
        'dzp' => self::TYPE_VIDEO,
        'dzt' => self::TYPE_VIDEO,
        'edl' => self::TYPE_VIDEO,
        'evo' => self::TYPE_VIDEO,
        'eye' => self::TYPE_VIDEO,
        'f4p' => self::TYPE_VIDEO,
        'f4v' => self::TYPE_VIDEO,
        'fbr' => self::TYPE_VIDEO,
        'fbz' => self::TYPE_VIDEO,
        'fcp' => self::TYPE_VIDEO,
        'fcproject' => self::TYPE_VIDEO,
        'flc' => self::TYPE_VIDEO,
        'flh' => self::TYPE_VIDEO,
        'fli' => self::TYPE_VIDEO,
        'flv' => self::TYPE_VIDEO,
        'flx' => self::TYPE_VIDEO,
        'gfp' => self::TYPE_VIDEO,
        'gl' => self::TYPE_VIDEO,
        'grasp' => self::TYPE_VIDEO,
        'gts' => self::TYPE_VIDEO,
        'gvi' => self::TYPE_VIDEO,
        'gvp' => self::TYPE_VIDEO,
        'h264' => self::TYPE_VIDEO,
        'hdmov' => self::TYPE_VIDEO,
        'hkm' => self::TYPE_VIDEO,
        'ifo' => self::TYPE_VIDEO,
        'imovieproj' => self::TYPE_VIDEO,
        'imovieproject' => self::TYPE_VIDEO,
        'irf' => self::TYPE_VIDEO,
        'ism' => self::TYPE_VIDEO,
        'ismc' => self::TYPE_VIDEO,
        'ismv' => self::TYPE_VIDEO,
        'iva' => self::TYPE_VIDEO,
        'ivf' => self::TYPE_VIDEO,
        'ivr' => self::TYPE_VIDEO,
        'ivs' => self::TYPE_VIDEO,
        'izz' => self::TYPE_VIDEO,
        'izzy' => self::TYPE_VIDEO,
        'jts' => self::TYPE_VIDEO,
        'jtv' => self::TYPE_VIDEO,
        'k3g' => self::TYPE_VIDEO,
        'lrec' => self::TYPE_VIDEO,
        'lsf' => self::TYPE_VIDEO,
        'lsx' => self::TYPE_VIDEO,
        'm15' => self::TYPE_VIDEO,
        'm1pg' => self::TYPE_VIDEO,
        'm1v' => self::TYPE_VIDEO,
        'm21' => self::TYPE_VIDEO,
        'm2a' => self::TYPE_VIDEO,
        'm2p' => self::TYPE_VIDEO,
        'm2t' => self::TYPE_VIDEO,
        'm2ts' => self::TYPE_VIDEO,
        'm2v' => self::TYPE_VIDEO,
        'm4e' => self::TYPE_VIDEO,
        'm4u' => self::TYPE_VIDEO,
        'm4v' => self::TYPE_VIDEO,
        'm75' => self::TYPE_VIDEO,
        'meta' => self::TYPE_VIDEO,
        'mgv' => self::TYPE_VIDEO,
        'mj2' => self::TYPE_VIDEO,
        'mjp' => self::TYPE_VIDEO,
        'mjpg' => self::TYPE_VIDEO,
        'mkv' => self::TYPE_VIDEO,
        'mmv' => self::TYPE_VIDEO,
        'mnv' => self::TYPE_VIDEO,
        'mob' => self::TYPE_VIDEO,
        'mod' => self::TYPE_VIDEO,
        'modd' => self::TYPE_VIDEO,
        'moff' => self::TYPE_VIDEO,
        'moi' => self::TYPE_VIDEO,
        'moov' => self::TYPE_VIDEO,
        'mov' => self::TYPE_VIDEO,
        'movie' => self::TYPE_VIDEO,
        'mp21' => self::TYPE_VIDEO,
        'mp2v' => self::TYPE_VIDEO,
        'mp4' => self::TYPE_VIDEO,
        'mp4v' => self::TYPE_VIDEO,
        'mpe' => self::TYPE_VIDEO,
        'mpeg' => self::TYPE_VIDEO,
        'mpeg4' => self::TYPE_VIDEO,
        'mpf' => self::TYPE_VIDEO,
        'mpg' => self::TYPE_VIDEO,
        'mpg2' => self::TYPE_VIDEO,
        'mpgindex' => self::TYPE_VIDEO,
        'mpl' => self::TYPE_VIDEO,
        'mpls' => self::TYPE_VIDEO,
        'mpsub' => self::TYPE_VIDEO,
        'mpv' => self::TYPE_VIDEO,
        'mpv2' => self::TYPE_VIDEO,
        'mqv' => self::TYPE_VIDEO,
        'msdvd' => self::TYPE_VIDEO,
        'msh' => self::TYPE_VIDEO,
        'mswmm' => self::TYPE_VIDEO,
        'mts' => self::TYPE_VIDEO,
        'mtv' => self::TYPE_VIDEO,
        'mvb' => self::TYPE_VIDEO,
        'mvc' => self::TYPE_VIDEO,
        'mvd' => self::TYPE_VIDEO,
        'mve' => self::TYPE_VIDEO,
        'mvp' => self::TYPE_VIDEO,
        'mvy' => self::TYPE_VIDEO,
        'mxf' => self::TYPE_VIDEO,
        'mys' => self::TYPE_VIDEO,
        'ncor' => self::TYPE_VIDEO,
        'nsv' => self::TYPE_VIDEO,
        'nuv' => self::TYPE_VIDEO,
        'nvc' => self::TYPE_VIDEO,
        'ogm' => self::TYPE_VIDEO,
        'ogv' => self::TYPE_VIDEO,
        'ogx' => self::TYPE_VIDEO,
        'osp' => self::TYPE_VIDEO,
        'par' => self::TYPE_VIDEO,
        'pds' => self::TYPE_VIDEO,
        'pgi' => self::TYPE_VIDEO,
        'photoshow' => self::TYPE_VIDEO,
        'piv' => self::TYPE_VIDEO,
        'playlist' => self::TYPE_VIDEO,
        'pmf' => self::TYPE_VIDEO,
        'pmv' => self::TYPE_VIDEO,
        'pns' => self::TYPE_VIDEO,
        'ppj' => self::TYPE_VIDEO,
        'prel' => self::TYPE_VIDEO,
        'pro' => self::TYPE_VIDEO,
        'prproj' => self::TYPE_VIDEO,
        'prtl' => self::TYPE_VIDEO,
        'psh' => self::TYPE_VIDEO,
        'pssd' => self::TYPE_VIDEO,
        'pva' => self::TYPE_VIDEO,
        'pvr' => self::TYPE_VIDEO,
        'pxv' => self::TYPE_VIDEO,
        'qt' => self::TYPE_VIDEO,
        'qtch' => self::TYPE_VIDEO,
        'qtl' => self::TYPE_VIDEO,
        'qtm' => self::TYPE_VIDEO,
        'qtz' => self::TYPE_VIDEO,
        'r3d' => self::TYPE_VIDEO,
        'rcproject' => self::TYPE_VIDEO,
        'rdb' => self::TYPE_VIDEO,
        'rec' => self::TYPE_VIDEO,
        'rm' => self::TYPE_VIDEO,
        'rmd' => self::TYPE_VIDEO,
        'rmp' => self::TYPE_VIDEO,
        'rms' => self::TYPE_VIDEO,
        'rmvb' => self::TYPE_VIDEO,
        'roq' => self::TYPE_VIDEO,
        'rp' => self::TYPE_VIDEO,
        'rsx' => self::TYPE_VIDEO,
        'rts' => self::TYPE_VIDEO,
        'rum' => self::TYPE_VIDEO,
        'rv' => self::TYPE_VIDEO,
        'sbk' => self::TYPE_VIDEO,
        'sbt' => self::TYPE_VIDEO,
        'scc' => self::TYPE_VIDEO,
        'scm' => self::TYPE_VIDEO,
        'scn' => self::TYPE_VIDEO,
        'screenflow' => self::TYPE_VIDEO,
        'sec' => self::TYPE_VIDEO,
        'seq' => self::TYPE_VIDEO,
        'sfd' => self::TYPE_VIDEO,
        'sfvidcap' => self::TYPE_VIDEO,
        'smi' => self::TYPE_VIDEO,
        'smil' => self::TYPE_VIDEO,
        'smk' => self::TYPE_VIDEO,
        'sml' => self::TYPE_VIDEO,
        'smv' => self::TYPE_VIDEO,
        'spl' => self::TYPE_VIDEO,
        'sqz' => self::TYPE_VIDEO,
        'srt' => self::TYPE_VIDEO,
        'ssm' => self::TYPE_VIDEO,
        'str' => self::TYPE_VIDEO,
        'stx' => self::TYPE_VIDEO,
        'svi' => self::TYPE_VIDEO,
        'swf' => self::TYPE_VIDEO,
        'swi' => self::TYPE_VIDEO,
        'swt' => self::TYPE_VIDEO,
        'tda3mt' => self::TYPE_VIDEO,
        'tdx' => self::TYPE_VIDEO,
        'tivo' => self::TYPE_VIDEO,
        'tix' => self::TYPE_VIDEO,
        'tod' => self::TYPE_VIDEO,
        'tp' => self::TYPE_VIDEO,
        'tp0' => self::TYPE_VIDEO,
        'tpd' => self::TYPE_VIDEO,
        'tpr' => self::TYPE_VIDEO,
        'trp' => self::TYPE_VIDEO,
        'ts' => self::TYPE_VIDEO,
        'tsp' => self::TYPE_VIDEO,
        'tvs' => self::TYPE_VIDEO,
        'vc1' => self::TYPE_VIDEO,
        'vcpf' => self::TYPE_VIDEO,
        'vcr' => self::TYPE_VIDEO,
        'vcv' => self::TYPE_VIDEO,
        'vdo' => self::TYPE_VIDEO,
        'vdr' => self::TYPE_VIDEO,
        'veg' => self::TYPE_VIDEO,
        'vem' => self::TYPE_VIDEO,
        'vep' => self::TYPE_VIDEO,
        'vf' => self::TYPE_VIDEO,
        'vft' => self::TYPE_VIDEO,
        'vfw' => self::TYPE_VIDEO,
        'vfz' => self::TYPE_VIDEO,
        'vgz' => self::TYPE_VIDEO,
        'vid' => self::TYPE_VIDEO,
        'video' => self::TYPE_VIDEO,
        'viewlet' => self::TYPE_VIDEO,
        'viv' => self::TYPE_VIDEO,
        'vivo' => self::TYPE_VIDEO,
        'vlab' => self::TYPE_VIDEO,
        'vob' => self::TYPE_VIDEO,
        'vp3' => self::TYPE_VIDEO,
        'vp6' => self::TYPE_VIDEO,
        'vp7' => self::TYPE_VIDEO,
        'vpj' => self::TYPE_VIDEO,
        'vro' => self::TYPE_VIDEO,
        'vs4' => self::TYPE_VIDEO,
        'vse' => self::TYPE_VIDEO,
        'vsp' => self::TYPE_VIDEO,
        'w32' => self::TYPE_VIDEO,
        'wcp' => self::TYPE_VIDEO,
        'webm' => self::TYPE_VIDEO,
        'wlmp' => self::TYPE_VIDEO,
        'wm' => self::TYPE_VIDEO,
        'wmd' => self::TYPE_VIDEO,
        'wmmp' => self::TYPE_VIDEO,
        'wmv' => self::TYPE_VIDEO,
        'wmx' => self::TYPE_VIDEO,
        'wot' => self::TYPE_VIDEO,
        'wp3' => self::TYPE_VIDEO,
        'wpl' => self::TYPE_VIDEO,
        'wtv' => self::TYPE_VIDEO,
        'wvx' => self::TYPE_VIDEO,
        'xej' => self::TYPE_VIDEO,
        'xel' => self::TYPE_VIDEO,
        'xesc' => self::TYPE_VIDEO,
        'xfl' => self::TYPE_VIDEO,
        'xlmv' => self::TYPE_VIDEO,
        'xvid' => self::TYPE_VIDEO,
        'yuv' => self::TYPE_VIDEO,
        'zm1' => self::TYPE_VIDEO,
        'zm2' => self::TYPE_VIDEO,
        'zm3' => self::TYPE_VIDEO,
        'zmv' => self::TYPE_VIDEO,
        '4mp' => self::TYPE_MUSIC,
        '669' => self::TYPE_MUSIC,
        '6cm' => self::TYPE_MUSIC,
        '8cm' => self::TYPE_MUSIC,
        '8med' => self::TYPE_MUSIC,
        '8svx' => self::TYPE_MUSIC,
        'a2m' => self::TYPE_MUSIC,
        'a52' => self::TYPE_MUSIC,
        'aa' => self::TYPE_MUSIC,
        'aa3' => self::TYPE_MUSIC,
        'aac' => self::TYPE_MUSIC,
        'aax' => self::TYPE_MUSIC,
        'ab' => self::TYPE_MUSIC,
        'abc' => self::TYPE_MUSIC,
        'abm' => self::TYPE_MUSIC,
        'ac3' => self::TYPE_MUSIC,
        'acd' => self::TYPE_MUSIC,
        'acd-bak' => self::TYPE_MUSIC,
        'acd-zip' => self::TYPE_MUSIC,
        'acm' => self::TYPE_MUSIC,
        'acp' => self::TYPE_MUSIC,
        'act' => self::TYPE_MUSIC,
        'adg' => self::TYPE_MUSIC,
        'adt' => self::TYPE_MUSIC,
        'adts' => self::TYPE_MUSIC,
        'adv' => self::TYPE_MUSIC,
        'afc' => self::TYPE_MUSIC,
        'agm' => self::TYPE_MUSIC,
        'ahx' => self::TYPE_MUSIC,
        'aif' => self::TYPE_MUSIC,
        'aifc' => self::TYPE_MUSIC,
        'aiff' => self::TYPE_MUSIC,
        'ais' => self::TYPE_MUSIC,
        'akp' => self::TYPE_MUSIC,
        'al' => self::TYPE_MUSIC,
        'alac' => self::TYPE_MUSIC,
        'alaw' => self::TYPE_MUSIC,
        'alc' => self::TYPE_MUSIC,
        'all' => self::TYPE_MUSIC,
        'als' => self::TYPE_MUSIC,
        'amf' => self::TYPE_MUSIC,
        'amr' => self::TYPE_MUSIC,
        'ams' => self::TYPE_MUSIC,
        'amxd' => self::TYPE_MUSIC,
        'aob' => self::TYPE_MUSIC,
        'ape' => self::TYPE_MUSIC,
        'apf' => self::TYPE_MUSIC,
        'apl' => self::TYPE_MUSIC,
        'aria' => self::TYPE_MUSIC,
        'ariax' => self::TYPE_MUSIC,
        'asd' => self::TYPE_MUSIC,
        'ase' => self::TYPE_MUSIC,
        'at3' => self::TYPE_MUSIC,
        'atrac' => self::TYPE_MUSIC,
        'au' => self::TYPE_MUSIC,
        'aud' => self::TYPE_MUSIC,
        'aup' => self::TYPE_MUSIC,
        'avr' => self::TYPE_MUSIC,
        'awb' => self::TYPE_MUSIC,
        'ay' => self::TYPE_MUSIC,
        'b4s' => self::TYPE_MUSIC,
        'band' => self::TYPE_MUSIC,
        'bap' => self::TYPE_MUSIC,
        'bdd' => self::TYPE_MUSIC,
        'bidule' => self::TYPE_MUSIC,
        'brstm' => self::TYPE_MUSIC,
        'bun' => self::TYPE_MUSIC,
        'bwf' => self::TYPE_MUSIC,
        'c01' => self::TYPE_MUSIC,
        'caf' => self::TYPE_MUSIC,
        'cda' => self::TYPE_MUSIC,
        'cdda' => self::TYPE_MUSIC,
        'cel' => self::TYPE_MUSIC,
        'cfa' => self::TYPE_MUSIC,
        'cfxr' => self::TYPE_MUSIC,
        'cidb' => self::TYPE_MUSIC,
        'cmf' => self::TYPE_MUSIC,
        'copy' => self::TYPE_MUSIC,
        'cpr' => self::TYPE_MUSIC,
        'cpt' => self::TYPE_MUSIC,
        'csh' => self::TYPE_MUSIC,
        'cwp' => self::TYPE_MUSIC,
        'd00' => self::TYPE_MUSIC,
        'd01' => self::TYPE_MUSIC,
        'dcf' => self::TYPE_MUSIC,
        'dcm' => self::TYPE_MUSIC,
        'dct' => self::TYPE_MUSIC,
        'ddt' => self::TYPE_MUSIC,
        'dewf' => self::TYPE_MUSIC,
        'df2' => self::TYPE_MUSIC,
        'dfc' => self::TYPE_MUSIC,
        'dig' => self::TYPE_MUSIC,
        'dls' => self::TYPE_MUSIC,
        'dm' => self::TYPE_MUSIC,
        'dmf' => self::TYPE_MUSIC,
        'dmsa' => self::TYPE_MUSIC,
        'dmse' => self::TYPE_MUSIC,
        'dra' => self::TYPE_MUSIC,
        'drg' => self::TYPE_MUSIC,
        'ds' => self::TYPE_MUSIC,
        'ds2' => self::TYPE_MUSIC,
        'dsf' => self::TYPE_MUSIC,
        'dsm' => self::TYPE_MUSIC,
        'dsp' => self::TYPE_MUSIC,
        'dss' => self::TYPE_MUSIC,
        'dtm' => self::TYPE_MUSIC,
        'dts' => self::TYPE_MUSIC,
        'dtshd' => self::TYPE_MUSIC,
        'dvf' => self::TYPE_MUSIC,
        'dwd' => self::TYPE_MUSIC,
        'ear' => self::TYPE_MUSIC,
        'efa' => self::TYPE_MUSIC,
        'efe' => self::TYPE_MUSIC,
        'efk' => self::TYPE_MUSIC,
        'efq' => self::TYPE_MUSIC,
        'efs' => self::TYPE_MUSIC,
        'efv' => self::TYPE_MUSIC,
        'emd' => self::TYPE_MUSIC,
        'emp' => self::TYPE_MUSIC,
        'emx' => self::TYPE_MUSIC,
        'esps' => self::TYPE_MUSIC,
        'expressionmap' => self::TYPE_MUSIC,
        'f2r' => self::TYPE_MUSIC,
        'f32' => self::TYPE_MUSIC,
        'f3r' => self::TYPE_MUSIC,
        'f4a' => self::TYPE_MUSIC,
        'f64' => self::TYPE_MUSIC,
        'far' => self::TYPE_MUSIC,
        'fda' => self::TYPE_MUSIC,
        'fff' => self::TYPE_MUSIC,
        'flac' => self::TYPE_MUSIC,
        'flp' => self::TYPE_MUSIC,
        'fls' => self::TYPE_MUSIC,
        'frg' => self::TYPE_MUSIC,
        'fsm' => self::TYPE_MUSIC,
        'ftm' => self::TYPE_MUSIC,
        'fzb' => self::TYPE_MUSIC,
        'fzf' => self::TYPE_MUSIC,
        'fzv' => self::TYPE_MUSIC,
        'g721' => self::TYPE_MUSIC,
        'g723' => self::TYPE_MUSIC,
        'g726' => self::TYPE_MUSIC,
        'gbproj' => self::TYPE_MUSIC,
        'gbs' => self::TYPE_MUSIC,
        'gig' => self::TYPE_MUSIC,
        'gm' => self::TYPE_MUSIC,
        'gp5' => self::TYPE_MUSIC,
        'gpbank' => self::TYPE_MUSIC,
        'gpk' => self::TYPE_MUSIC,
        'gpx' => self::TYPE_MUSIC,
        'gro' => self::TYPE_MUSIC,
        'groove' => self::TYPE_MUSIC,
        'gsm' => self::TYPE_MUSIC,
        'h0' => self::TYPE_MUSIC,
        'hdp' => self::TYPE_MUSIC,
        'hma' => self::TYPE_MUSIC,
        'hsb' => self::TYPE_MUSIC,
        'ics' => self::TYPE_MUSIC,
        'igp' => self::TYPE_MUSIC,
        'igr' => self::TYPE_MUSIC,
        'imf' => self::TYPE_MUSIC,
        'imp' => self::TYPE_MUSIC,
        'ins' => self::TYPE_MUSIC,
        'isma' => self::TYPE_MUSIC,
        'it' => self::TYPE_MUSIC,
        'iti' => self::TYPE_MUSIC,
        'its' => self::TYPE_MUSIC,
        'jam' => self::TYPE_MUSIC,
        'jo' => self::TYPE_MUSIC,
        'jo-7z' => self::TYPE_MUSIC,
        'k25' => self::TYPE_MUSIC,
        'k26' => self::TYPE_MUSIC,
        'kar' => self::TYPE_MUSIC,
        'kfn' => self::TYPE_MUSIC,
        'kin' => self::TYPE_MUSIC,
        'kit' => self::TYPE_MUSIC,
        'kmp' => self::TYPE_MUSIC,
        'koz' => self::TYPE_MUSIC,
        'kpl' => self::TYPE_MUSIC,
        'krz' => self::TYPE_MUSIC,
        'ksc' => self::TYPE_MUSIC,
        'ksf' => self::TYPE_MUSIC,
        'kt2' => self::TYPE_MUSIC,
        'kt3' => self::TYPE_MUSIC,
        'ktp' => self::TYPE_MUSIC,
        'l' => self::TYPE_MUSIC,
        'la' => self::TYPE_MUSIC,
        'lof' => self::TYPE_MUSIC,
        'lqt' => self::TYPE_MUSIC,
        'lso' => self::TYPE_MUSIC,
        'lvp' => self::TYPE_MUSIC,
        'lwv' => self::TYPE_MUSIC,
        'm1a' => self::TYPE_MUSIC,
        'm3u' => self::TYPE_MUSIC,
        'm3u8' => self::TYPE_MUSIC,
        'm4a' => self::TYPE_MUSIC,
        'm4b' => self::TYPE_MUSIC,
        'm4p' => self::TYPE_MUSIC,
        'm4r' => self::TYPE_MUSIC,
        'ma1' => self::TYPE_MUSIC,
        'mbr' => self::TYPE_MUSIC,
        'mdl' => self::TYPE_MUSIC,
        'med' => self::TYPE_MUSIC,
        'mgv' => self::TYPE_MUSIC,
        'mid' => self::TYPE_MUSIC,
        'midi' => self::TYPE_MUSIC,
        'miniusf' => self::TYPE_MUSIC,
        'mka' => self::TYPE_MUSIC,
        'mlp' => self::TYPE_MUSIC,
        'mmf' => self::TYPE_MUSIC,
        'mmm' => self::TYPE_MUSIC,
        'mmp' => self::TYPE_MUSIC,
        'mo3' => self::TYPE_MUSIC,
        'mod' => self::TYPE_MUSIC,
        'mp1' => self::TYPE_MUSIC,
        'mp2' => self::TYPE_MUSIC,
        'mp3' => self::TYPE_MUSIC,
        'mpa' => self::TYPE_MUSIC,
        'mpc' => self::TYPE_MUSIC,
        'mpga' => self::TYPE_MUSIC,
        'mpu' => self::TYPE_MUSIC,
        'mp_' => self::TYPE_MUSIC,
        'mscx' => self::TYPE_MUSIC,
        'mscz' => self::TYPE_MUSIC,
        'msv' => self::TYPE_MUSIC,
        'mt2' => self::TYPE_MUSIC,
        'mt9' => self::TYPE_MUSIC,
        'mte' => self::TYPE_MUSIC,
        'mtf' => self::TYPE_MUSIC,
        'mti' => self::TYPE_MUSIC,
        'mtm' => self::TYPE_MUSIC,
        'mtp' => self::TYPE_MUSIC,
        'mts' => self::TYPE_MUSIC,
        'mus' => self::TYPE_MUSIC,
        'mus' => self::TYPE_MUSIC,
        'musa' => self::TYPE_MUSIC,
        'mws' => self::TYPE_MUSIC,
        'mxl' => self::TYPE_MUSIC,
        'mxmf' => self::TYPE_MUSIC,
        'mzp' => self::TYPE_MUSIC,
        'nap' => self::TYPE_MUSIC,
        'ncw' => self::TYPE_MUSIC,
        'nkb' => self::TYPE_MUSIC,
        'nki' => self::TYPE_MUSIC,
        'nkm' => self::TYPE_MUSIC,
        'nks' => self::TYPE_MUSIC,
        'nkx' => self::TYPE_MUSIC,
        'npl' => self::TYPE_MUSIC,
        'nra' => self::TYPE_MUSIC,
        'nrt' => self::TYPE_MUSIC,
        'nsa' => self::TYPE_MUSIC,
        'nsf' => self::TYPE_MUSIC,
        'nst' => self::TYPE_MUSIC,
        'ntn' => self::TYPE_MUSIC,
        'nvf' => self::TYPE_MUSIC,
        'nwc' => self::TYPE_MUSIC,
        'odm' => self::TYPE_MUSIC,
        'ofr' => self::TYPE_MUSIC,
        'oga' => self::TYPE_MUSIC,
        'ogg' => self::TYPE_MUSIC,
        'okt' => self::TYPE_MUSIC,
        'oma' => self::TYPE_MUSIC,
        'omf' => self::TYPE_MUSIC,
        'omg' => self::TYPE_MUSIC,
        'omx' => self::TYPE_MUSIC,
        'orc' => self::TYPE_MUSIC,
        'ots' => self::TYPE_MUSIC,
        'ove' => self::TYPE_MUSIC,
        'ovw' => self::TYPE_MUSIC,
        'pac' => self::TYPE_MUSIC,
        'pat' => self::TYPE_MUSIC,
        'pbf' => self::TYPE_MUSIC,
        'pca' => self::TYPE_MUSIC,
        'pcast' => self::TYPE_MUSIC,
        'pcg' => self::TYPE_MUSIC,
        'pcm' => self::TYPE_MUSIC,
        'pd' => self::TYPE_MUSIC,
        'peak' => self::TYPE_MUSIC,
        'pek' => self::TYPE_MUSIC,
        'pho' => self::TYPE_MUSIC,
        'phy' => self::TYPE_MUSIC,
        'pk' => self::TYPE_MUSIC,
        'pkf' => self::TYPE_MUSIC,
        'pla' => self::TYPE_MUSIC,
        'pls' => self::TYPE_MUSIC,
        'pna' => self::TYPE_MUSIC,
        'ppc' => self::TYPE_MUSIC,
        'ppcx' => self::TYPE_MUSIC,
        'prg' => self::TYPE_MUSIC,
        'psf' => self::TYPE_MUSIC,
        'psm' => self::TYPE_MUSIC,
        'psy' => self::TYPE_MUSIC,
        'ptf' => self::TYPE_MUSIC,
        'ptm' => self::TYPE_MUSIC,
        'pts' => self::TYPE_MUSIC,
        'pvc' => self::TYPE_MUSIC,
        'qcp' => self::TYPE_MUSIC,
        'r' => self::TYPE_MUSIC,
        'r1m' => self::TYPE_MUSIC,
        'ra' => self::TYPE_MUSIC,
        'ram' => self::TYPE_MUSIC,
        'raw' => self::TYPE_MUSIC,
        'rax' => self::TYPE_MUSIC,
        'rbs' => self::TYPE_MUSIC,
        'rcy' => self::TYPE_MUSIC,
        'rex' => self::TYPE_MUSIC,
        'rfl' => self::TYPE_MUSIC,
        'rip' => self::TYPE_MUSIC,
        'rmf' => self::TYPE_MUSIC,
        'rmi' => self::TYPE_MUSIC,
        'rmj' => self::TYPE_MUSIC,
        'rmm' => self::TYPE_MUSIC,
        'rmx' => self::TYPE_MUSIC,
        'rng' => self::TYPE_MUSIC,
        'rns' => self::TYPE_MUSIC,
        'rol' => self::TYPE_MUSIC,
        'rsn' => self::TYPE_MUSIC,
        'rso' => self::TYPE_MUSIC,
        'rti' => self::TYPE_MUSIC,
        'rtm' => self::TYPE_MUSIC,
        'rts' => self::TYPE_MUSIC,
        'rvx' => self::TYPE_MUSIC,
        'rx2' => self::TYPE_MUSIC,
        's3i' => self::TYPE_MUSIC,
        's3m' => self::TYPE_MUSIC,
        's3z' => self::TYPE_MUSIC,
        'saf' => self::TYPE_MUSIC,
        'sam' => self::TYPE_MUSIC,
        'sap' => self::TYPE_MUSIC,
        'sb' => self::TYPE_MUSIC,
        'sbg' => self::TYPE_MUSIC,
        'sbi' => self::TYPE_MUSIC,
        'sbk' => self::TYPE_MUSIC,
        'sc2' => self::TYPE_MUSIC,
        'sd' => self::TYPE_MUSIC,
        'sd2' => self::TYPE_MUSIC,
        'sd2f' => self::TYPE_MUSIC,
        'sdat' => self::TYPE_MUSIC,
        'sdii' => self::TYPE_MUSIC,
        'sds' => self::TYPE_MUSIC,
        'sdt' => self::TYPE_MUSIC,
        'sdx' => self::TYPE_MUSIC,
        'seg' => self::TYPE_MUSIC,
        'ses' => self::TYPE_MUSIC,
        'sesx' => self::TYPE_MUSIC,
        'sf' => self::TYPE_MUSIC,
        'sf2' => self::TYPE_MUSIC,
        'sfap0' => self::TYPE_MUSIC,
        'sfk' => self::TYPE_MUSIC,
        'sfl' => self::TYPE_MUSIC,
        'sfs' => self::TYPE_MUSIC,
        'shn' => self::TYPE_MUSIC,
        'sib' => self::TYPE_MUSIC,
        'sid' => self::TYPE_MUSIC,
        'sid' => self::TYPE_MUSIC,
        'smf' => self::TYPE_MUSIC,
        'smp' => self::TYPE_MUSIC,
        'snd' => self::TYPE_MUSIC,
        'snd' => self::TYPE_MUSIC,
        'snd' => self::TYPE_MUSIC,
        'sng' => self::TYPE_MUSIC,
        'sng' => self::TYPE_MUSIC,
        'sou' => self::TYPE_MUSIC,
        'sppack' => self::TYPE_MUSIC,
        'sprg' => self::TYPE_MUSIC,
        'spx' => self::TYPE_MUSIC,
        'sseq' => self::TYPE_MUSIC,
        'sseq' => self::TYPE_MUSIC,
        'ssnd' => self::TYPE_MUSIC,
        'stap' => self::TYPE_MUSIC,
        'stm' => self::TYPE_MUSIC,
        'stx' => self::TYPE_MUSIC,
        'sty' => self::TYPE_MUSIC,
        'sty' => self::TYPE_MUSIC,
        'svd' => self::TYPE_MUSIC,
        'svx' => self::TYPE_MUSIC,
        'sw' => self::TYPE_MUSIC,
        'swa' => self::TYPE_MUSIC,
        'syh' => self::TYPE_MUSIC,
        'syn' => self::TYPE_MUSIC,
        'syn' => self::TYPE_MUSIC,
        'syw' => self::TYPE_MUSIC,
        'syx' => self::TYPE_MUSIC,
        'tak' => self::TYPE_MUSIC,
        'tak' => self::TYPE_MUSIC,
        'td0' => self::TYPE_MUSIC,
        'tfmx' => self::TYPE_MUSIC,
        'tg' => self::TYPE_MUSIC,
        'thx' => self::TYPE_MUSIC,
        'toc' => self::TYPE_MUSIC,
        'tsp' => self::TYPE_MUSIC,
        'tta' => self::TYPE_MUSIC,
        'tun' => self::TYPE_MUSIC,
        'txw' => self::TYPE_MUSIC,
        'u' => self::TYPE_MUSIC,
        'uax' => self::TYPE_MUSIC,
        'ub' => self::TYPE_MUSIC,
        'ulaw' => self::TYPE_MUSIC,
        'ult' => self::TYPE_MUSIC,
        'ulw' => self::TYPE_MUSIC,
        'uni' => self::TYPE_MUSIC,
        'usf' => self::TYPE_MUSIC,
        'usflib' => self::TYPE_MUSIC,
        'uw' => self::TYPE_MUSIC,
        'uwf' => self::TYPE_MUSIC,
        'vag' => self::TYPE_MUSIC,
        'val' => self::TYPE_MUSIC,
        'vap' => self::TYPE_MUSIC,
        'vb' => self::TYPE_MUSIC,
        'vc3' => self::TYPE_MUSIC,
        'vdj' => self::TYPE_MUSIC,
        'vgm' => self::TYPE_MUSIC,
        'vgz' => self::TYPE_MUSIC,
        'vmd' => self::TYPE_MUSIC,
        'vmf' => self::TYPE_MUSIC,
        'vmf' => self::TYPE_MUSIC,
        'voc' => self::TYPE_MUSIC,
        'voi' => self::TYPE_MUSIC,
        'vox' => self::TYPE_MUSIC,
        'vpm' => self::TYPE_MUSIC,
        'vqf' => self::TYPE_MUSIC,
        'vrf' => self::TYPE_MUSIC,
        'vtx' => self::TYPE_MUSIC,
        'vyf' => self::TYPE_MUSIC,
        'w01' => self::TYPE_MUSIC,
        'w64' => self::TYPE_MUSIC,
        'wav' => self::TYPE_MUSIC,
        'wav' => self::TYPE_MUSIC,
        'wave' => self::TYPE_MUSIC,
        'wax' => self::TYPE_MUSIC,
        'wfb' => self::TYPE_MUSIC,
        'wfd' => self::TYPE_MUSIC,
        'wfp' => self::TYPE_MUSIC,
        'wma' => self::TYPE_MUSIC,
        'wow' => self::TYPE_MUSIC,
        'wpk' => self::TYPE_MUSIC,
        'wpp' => self::TYPE_MUSIC,
        'wproj' => self::TYPE_MUSIC,
        'wrk' => self::TYPE_MUSIC,
        'wtpl' => self::TYPE_MUSIC,
        'wtpt' => self::TYPE_MUSIC,
        'wus' => self::TYPE_MUSIC,
        'wut' => self::TYPE_MUSIC,
        'wv' => self::TYPE_MUSIC,
        'wvc' => self::TYPE_MUSIC,
        'wve' => self::TYPE_MUSIC,
        'wwu' => self::TYPE_MUSIC,
        'wyz' => self::TYPE_MUSIC,
        'xa' => self::TYPE_MUSIC,
        'xa' => self::TYPE_MUSIC,
        'xfs' => self::TYPE_MUSIC,
        'xi' => self::TYPE_MUSIC,
        'xm' => self::TYPE_MUSIC,
        'xmf' => self::TYPE_MUSIC,
        'xmi' => self::TYPE_MUSIC,
        'xmz' => self::TYPE_MUSIC,
        'xp' => self::TYPE_MUSIC,
        'xrns' => self::TYPE_MUSIC,
        'xsb' => self::TYPE_MUSIC,
        'xspf' => self::TYPE_MUSIC,
        'xt' => self::TYPE_MUSIC,
        'xwb' => self::TYPE_MUSIC,
        'ym' => self::TYPE_MUSIC,
        'zpa' => self::TYPE_MUSIC,
        'zpl' => self::TYPE_MUSIC,
        'zvd' => self::TYPE_MUSIC,
        'zvr' => self::TYPE_MUSIC,
        '0' => self::TYPE_ARCHIVE,
        '000' => self::TYPE_ARCHIVE,
        '7z' => self::TYPE_ARCHIVE,
        'a00' => self::TYPE_ARCHIVE,
        'a01' => self::TYPE_ARCHIVE,
        'a02' => self::TYPE_ARCHIVE,
        'ace' => self::TYPE_ARCHIVE,
        'ain' => self::TYPE_ARCHIVE,
        'alz' => self::TYPE_ARCHIVE,
        'apz' => self::TYPE_ARCHIVE,
        'ar' => self::TYPE_ARCHIVE,
        'arc' => self::TYPE_ARCHIVE,
        'arh' => self::TYPE_ARCHIVE,
        'ari' => self::TYPE_ARCHIVE,
        'arj' => self::TYPE_ARCHIVE,
        'ark' => self::TYPE_ARCHIVE,
        'b1' => self::TYPE_ARCHIVE,
        'b64' => self::TYPE_ARCHIVE,
        'ba' => self::TYPE_ARCHIVE,
        'bh' => self::TYPE_ARCHIVE,
        'boo' => self::TYPE_ARCHIVE,
        'bz' => self::TYPE_ARCHIVE,
        'bz2' => self::TYPE_ARCHIVE,
        'bza' => self::TYPE_ARCHIVE,
        'bzip' => self::TYPE_ARCHIVE,
        'bzip2' => self::TYPE_ARCHIVE,
        'c00' => self::TYPE_ARCHIVE,
        'c01' => self::TYPE_ARCHIVE,
        'c02' => self::TYPE_ARCHIVE,
        'c10' => self::TYPE_ARCHIVE,
        'car' => self::TYPE_ARCHIVE,
        'cb7' => self::TYPE_ARCHIVE,
        'cba' => self::TYPE_ARCHIVE,
        'cbr' => self::TYPE_ARCHIVE,
        'cbt' => self::TYPE_ARCHIVE,
        'cbz' => self::TYPE_ARCHIVE,
        'cp9' => self::TYPE_ARCHIVE,
        'cpgz' => self::TYPE_ARCHIVE,
        'cpt' => self::TYPE_ARCHIVE,
        'czip' => self::TYPE_ARCHIVE,
        'dar' => self::TYPE_ARCHIVE,
        'dd' => self::TYPE_ARCHIVE,
        'deb' => self::TYPE_ARCHIVE,
        'dgc' => self::TYPE_ARCHIVE,
        'dist' => self::TYPE_ARCHIVE,
        'dl_' => self::TYPE_ARCHIVE,
        'dz' => self::TYPE_ARCHIVE,
        'ecs' => self::TYPE_ARCHIVE,
        'efw' => self::TYPE_ARCHIVE,
        'epi' => self::TYPE_ARCHIVE,
        'f' => self::TYPE_ARCHIVE,
        'fdp' => self::TYPE_ARCHIVE,
        'gca' => self::TYPE_ARCHIVE,
        'gz' => self::TYPE_ARCHIVE,
        'gz2' => self::TYPE_ARCHIVE,
        'gza' => self::TYPE_ARCHIVE,
        'gzi' => self::TYPE_ARCHIVE,
        'gzip' => self::TYPE_ARCHIVE,
        'ha' => self::TYPE_ARCHIVE,
        'hbc' => self::TYPE_ARCHIVE,
        'hbc2' => self::TYPE_ARCHIVE,
        'hbe' => self::TYPE_ARCHIVE,
        'hki' => self::TYPE_ARCHIVE,
        'hki1' => self::TYPE_ARCHIVE,
        'hki2' => self::TYPE_ARCHIVE,
        'hki3' => self::TYPE_ARCHIVE,
        'hpk' => self::TYPE_ARCHIVE,
        'hyp' => self::TYPE_ARCHIVE,
        'ice' => self::TYPE_ARCHIVE,
        'ipg' => self::TYPE_ARCHIVE,
        'ipk' => self::TYPE_ARCHIVE,
        'ish' => self::TYPE_ARCHIVE,
        'ita' => self::TYPE_ARCHIVE,
        'j' => self::TYPE_ARCHIVE,
        'jar.pack' => self::TYPE_ARCHIVE,
        'jgz' => self::TYPE_ARCHIVE,
        'jic' => self::TYPE_ARCHIVE,
        'kgb' => self::TYPE_ARCHIVE,
        'kz' => self::TYPE_ARCHIVE,
        'lbr' => self::TYPE_ARCHIVE,
        'lemon' => self::TYPE_ARCHIVE,
        'lha' => self::TYPE_ARCHIVE,
        'lnx' => self::TYPE_ARCHIVE,
        'lqr' => self::TYPE_ARCHIVE,
        'lz' => self::TYPE_ARCHIVE,
        'lzh' => self::TYPE_ARCHIVE,
        'lzm' => self::TYPE_ARCHIVE,
        'lzma' => self::TYPE_ARCHIVE,
        'lzo' => self::TYPE_ARCHIVE,
        'lzx' => self::TYPE_ARCHIVE,
        'md' => self::TYPE_ARCHIVE,
        'mint' => self::TYPE_ARCHIVE,
        'mou' => self::TYPE_ARCHIVE,
        'mpkg' => self::TYPE_ARCHIVE,
        'mzp' => self::TYPE_ARCHIVE,
        'mzp' => self::TYPE_ARCHIVE,
        'oar' => self::TYPE_ARCHIVE,
        'oz' => self::TYPE_ARCHIVE,
        'pack.gz' => self::TYPE_ARCHIVE,
        'package' => self::TYPE_ARCHIVE,
        'pae' => self::TYPE_ARCHIVE,
        'pak' => self::TYPE_ARCHIVE,
        'paq6' => self::TYPE_ARCHIVE,
        'paq7' => self::TYPE_ARCHIVE,
        'paq8' => self::TYPE_ARCHIVE,
        'paq8f' => self::TYPE_ARCHIVE,
        'par' => self::TYPE_ARCHIVE,
        'par2' => self::TYPE_ARCHIVE,
        'pax' => self::TYPE_ARCHIVE,
        'pbi' => self::TYPE_ARCHIVE,
        'pcv' => self::TYPE_ARCHIVE,
        'pea' => self::TYPE_ARCHIVE,
        'pet' => self::TYPE_ARCHIVE,
        'pf' => self::TYPE_ARCHIVE,
        'pim' => self::TYPE_ARCHIVE,
        'pit' => self::TYPE_ARCHIVE,
        'piz' => self::TYPE_ARCHIVE,
        'pkg' => self::TYPE_ARCHIVE,
        'pup' => self::TYPE_ARCHIVE,
        'pup' => self::TYPE_ARCHIVE,
        'puz' => self::TYPE_ARCHIVE,
        'pwa' => self::TYPE_ARCHIVE,
        'qda' => self::TYPE_ARCHIVE,
        'r0' => self::TYPE_ARCHIVE,
        'r00' => self::TYPE_ARCHIVE,
        'r01' => self::TYPE_ARCHIVE,
        'r02' => self::TYPE_ARCHIVE,
        'r03' => self::TYPE_ARCHIVE,
        'r1' => self::TYPE_ARCHIVE,
        'r2' => self::TYPE_ARCHIVE,
        'r21' => self::TYPE_ARCHIVE,
        'r30' => self::TYPE_ARCHIVE,
        'rar' => self::TYPE_ARCHIVE,
        'rev' => self::TYPE_ARCHIVE,
        'rk' => self::TYPE_ARCHIVE,
        'rnc' => self::TYPE_ARCHIVE,
        'rp9' => self::TYPE_ARCHIVE,
        'rpm' => self::TYPE_ARCHIVE,
        'rte' => self::TYPE_ARCHIVE,
        'rz' => self::TYPE_ARCHIVE,
        'rzs' => self::TYPE_ARCHIVE,
        's00' => self::TYPE_ARCHIVE,
        's01' => self::TYPE_ARCHIVE,
        's02' => self::TYPE_ARCHIVE,
        's7z' => self::TYPE_ARCHIVE,
        'sar' => self::TYPE_ARCHIVE,
        'sbx' => self::TYPE_ARCHIVE,
        'sdc' => self::TYPE_ARCHIVE,
        'sdn' => self::TYPE_ARCHIVE,
        'sea' => self::TYPE_ARCHIVE,
        'sen' => self::TYPE_ARCHIVE,
        'sfs' => self::TYPE_ARCHIVE,
        'sfx' => self::TYPE_ARCHIVE,
        'sh' => self::TYPE_ARCHIVE,
        'shar' => self::TYPE_ARCHIVE,
        'shk' => self::TYPE_ARCHIVE,
        'shr' => self::TYPE_ARCHIVE,
        'sit' => self::TYPE_ARCHIVE,
        'sitx' => self::TYPE_ARCHIVE,
        'spt' => self::TYPE_ARCHIVE,
        'sqx' => self::TYPE_ARCHIVE,
        'srep' => self::TYPE_ARCHIVE,
        'sy_' => self::TYPE_ARCHIVE,
        'tar.gz' => self::TYPE_ARCHIVE,
        'tar.gz2' => self::TYPE_ARCHIVE,
        'tar.lzma' => self::TYPE_ARCHIVE,
        'tar.xz' => self::TYPE_ARCHIVE,
        'taz' => self::TYPE_ARCHIVE,
        'tbz' => self::TYPE_ARCHIVE,
        'tbz2' => self::TYPE_ARCHIVE,
        'tg' => self::TYPE_ARCHIVE,
        'tgz' => self::TYPE_ARCHIVE,
        'tlz' => self::TYPE_ARCHIVE,
        'tlzma' => self::TYPE_ARCHIVE,
        'txz' => self::TYPE_ARCHIVE,
        'tz' => self::TYPE_ARCHIVE,
        'uc2' => self::TYPE_ARCHIVE,
        'ufs.uzip' => self::TYPE_ARCHIVE,
        'uha' => self::TYPE_ARCHIVE,
        'uzip' => self::TYPE_ARCHIVE,
        'vem' => self::TYPE_ARCHIVE,
        'vsi' => self::TYPE_ARCHIVE,
        'war' => self::TYPE_ARCHIVE,
        'wot' => self::TYPE_ARCHIVE,
        'xef' => self::TYPE_ARCHIVE,
        'xez' => self::TYPE_ARCHIVE,
        'xmcdz' => self::TYPE_ARCHIVE,
        'xx' => self::TYPE_ARCHIVE,
        'xz' => self::TYPE_ARCHIVE,
        'y' => self::TYPE_ARCHIVE,
        'yz' => self::TYPE_ARCHIVE,
        'yz1' => self::TYPE_ARCHIVE,
        'z' => self::TYPE_ARCHIVE,
        'z01' => self::TYPE_ARCHIVE,
        'z02' => self::TYPE_ARCHIVE,
        'z03' => self::TYPE_ARCHIVE,
        'z04' => self::TYPE_ARCHIVE,
        'zap' => self::TYPE_ARCHIVE,
        'zfsendtotarget' => self::TYPE_ARCHIVE,
        'zi' => self::TYPE_ARCHIVE,
        'zip' => self::TYPE_ARCHIVE,
        'zipx' => self::TYPE_ARCHIVE,
        'zix' => self::TYPE_ARCHIVE,
        'zl' => self::TYPE_ARCHIVE,
        'zoo' => self::TYPE_ARCHIVE,
        'zpi' => self::TYPE_ARCHIVE,
        'zz' => self::TYPE_ARCHIVE,
        'pdf' => self::TYPE_PDF,
        'dae' => self::TYPE_MODEL,
        'obj' => self::TYPE_MODEL,
        'fbx' => self::TYPE_MODEL,
        'spx' => self::TYPE_MODEL,
        '3ds' => self::TYPE_MODEL,
        '3mf' => self::TYPE_MODEL,
        'blend' => self::TYPE_MODEL,
        'awd' => self::TYPE_MODEL,
        'ply' => self::TYPE_MODEL,
        'pcd' => self::TYPE_MODEL,
        'stl' => self::TYPE_MODEL,
        'skp' => self::TYPE_MODEL,
    ];

    /**
     * Unique identifier
     *
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Id of the assigned album
     *
     * @var int
     * @ORM\Column(name="albumID", type="integer", nullable=false)
     */
    private $albumId;

    /**
     * Name of the media, also used as a file name
     *
     * @var string
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Description for the media.
     *
     * @var string
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * Path of the uploaded file.
     *
     * @var string
     * @ORM\Column(name="path", type="string", length=255, nullable=false)
     */
    private $path;

    /**
     * Flag for the media type.
     *
     * @var string
     * @ORM\Column(name="type", type="string", length=50, nullable=false)
     */
    private $type;

    /**
     * Extension of the uploaded file
     *
     * @var string
     * @ORM\Column(name="extension", type="string", length=20, nullable=false)
     */
    private $extension;

    /**
     * Id of the user, who uploaded the file.
     *
     * @var int
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $userId;

    /**
     * Creation date of the media
     *
     * @var \DateTime
     * @ORM\Column(name="created", type="date", nullable=false)
     */
    private $created;

    /**
     * Internal container for the uploaded file.
     *
     * @var UploadedFile
     */
    private $file;

    /**
     * Filesize of the file in bytes
     *
     * @var int
     * @ORM\Column(name="file_size", type="integer", nullable=false)
     */
    private $fileSize;

    /**
     * Width of the file in px if it's an image
     *
     * @var int
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * Height of the file in px if it's an image
     *
     * @var int
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * Assigned album association. Is automatically loaded when the standard functions "find" ... be used,
     * or if the Query Builder is specified with the association.
     *
     * @var \Shopware\Models\Media\Album
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Media\Album", inversedBy="media")
     * @ORM\JoinColumn(name="albumID", referencedColumnName="id")
     */
    private $album;

    /**
     * Contains the thumbnails paths.
     * Contains all created thumbnails
     *
     * @var array
     */
    private $thumbnails;

    /**
     * Contains the high dpi thumbnails paths.
     *
     * @var array
     */
    private $highDpiThumbnails;

    /****************************************************************
     *                  Property Getter & Setter                    *
     ****************************************************************/

    /**
     * @return array
     */
    public function getTypeMapping()
    {
        return $this->typeMapping;
    }

    /**
     * Returns the identifier "id"
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the assigned album.
     *
     * @param int $albumId
     *
     * @return Media
     */
    public function setAlbumId($albumId)
    {
        $this->albumId = $albumId;

        return $this;
    }

    /**
     * Returns the id of the assigned album.
     *
     * @return int
     */
    public function getAlbumId()
    {
        return $this->albumId;
    }

    /**
     * Sets the name of the media, also used as file name
     *
     * @param string $name
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setName($name)
    {
        $this->name = $this->removeSpecialCharacters($name);

        return $this;
    }

    /**
     * Returns the name of the media, also used as file name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the description of the media.
     *
     * @param string $description
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns the media description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the file path of the media.
     *
     * @param string $path
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns the file path of the media
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the internal type of the media.
     *
     * @param string $type
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the media type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the file extension.
     *
     * @param string $extension
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Returns the file extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Sets the id of the user, who uploaded the file.
     *
     * @param int $userId
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Returns the id of the user, who uploaded the file.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the creation date of the media.
     *
     * @param \DateTime $created
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Returns the creation date of the media.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets the memory size of the file.
     *
     * @param float $fileSize
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Returns the filesize of the file in bytes.
     *
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Returns the filesize of the file in human readable format
     *
     * @return string
     */
    public function getFormattedFileSize()
    {
        $size = $this->fileSize;

        if ($size < 1024) {
            $filesize = $size . ' bytes';
        } elseif ($size >= 1024 && $size < 1048576) {
            $filesize = round($size / 1024, 2) . ' KB';
        } elseif ($size >= 1048576) {
            $filesize = round($size / 1048576, 2) . ' MB';
        }

        return $filesize;
    }

    /**
     * Returns the instance of the assigned album
     *
     * @return \Shopware\Models\Media\Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Sets the assigned album.
     *
     * @param  $album
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setAlbum(Album $album)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Returns the file
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Setter method for the file property. If the file is set, the file information will be extracted
     * and set into the internal properties.
     *
     * @param  $file \Symfony\Component\HttpFoundation\File\File
     *
     * @return \Shopware\Models\Media\Media
     */
    public function setFile(\Symfony\Component\HttpFoundation\File\File $file)
    {
        $this->file = $file;
        $this->setFileInfo();

        return $this;
    }

    /**
     * Returns the thumbnail paths in an array
     *
     * @return array
     */
    public function getThumbnails()
    {
        if (empty($this->thumbnails)) {
            $this->thumbnails = $this->loadThumbnails();
        }

        return $this->thumbnails;
    }

    /**
     * Returns the high dpi thumbnail paths in an array
     *
     * @return array
     */
    public function getHighDpiThumbnails()
    {
        if (empty($this->highDpiThumbnails)) {
            $this->highDpiThumbnails = $this->loadThumbnails(true);
        }

        return $this->highDpiThumbnails;
    }

    /**
     * Returns the thumbnail paths of already generated thumbnails
     *
     * @return array
     */
    public function getCreatedThumbnails()
    {
        return $this->thumbnails;
    }

    /****************************************************************
     *                  Lifecycle Callbacks                         *
     ****************************************************************/

    /**
     * Moves the uploaded file into the correctly media directory,
     * creates the default thumbnails for image media to display the
     * media in the media manager and creates the thumbnails for the
     * configured album thumbnail sizes.
     *
     * @ORM\PrePersist
     */
    public function onSave()
    {
        //Upload file
        $this->uploadFile();
    }

    /**
     * Checks if the name changed, if this is the case, the uploaded file
     * has to be renamed.
     * Removes the thumbnail files if the album or the name changed.
     * Creates the default and album thumbnails if the name or the album changed.
     *
     * @ORM\PostUpdate
     */
    public function onUpdate()
    {
        //returns a change set for the model, which contains all changed properties with the old and new value.
        $changeSet = 🦄()->Models()->getUnitOfWork()->getEntityChangeSet($this);

        $isNameChanged = isset($changeSet['name']) && $changeSet['name'][0] !== $changeSet['name'][1];
        $isAlbumChanged = isset($changeSet['albumId']) && $changeSet['albumId'][0] !== $changeSet['albumId'][1];

        //name changed || album changed?
        if ($isNameChanged || $isAlbumChanged) {
            //to remove the old thumbnails, use the old name.
            $name = (isset($changeSet['name'])) ? $changeSet['name'][0] : $this->name;
            $name = $this->removeSpecialCharacters($name);
            $name = $name . '.' . $this->extension;

            //to remove the old album thumbnails, use the old album
            $album = (isset($changeSet['album'])) ? $changeSet['album'][0] : $this->album;

            if ($isNameChanged) {
                //remove default thumbnails
                $this->removeDefaultThumbnails($name);

                //create default thumbnails
                $this->createDefaultThumbnails();
            }

            //remove the configured album thumbnail files
            $settings = $album->getSettings();
            if ($settings !== null) {
                $this->removeAlbumThumbnails($settings->getThumbnailSize(), $name);
            }

            $this->updateAssociations();

            //create album thumbnails
            $this->createAlbumThumbnails($this->album);
        }

        //name changed? Then rename the file and set the new path
        if ($isNameChanged) {
            $mediaService = 🦄()->Container()->get('shopware_media.media_service');
            $newName = $this->getFileName();
            $newPath = $this->getUploadDir() . $newName;

            //rename the file
            $mediaService->rename($this->path, $newPath);

            $newPath = str_replace(🦄()->DocPath(), '', $newPath);

            //set the new path to save it.
            $this->path = $newPath;
        }
    }

    /**
     * Model event function, which called when the model is loaded.
     *
     * @ORM\PostLoad
     */
    public function onLoad()
    {
        $this->thumbnails = $this->loadThumbnails();
    }

    /**
     * Removes the media files from the file system
     *
     * @ORM\PostRemove
     */
    public function onRemove()
    {
        $mediaService = 🦄()->Container()->get('shopware_media.media_service');
        //check if file exist and remove it
        if ($mediaService->has($this->path)) {
            $mediaService->delete($this->path);
        }

        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        $thumbnailSizes = $this->getAllThumbnailSizes();

        $this->removeDefaultThumbnails($this->getFileName());
        $this->removeAlbumThumbnails($thumbnailSizes, $this->getFileName());
    }

    /****************************************************************
     *                  Global functions                            *
     ****************************************************************/

    /**
     * Creates the thumbnail files in the different sizes which configured in the album settings.
     *
     * @param \Shopware\Models\Media\Album $album
     */
    public function createAlbumThumbnails(Album $album)
    {
        //is image media?
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        //Check if the album has loaded correctly and should be created for the album thumbnails?
        if ($album === null || $album->getSettings() === null || !$album->getSettings()->getCreateThumbnails()) {
            return;
        }

        $defaultSizes = $this->getDefaultThumbnails();
        $defaultSize = implode('x', $defaultSizes[0]);
        //load the configured album thumbnail sizes
        $sizes = $album->getSettings()->getThumbnailSize();
        $sizes[] = $defaultSize;

        //iterate the sizes and create the thumbnails
        foreach ($sizes as $size) {
            //split the width and height (example: $size = 70x70)
            $data = explode('x', $size);

            // To avoid any confusing, we're mapping the index based to an association based array and remove the index based elements.
            $data['width'] = $data[0];
            $data['height'] = $data[1];
            unset($data[0]);
            unset($data[1]);

            //continue if configured size is not numeric
            if (!is_numeric($data['width'])) {
                continue;
            }
            //if no height configured, set 0
            $data['height'] = (isset($data['height'])) ? $data['height'] : 0;

            //create thumbnail with the configured size
            $this->createThumbnail((int) $data['width'], (int) $data['height']);
        }
    }

    /**
     * Removes the configured album thumbnails for the passed album instance and with the
     * passed file name. The file name have to be passed, because on update the internal
     * file name property is already changed to the new name.
     *
     * @param   $thumbnailSizes
     * @param   $fileName
     */
    public function removeAlbumThumbnails($thumbnailSizes, $fileName)
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }
        if ($thumbnailSizes === null || empty($thumbnailSizes)) {
            return;
        }

        $mediaService = 🦄()->Container()->get('shopware_media.media_service');

        foreach ($thumbnailSizes as $size) {
            if (strpos($size, 'x') === false) {
                $size = $size . 'x' . $size;
            }
            $names = $this->getThumbnailNames($size, $fileName);

            if ($mediaService->has($names['jpg'])) {
                $mediaService->delete($names['jpg']);
            }

            if ($mediaService->has($names['jpgHD'])) {
                $mediaService->delete($names['jpgHD']);
            }

            if ($mediaService->has($names['original'])) {
                $mediaService->delete($names['original']);
            }

            if ($mediaService->has($names['originalHD'])) {
                $mediaService->delete($names['originalHD']);
            }
        }
    }

    /**
     * Returns the converted file name.
     *
     * @return bool|string
     */
    public function getFileName()
    {
        if ($this->name !== '') {
            return $this->removeSpecialCharacters($this->name) . '.' . $this->extension;
        }
            // do whatever you want to generate a unique name
            return uniqid() . '.' . $this->extension;
    }

    /**
     * Loads the thumbnails paths via the configured thumbnail sizes.
     *
     * @param bool $highDpi - If true, loads high dpi thumbnails instead
     *
     * @return array
     */
    public function loadThumbnails($highDpi = false)
    {
        $thumbnails = $this->getThumbnailFilePaths($highDpi);
        $mediaService = 🦄()->Container()->get('shopware_media.media_service');

        if (!$mediaService->has($this->getPath())) {
            return $thumbnails;
        }

        foreach ($thumbnails as $size => $thumbnail) {
            $size = explode('x', $size);

            if (!$mediaService->has($thumbnail)) {
                try {
                    $this->createThumbnail($size[0], $size[1]);
                } catch (\Exception $e) {
                    // Ignore for now
                    // Exception might be thrown when thumbnails can not
                    // be generated due to invalid image files
                }
            }
        }

        return $thumbnails;
    }

    /**
     * Returns an array of all thumbnail paths the media object can have
     *
     * @param bool $highDpi - If true, returns the file path for the high dpi thumbnails instead
     *
     * @return array
     */
    public function getThumbnailFilePaths($highDpi = false)
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return [];
        }
        $sizes = [];

        //concat default sizes
        foreach ($this->defaultThumbnails as $size) {
            if (count($size) === 1) {
                $sizes[] = $size . 'x' . $size;
            } else {
                $sizes[] = $size[0] . 'x' . $size[1];
            }
        }

        //Check if the album has loaded correctly.
        if ($this->album !== null && $this->album->getSettings() !== null && $this->album->getSettings()->getCreateThumbnails() === 1) {
            $sizes = array_merge($this->album->getSettings()->getThumbnailSize(), $sizes);
            $sizes = array_unique($sizes);
        }
        $thumbnails = [];
        $suffix = $highDpi ? '@2x' : '';

        //iterate thumbnail sizes
        foreach ($sizes as $size) {
            if (strpos($size, 'x') === false) {
                $size = $size . 'x' . $size;
            }

            $fileName = str_replace(
                '.' . $this->extension,
                '_' . $size . $suffix . '.' . $this->extension,
                $this->getFileName()
            );

            $path = $this->getThumbnailDir() . $fileName;
            $path = str_replace(🦄()->DocPath(), '', $path);
            if (DIRECTORY_SEPARATOR !== '/') {
                $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
            }
            $thumbnails[$size] = $path;
        }

        return $thumbnails;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getDefaultThumbnails()
    {
        return $this->defaultThumbnails;
    }

    public function setDefaultThumbnails($defaultThumbnails)
    {
        $this->defaultThumbnails = $defaultThumbnails;
    }

    /**
     * @return \Shopware\Models\Attribute\Media
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Media|array|null $attribute
     *
     * @return \Shopware\Models\Attribute\Media
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Media', 'attribute', 'media');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    public function removeThumbnails()
    {
        $thumbnailSizes = $this->getAllThumbnailSizes();

        $this->removeDefaultThumbnails($this->getFileName());
        $this->removeAlbumThumbnails($thumbnailSizes, $this->getFileName());
    }

    /**
     * @param int $newAlbumId
     */
    private function createThumbnailsForMovedMedia($newAlbumId)
    {
        $albumRepository = 🦄()->Container()->get('models')->getRepository(Album::class);

        /** @var Album $album */
        $album = $albumRepository->find($newAlbumId);
        if ($album) {
            $this->createAlbumThumbnails($album);
        }
    }

    /**
     * Internal helper function which updates all associated data which has the image path as own property.
     *
     * @internal param $name
     */
    private function updateAssociations()
    {
        /** @var $article \Shopware\Models\Article\Image */
        foreach ($this->articles as $article) {
            $article->setPath($this->getName());
            🦄()->Models()->persist($article);
        }
        🦄()->Models()->flush();
    }

    /****************************************************************
     *                  Internal functions                          *
     ****************************************************************/

    /**
     * Moves the uploaded file to the correctly directory.
     *
     * @return bool
     */
    private function uploadFile()
    {
        $mediaService = 🦄()->Container()->get('shopware_media.media_service');

        //move the file to the upload directory
        if ($this->file !== null) {
            //file already exists?
            if ($mediaService->has($this->getPath())) {
                $this->name = $this->name . uniqid();
                // Path in setFileInfo is set, before the file gets a unique ID here
                // Therefore the path is updated here SW-2889
                $this->path = str_replace(🦄()->DocPath(), '', $this->getUploadDir() . $this->getFileName());

                /*
                 * SW-3805 - Hotfix for windows path's
                 */
                $this->path = str_replace('\\', '/', $this->path);
            }

            $mediaService->write($this->path, file_get_contents($this->file->getRealPath()));
            unlink($this->file->getPathname());
        }

        return true;
    }

    /**
     * Creates the default thumbnails 70x70 and 153x153 to display the images
     * in the media manager listing.
     */
    private function createDefaultThumbnails()
    {
        //create only thumbnails for image media
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        /** @var \Shopware\Components\Thumbnail\Manager $generator */
        $generator = 🦄()->Container()->get('thumbnail_manager');

        $generator->createMediaThumbnail($this, $this->defaultThumbnails, true);
    }

    /**
     * Removes the default thumbnail files. The file name have to be passed, because on update the internal
     * file name property is already changed to the new name.
     *
     * @param $fileName
     */
    private function removeDefaultThumbnails($fileName)
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        $mediaService = 🦄()->Container()->get('shopware_media.media_service');

        foreach ($this->defaultThumbnails as $size) {
            if (count($size) === 1) {
                $sizeString = $size . 'x' . $size;
            } else {
                $sizeString = $size[0] . 'x' . $size[1];
            }
            $names = $this->getThumbnailNames($sizeString, $fileName);

            if ($mediaService->has($names['jpg'])) {
                $mediaService->delete($names['jpg']);
            }

            if ($mediaService->has($names['jpgHD'])) {
                $mediaService->delete($names['jpgHD']);
            }

            if ($mediaService->has($names['original'])) {
                $mediaService->delete($names['original']);
            }

            if ($mediaService->has($names['originalHD'])) {
                $mediaService->delete($names['originalHD']);
            }
        }
    }

    /**
     * Returns the directory to upload
     *
     * @return string
     */
    private function getUploadDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return 🦄()->DocPath('media_' . strtolower($this->type));
    }

    /**
     * Returns the directory of the thumbnail files.
     *
     * @return string
     */
    private function getThumbnailDir()
    {
        $mediaService = 🦄()->Container()->get('shopware_media.media_service');
        $path = $this->getUploadDir() . 'thumbnail' . DIRECTORY_SEPARATOR;
        $path = $mediaService->normalize($path);

        return $path;
    }

    /**
     * Create a thumbnail file for the internal file with the passed width and height.
     *
     * @param $width
     * @param $height
     *
     * @return bool
     */
    private function createThumbnail($width, $height)
    {
        //create only thumbnails for image media
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        /** @var \Shopware\Components\Thumbnail\Manager $manager */
        $manager = 🦄()->Container()->get('thumbnail_manager');

        $newSize = [
            'width' => $width,
            'height' => $height,
        ];

        $manager->createMediaThumbnail($this, [$newSize], true);
    }

    /**
     * Create the new names for the jpg file and the file with the original extension
     * Also returns high dpi paths
     *
     * @param $suffix
     * @param $fileName
     *
     * @return array
     */
    private function getThumbnailNames($suffix, $fileName)
    {
        $jpgName = str_replace('.' . $this->extension, '_' . $suffix . '.jpg', $fileName);
        $jpgHDName = str_replace('.' . $this->extension, '_' . $suffix . '@2x.jpg', $fileName);
        $originalName = str_replace('.' . $this->extension, '_' . $suffix . '.' . $this->extension, $fileName);
        $originalHDName = str_replace('.' . $this->extension, '_' . $suffix . '@2x.' . $this->extension, $fileName);

        return [
            'jpg' => $this->getThumbnailDir() . $jpgName,
            'jpgHD' => $this->getThumbnailDir() . $jpgHDName,
            'original' => $this->getThumbnailDir() . $originalName,
            'originalHD' => $this->getThumbnailDir() . $originalHDName,
        ];
    }

    /**
     * Calculate image proportion and set the new resolution
     *
     * @param $originalSize
     * @param $width
     * @param $height
     *
     * @return array
     */
    private function calculateThumbnailSize(array $originalSize, $width, $height)
    {
        // Source image size
        $srcWidth = $originalSize[0];
        $srcHeight = $originalSize[1];

        // Calculate the scale factor
        if ($width === 0) {
            $factor = $height / $srcHeight;
        } elseif ($height === 0) {
            $factor = $width / $srcWidth;
        } else {
            $factor = min($width / $srcWidth, $height / $srcHeight);
        }

        // Get the destination size
        $dstWidth = round($srcWidth * $factor);
        $dstHeight = round($srcHeight * $factor);

        return [
            'width' => $dstWidth,
            'height' => $dstHeight,
            'proportion' => $factor,
        ];
    }

    /**
     * Creates the image resource
     *
     * @return bool|resource
     */
    private function createFileImage()
    {
        switch (strtolower($this->extension)) {
            case 'gif':
                $image = imagecreatefromgif($this->path);
                break;
            case 'png':
                $image = imagecreatefrompng($this->path);
                break;
            case 'jpg':
                $image = imagecreatefromjpeg($this->path);
                break;
            default:
                return false;
        }

        return $image;
    }

    /**
     * Extract the file information from the uploaded file, into the internal properties
     */
    private function setFileInfo()
    {
        if ($this->file === null) {
            return;
        }

        if ($this->file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            //load file information
            $fileInfo = pathinfo($this->file->getClientOriginalName());
            $extension = $fileInfo['extension'];
            $name = $fileInfo['filename'];
        } else {
            $extension = $this->file->guessExtension();
            $name = $this->file->getBasename();
        }

        // make sure that the name don't contains the file extension.
        $name = str_replace('.' . $extension, '', $name);
        if ($extension === 'jpeg') {
            $name = str_replace('.jpg', '', $name);
        }

        //set the file type using the type mapping
        if (array_key_exists(strtolower($extension), $this->typeMapping)) {
            $this->type = $this->typeMapping[strtolower($extension)];
        } else {
            $this->type = self::TYPE_UNKNOWN;
        }

        // The filesize in bytes.
        $this->fileSize = $this->file->getSize();
        $this->name = $this->removeSpecialCharacters($name);
        $this->extension = str_replace('jpeg', 'jpg', $extension);
        $this->path = str_replace(🦄()->DocPath(), '', $this->getUploadDir() . $this->getFileName());

        if (DIRECTORY_SEPARATOR !== '/') {
            $this->path = str_replace(DIRECTORY_SEPARATOR, '/', $this->path);
        }
    }

    private function removeSpecialCharacters($name)
    {
        $name = iconv('utf-8', 'ascii//translit', $name);
        $name = preg_replace('#[^A-z0-9\-_]#', '-', $name);
        $name = preg_replace('#-{2,}#', '-', $name);
        $name = trim($name, '-');

        return mb_substr($name, 0, 180);
    }

    /**
     * Searches all album settings for thumbnail sizes
     *
     * @return array
     */
    private function getAllThumbnailSizes()
    {
        $joinedSizes = 🦄()->Container()->get('dbal_connection')
            ->query('SELECT DISTINCT thumbnail_size FROM s_media_album_settings WHERE thumbnail_size != ""')
            ->fetchAll(\PDO::FETCH_COLUMN);

        $sizes = [];
        foreach ($joinedSizes as $sizeItem) {
            $explodedSizes = explode(';', $sizeItem);
            if (empty($explodedSizes)) {
                continue;
            }

            $sizes = array_merge($sizes, array_flip($explodedSizes));
        }

        return array_keys($sizes);
    }
}
