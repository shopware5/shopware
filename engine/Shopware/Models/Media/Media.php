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
use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

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
     * Contains the default thumbnail sizes which used for backend modules.
     * @var array
     */
    private $defaultThumbnails = array(
        array(140,140)
    );

    /**
     * All known file extensions and the mapped media type
     * @var array
     */
    private $typeMapping = array(
        '24b' => Media::TYPE_IMAGE,
        'ai' => Media::TYPE_IMAGE,
        'bmp' => Media::TYPE_IMAGE,
        'cdr' => Media::TYPE_IMAGE,
        'gif' => Media::TYPE_IMAGE,
        'iff' => Media::TYPE_IMAGE,
        'ilbm' => Media::TYPE_IMAGE,
        'jpeg' => Media::TYPE_IMAGE,
        'jpg' => Media::TYPE_IMAGE,
        'pcx' => Media::TYPE_IMAGE,
        'png' => Media::TYPE_IMAGE,
        'tif' => Media::TYPE_IMAGE,
        'tiff' => Media::TYPE_IMAGE,
        'eps' => Media::TYPE_IMAGE,
        'pbm' => Media::TYPE_IMAGE,
        'psd' => Media::TYPE_IMAGE,
        'wbm' => Media::TYPE_IMAGE,
        '264' => Media::TYPE_VIDEO,
        '3g2' => Media::TYPE_VIDEO,
        '3gp' => Media::TYPE_VIDEO,
        '3gp2' => Media::TYPE_VIDEO,
        '3gpp' => Media::TYPE_VIDEO,
        '3gpp2' => Media::TYPE_VIDEO,
        '3mm' => Media::TYPE_VIDEO,
        '3p2' => Media::TYPE_VIDEO,
        '60d' => Media::TYPE_VIDEO,
        '787' => Media::TYPE_VIDEO,
        'aaf' => Media::TYPE_VIDEO,
        'aep' => Media::TYPE_VIDEO,
        'aepx' => Media::TYPE_VIDEO,
        'aet' => Media::TYPE_VIDEO,
        'aetx' => Media::TYPE_VIDEO,
        'ajp' => Media::TYPE_VIDEO,
        'ale' => Media::TYPE_VIDEO,
        'amv' => Media::TYPE_VIDEO,
        'amx' => Media::TYPE_VIDEO,
        'anim' => Media::TYPE_VIDEO,
        'arf' => Media::TYPE_VIDEO,
        'asf' => Media::TYPE_VIDEO,
        'asx' => Media::TYPE_VIDEO,
        'avb' => Media::TYPE_VIDEO,
        'avd' => Media::TYPE_VIDEO,
        'avi' => Media::TYPE_VIDEO,
        'avp' => Media::TYPE_VIDEO,
        'avs' => Media::TYPE_VIDEO,
        'axm' => Media::TYPE_VIDEO,
        'bdm' => Media::TYPE_VIDEO,
        'bdmv' => Media::TYPE_VIDEO,
        'bik' => Media::TYPE_VIDEO,
        'bin' => Media::TYPE_VIDEO,
        'bix' => Media::TYPE_VIDEO,
        'bmk' => Media::TYPE_VIDEO,
        'bnp' => Media::TYPE_VIDEO,
        'box' => Media::TYPE_VIDEO,
        'bs4' => Media::TYPE_VIDEO,
        'bsf' => Media::TYPE_VIDEO,
        'byu' => Media::TYPE_VIDEO,
        'camproj' => Media::TYPE_VIDEO,
        'camrec' => Media::TYPE_VIDEO,
        'clpi' => Media::TYPE_VIDEO,
        'cmmp' => Media::TYPE_VIDEO,
        'cmmtpl' => Media::TYPE_VIDEO,
        'cmproj' => Media::TYPE_VIDEO,
        'cmrec' => Media::TYPE_VIDEO,
        'cpi' => Media::TYPE_VIDEO,
        'cst' => Media::TYPE_VIDEO,
        'cvc' => Media::TYPE_VIDEO,
        'd2v' => Media::TYPE_VIDEO,
        'd3v' => Media::TYPE_VIDEO,
        'dat' => Media::TYPE_VIDEO,
        'dav' => Media::TYPE_VIDEO,
        'dce' => Media::TYPE_VIDEO,
        'dck' => Media::TYPE_VIDEO,
        'ddat' => Media::TYPE_VIDEO,
        'dif' => Media::TYPE_VIDEO,
        'dir' => Media::TYPE_VIDEO,
        'divx' => Media::TYPE_VIDEO,
        'dlx' => Media::TYPE_VIDEO,
        'dmb' => Media::TYPE_VIDEO,
        'dmsd' => Media::TYPE_VIDEO,
        'dmsd3d' => Media::TYPE_VIDEO,
        'dmsm' => Media::TYPE_VIDEO,
        'dmsm3d' => Media::TYPE_VIDEO,
        'dmss' => Media::TYPE_VIDEO,
        'dnc' => Media::TYPE_VIDEO,
        'dpa' => Media::TYPE_VIDEO,
        'dpg' => Media::TYPE_VIDEO,
        'dream' => Media::TYPE_VIDEO,
        'dsy' => Media::TYPE_VIDEO,
        'dv' => Media::TYPE_VIDEO,
        'dv-avi' => Media::TYPE_VIDEO,
        'dv4' => Media::TYPE_VIDEO,
        'dvdmedia' => Media::TYPE_VIDEO,
        'dvr' => Media::TYPE_VIDEO,
        'dvr-ms' => Media::TYPE_VIDEO,
        'dvx' => Media::TYPE_VIDEO,
        'dxr' => Media::TYPE_VIDEO,
        'dzm' => Media::TYPE_VIDEO,
        'dzp' => Media::TYPE_VIDEO,
        'dzt' => Media::TYPE_VIDEO,
        'edl' => Media::TYPE_VIDEO,
        'evo' => Media::TYPE_VIDEO,
        'eye' => Media::TYPE_VIDEO,
        'f4p' => Media::TYPE_VIDEO,
        'f4v' => Media::TYPE_VIDEO,
        'fbr' => Media::TYPE_VIDEO,
        'fbz' => Media::TYPE_VIDEO,
        'fcp' => Media::TYPE_VIDEO,
        'fcproject' => Media::TYPE_VIDEO,
        'flc' => Media::TYPE_VIDEO,
        'flh' => Media::TYPE_VIDEO,
        'fli' => Media::TYPE_VIDEO,
        'flv' => Media::TYPE_VIDEO,
        'flx' => Media::TYPE_VIDEO,
        'gfp' => Media::TYPE_VIDEO,
        'gl' => Media::TYPE_VIDEO,
        'grasp' => Media::TYPE_VIDEO,
        'gts' => Media::TYPE_VIDEO,
        'gvi' => Media::TYPE_VIDEO,
        'gvp' => Media::TYPE_VIDEO,
        'h264' => Media::TYPE_VIDEO,
        'hdmov' => Media::TYPE_VIDEO,
        'hkm' => Media::TYPE_VIDEO,
        'ifo' => Media::TYPE_VIDEO,
        'imovieproj' => Media::TYPE_VIDEO,
        'imovieproject' => Media::TYPE_VIDEO,
        'irf' => Media::TYPE_VIDEO,
        'ism' => Media::TYPE_VIDEO,
        'ismc' => Media::TYPE_VIDEO,
        'ismv' => Media::TYPE_VIDEO,
        'iva' => Media::TYPE_VIDEO,
        'ivf' => Media::TYPE_VIDEO,
        'ivr' => Media::TYPE_VIDEO,
        'ivs' => Media::TYPE_VIDEO,
        'izz' => Media::TYPE_VIDEO,
        'izzy' => Media::TYPE_VIDEO,
        'jts' => Media::TYPE_VIDEO,
        'jtv' => Media::TYPE_VIDEO,
        'k3g' => Media::TYPE_VIDEO,
        'lrec' => Media::TYPE_VIDEO,
        'lsf' => Media::TYPE_VIDEO,
        'lsx' => Media::TYPE_VIDEO,
        'm15' => Media::TYPE_VIDEO,
        'm1pg' => Media::TYPE_VIDEO,
        'm1v' => Media::TYPE_VIDEO,
        'm21' => Media::TYPE_VIDEO,
        'm2a' => Media::TYPE_VIDEO,
        'm2p' => Media::TYPE_VIDEO,
        'm2t' => Media::TYPE_VIDEO,
        'm2ts' => Media::TYPE_VIDEO,
        'm2v' => Media::TYPE_VIDEO,
        'm4e' => Media::TYPE_VIDEO,
        'm4u' => Media::TYPE_VIDEO,
        'm4v' => Media::TYPE_VIDEO,
        'm75' => Media::TYPE_VIDEO,
        'meta' => Media::TYPE_VIDEO,
        'mgv' => Media::TYPE_VIDEO,
        'mj2' => Media::TYPE_VIDEO,
        'mjp' => Media::TYPE_VIDEO,
        'mjpg' => Media::TYPE_VIDEO,
        'mkv' => Media::TYPE_VIDEO,
        'mmv' => Media::TYPE_VIDEO,
        'mnv' => Media::TYPE_VIDEO,
        'mob' => Media::TYPE_VIDEO,
        'mod' => Media::TYPE_VIDEO,
        'modd' => Media::TYPE_VIDEO,
        'moff' => Media::TYPE_VIDEO,
        'moi' => Media::TYPE_VIDEO,
        'moov' => Media::TYPE_VIDEO,
        'mov' => Media::TYPE_VIDEO,
        'movie' => Media::TYPE_VIDEO,
        'mp21' => Media::TYPE_VIDEO,
        'mp2v' => Media::TYPE_VIDEO,
        'mp4' => Media::TYPE_VIDEO,
        'mp4v' => Media::TYPE_VIDEO,
        'mpe' => Media::TYPE_VIDEO,
        'mpeg' => Media::TYPE_VIDEO,
        'mpeg4' => Media::TYPE_VIDEO,
        'mpf' => Media::TYPE_VIDEO,
        'mpg' => Media::TYPE_VIDEO,
        'mpg2' => Media::TYPE_VIDEO,
        'mpgindex' => Media::TYPE_VIDEO,
        'mpl' => Media::TYPE_VIDEO,
        'mpls' => Media::TYPE_VIDEO,
        'mpsub' => Media::TYPE_VIDEO,
        'mpv' => Media::TYPE_VIDEO,
        'mpv2' => Media::TYPE_VIDEO,
        'mqv' => Media::TYPE_VIDEO,
        'msdvd' => Media::TYPE_VIDEO,
        'msh' => Media::TYPE_VIDEO,
        'mswmm' => Media::TYPE_VIDEO,
        'mts' => Media::TYPE_VIDEO,
        'mtv' => Media::TYPE_VIDEO,
        'mvb' => Media::TYPE_VIDEO,
        'mvc' => Media::TYPE_VIDEO,
        'mvd' => Media::TYPE_VIDEO,
        'mve' => Media::TYPE_VIDEO,
        'mvp' => Media::TYPE_VIDEO,
        'mvy' => Media::TYPE_VIDEO,
        'mxf' => Media::TYPE_VIDEO,
        'mys' => Media::TYPE_VIDEO,
        'ncor' => Media::TYPE_VIDEO,
        'nsv' => Media::TYPE_VIDEO,
        'nuv' => Media::TYPE_VIDEO,
        'nvc' => Media::TYPE_VIDEO,
        'ogm' => Media::TYPE_VIDEO,
        'ogv' => Media::TYPE_VIDEO,
        'ogx' => Media::TYPE_VIDEO,
        'osp' => Media::TYPE_VIDEO,
        'par' => Media::TYPE_VIDEO,
        'pds' => Media::TYPE_VIDEO,
        'pgi' => Media::TYPE_VIDEO,
        'photoshow' => Media::TYPE_VIDEO,
        'piv' => Media::TYPE_VIDEO,
        'playlist' => Media::TYPE_VIDEO,
        'pmf' => Media::TYPE_VIDEO,
        'pmv' => Media::TYPE_VIDEO,
        'pns' => Media::TYPE_VIDEO,
        'ppj' => Media::TYPE_VIDEO,
        'prel' => Media::TYPE_VIDEO,
        'pro' => Media::TYPE_VIDEO,
        'prproj' => Media::TYPE_VIDEO,
        'prtl' => Media::TYPE_VIDEO,
        'psh' => Media::TYPE_VIDEO,
        'pssd' => Media::TYPE_VIDEO,
        'pva' => Media::TYPE_VIDEO,
        'pvr' => Media::TYPE_VIDEO,
        'pxv' => Media::TYPE_VIDEO,
        'qt' => Media::TYPE_VIDEO,
        'qtch' => Media::TYPE_VIDEO,
        'qtl' => Media::TYPE_VIDEO,
        'qtm' => Media::TYPE_VIDEO,
        'qtz' => Media::TYPE_VIDEO,
        'r3d' => Media::TYPE_VIDEO,
        'rcproject' => Media::TYPE_VIDEO,
        'rdb' => Media::TYPE_VIDEO,
        'rec' => Media::TYPE_VIDEO,
        'rm' => Media::TYPE_VIDEO,
        'rmd' => Media::TYPE_VIDEO,
        'rmp' => Media::TYPE_VIDEO,
        'rms' => Media::TYPE_VIDEO,
        'rmvb' => Media::TYPE_VIDEO,
        'roq' => Media::TYPE_VIDEO,
        'rp' => Media::TYPE_VIDEO,
        'rsx' => Media::TYPE_VIDEO,
        'rts' => Media::TYPE_VIDEO,
        'rum' => Media::TYPE_VIDEO,
        'rv' => Media::TYPE_VIDEO,
        'sbk' => Media::TYPE_VIDEO,
        'sbt' => Media::TYPE_VIDEO,
        'scc' => Media::TYPE_VIDEO,
        'scm' => Media::TYPE_VIDEO,
        'scn' => Media::TYPE_VIDEO,
        'screenflow' => Media::TYPE_VIDEO,
        'sec' => Media::TYPE_VIDEO,
        'seq' => Media::TYPE_VIDEO,
        'sfd' => Media::TYPE_VIDEO,
        'sfvidcap' => Media::TYPE_VIDEO,
        'smi' => Media::TYPE_VIDEO,
        'smil' => Media::TYPE_VIDEO,
        'smk' => Media::TYPE_VIDEO,
        'sml' => Media::TYPE_VIDEO,
        'smv' => Media::TYPE_VIDEO,
        'spl' => Media::TYPE_VIDEO,
        'sqz' => Media::TYPE_VIDEO,
        'srt' => Media::TYPE_VIDEO,
        'ssm' => Media::TYPE_VIDEO,
        'str' => Media::TYPE_VIDEO,
        'stx' => Media::TYPE_VIDEO,
        'svi' => Media::TYPE_VIDEO,
        'swf' => Media::TYPE_VIDEO,
        'swi' => Media::TYPE_VIDEO,
        'swt' => Media::TYPE_VIDEO,
        'tda3mt' => Media::TYPE_VIDEO,
        'tdx' => Media::TYPE_VIDEO,
        'tivo' => Media::TYPE_VIDEO,
        'tix' => Media::TYPE_VIDEO,
        'tod' => Media::TYPE_VIDEO,
        'tp' => Media::TYPE_VIDEO,
        'tp0' => Media::TYPE_VIDEO,
        'tpd' => Media::TYPE_VIDEO,
        'tpr' => Media::TYPE_VIDEO,
        'trp' => Media::TYPE_VIDEO,
        'ts' => Media::TYPE_VIDEO,
        'tsp' => Media::TYPE_VIDEO,
        'tvs' => Media::TYPE_VIDEO,
        'vc1' => Media::TYPE_VIDEO,
        'vcpf' => Media::TYPE_VIDEO,
        'vcr' => Media::TYPE_VIDEO,
        'vcv' => Media::TYPE_VIDEO,
        'vdo' => Media::TYPE_VIDEO,
        'vdr' => Media::TYPE_VIDEO,
        'veg' => Media::TYPE_VIDEO,
        'vem' => Media::TYPE_VIDEO,
        'vep' => Media::TYPE_VIDEO,
        'vf' => Media::TYPE_VIDEO,
        'vft' => Media::TYPE_VIDEO,
        'vfw' => Media::TYPE_VIDEO,
        'vfz' => Media::TYPE_VIDEO,
        'vgz' => Media::TYPE_VIDEO,
        'vid' => Media::TYPE_VIDEO,
        'video' => Media::TYPE_VIDEO,
        'viewlet' => Media::TYPE_VIDEO,
        'viv' => Media::TYPE_VIDEO,
        'vivo' => Media::TYPE_VIDEO,
        'vlab' => Media::TYPE_VIDEO,
        'vob' => Media::TYPE_VIDEO,
        'vp3' => Media::TYPE_VIDEO,
        'vp6' => Media::TYPE_VIDEO,
        'vp7' => Media::TYPE_VIDEO,
        'vpj' => Media::TYPE_VIDEO,
        'vro' => Media::TYPE_VIDEO,
        'vs4' => Media::TYPE_VIDEO,
        'vse' => Media::TYPE_VIDEO,
        'vsp' => Media::TYPE_VIDEO,
        'w32' => Media::TYPE_VIDEO,
        'wcp' => Media::TYPE_VIDEO,
        'webm' => Media::TYPE_VIDEO,
        'wlmp' => Media::TYPE_VIDEO,
        'wm' => Media::TYPE_VIDEO,
        'wmd' => Media::TYPE_VIDEO,
        'wmmp' => Media::TYPE_VIDEO,
        'wmv' => Media::TYPE_VIDEO,
        'wmx' => Media::TYPE_VIDEO,
        'wot' => Media::TYPE_VIDEO,
        'wp3' => Media::TYPE_VIDEO,
        'wpl' => Media::TYPE_VIDEO,
        'wtv' => Media::TYPE_VIDEO,
        'wvx' => Media::TYPE_VIDEO,
        'xej' => Media::TYPE_VIDEO,
        'xel' => Media::TYPE_VIDEO,
        'xesc' => Media::TYPE_VIDEO,
        'xfl' => Media::TYPE_VIDEO,
        'xlmv' => Media::TYPE_VIDEO,
        'xvid' => Media::TYPE_VIDEO,
        'yuv' => Media::TYPE_VIDEO,
        'zm1' => Media::TYPE_VIDEO,
        'zm2' => Media::TYPE_VIDEO,
        'zm3' => Media::TYPE_VIDEO,
        'zmv' => Media::TYPE_VIDEO,
        '4mp' => Media::TYPE_MUSIC,
        '669' => Media::TYPE_MUSIC,
        '6cm' => Media::TYPE_MUSIC,
        '8cm' => Media::TYPE_MUSIC,
        '8med' => Media::TYPE_MUSIC,
        '8svx' => Media::TYPE_MUSIC,
        'a2m' => Media::TYPE_MUSIC,
        'a52' => Media::TYPE_MUSIC,
        'aa' => Media::TYPE_MUSIC,
        'aa3' => Media::TYPE_MUSIC,
        'aac' => Media::TYPE_MUSIC,
        'aax' => Media::TYPE_MUSIC,
        'ab' => Media::TYPE_MUSIC,
        'abc' => Media::TYPE_MUSIC,
        'abm' => Media::TYPE_MUSIC,
        'ac3' => Media::TYPE_MUSIC,
        'acd' => Media::TYPE_MUSIC,
        'acd-bak' => Media::TYPE_MUSIC,
        'acd-zip' => Media::TYPE_MUSIC,
        'acm' => Media::TYPE_MUSIC,
        'acp' => Media::TYPE_MUSIC,
        'act' => Media::TYPE_MUSIC,
        'adg' => Media::TYPE_MUSIC,
        'adt' => Media::TYPE_MUSIC,
        'adts' => Media::TYPE_MUSIC,
        'adv' => Media::TYPE_MUSIC,
        'afc' => Media::TYPE_MUSIC,
        'agm' => Media::TYPE_MUSIC,
        'ahx' => Media::TYPE_MUSIC,
        'aif' => Media::TYPE_MUSIC,
        'aifc' => Media::TYPE_MUSIC,
        'aiff' => Media::TYPE_MUSIC,
        'ais' => Media::TYPE_MUSIC,
        'akp' => Media::TYPE_MUSIC,
        'al' => Media::TYPE_MUSIC,
        'alac' => Media::TYPE_MUSIC,
        'alaw' => Media::TYPE_MUSIC,
        'alc' => Media::TYPE_MUSIC,
        'all' => Media::TYPE_MUSIC,
        'als' => Media::TYPE_MUSIC,
        'amf' => Media::TYPE_MUSIC,
        'amr' => Media::TYPE_MUSIC,
        'ams' => Media::TYPE_MUSIC,
        'amxd' => Media::TYPE_MUSIC,
        'aob' => Media::TYPE_MUSIC,
        'ape' => Media::TYPE_MUSIC,
        'apf' => Media::TYPE_MUSIC,
        'apl' => Media::TYPE_MUSIC,
        'aria' => Media::TYPE_MUSIC,
        'ariax' => Media::TYPE_MUSIC,
        'asd' => Media::TYPE_MUSIC,
        'ase' => Media::TYPE_MUSIC,
        'at3' => Media::TYPE_MUSIC,
        'atrac' => Media::TYPE_MUSIC,
        'au' => Media::TYPE_MUSIC,
        'aud' => Media::TYPE_MUSIC,
        'aup' => Media::TYPE_MUSIC,
        'avr' => Media::TYPE_MUSIC,
        'awb' => Media::TYPE_MUSIC,
        'ay' => Media::TYPE_MUSIC,
        'b4s' => Media::TYPE_MUSIC,
        'band' => Media::TYPE_MUSIC,
        'bap' => Media::TYPE_MUSIC,
        'bdd' => Media::TYPE_MUSIC,
        'bidule' => Media::TYPE_MUSIC,
        'brstm' => Media::TYPE_MUSIC,
        'bun' => Media::TYPE_MUSIC,
        'bwf' => Media::TYPE_MUSIC,
        'c01' => Media::TYPE_MUSIC,
        'caf' => Media::TYPE_MUSIC,
        'cda' => Media::TYPE_MUSIC,
        'cdda' => Media::TYPE_MUSIC,
        'cel' => Media::TYPE_MUSIC,
        'cfa' => Media::TYPE_MUSIC,
        'cfxr' => Media::TYPE_MUSIC,
        'cidb' => Media::TYPE_MUSIC,
        'cmf' => Media::TYPE_MUSIC,
        'copy' => Media::TYPE_MUSIC,
        'cpr' => Media::TYPE_MUSIC,
        'cpt' => Media::TYPE_MUSIC,
        'csh' => Media::TYPE_MUSIC,
        'cwp' => Media::TYPE_MUSIC,
        'd00' => Media::TYPE_MUSIC,
        'd01' => Media::TYPE_MUSIC,
        'dcf' => Media::TYPE_MUSIC,
        'dcm' => Media::TYPE_MUSIC,
        'dct' => Media::TYPE_MUSIC,
        'ddt' => Media::TYPE_MUSIC,
        'dewf' => Media::TYPE_MUSIC,
        'df2' => Media::TYPE_MUSIC,
        'dfc' => Media::TYPE_MUSIC,
        'dig' => Media::TYPE_MUSIC,
        'dls' => Media::TYPE_MUSIC,
        'dm' => Media::TYPE_MUSIC,
        'dmf' => Media::TYPE_MUSIC,
        'dmsa' => Media::TYPE_MUSIC,
        'dmse' => Media::TYPE_MUSIC,
        'dra' => Media::TYPE_MUSIC,
        'drg' => Media::TYPE_MUSIC,
        'ds' => Media::TYPE_MUSIC,
        'ds2' => Media::TYPE_MUSIC,
        'dsf' => Media::TYPE_MUSIC,
        'dsm' => Media::TYPE_MUSIC,
        'dsp' => Media::TYPE_MUSIC,
        'dss' => Media::TYPE_MUSIC,
        'dtm' => Media::TYPE_MUSIC,
        'dts' => Media::TYPE_MUSIC,
        'dtshd' => Media::TYPE_MUSIC,
        'dvf' => Media::TYPE_MUSIC,
        'dwd' => Media::TYPE_MUSIC,
        'ear' => Media::TYPE_MUSIC,
        'efa' => Media::TYPE_MUSIC,
        'efe' => Media::TYPE_MUSIC,
        'efk' => Media::TYPE_MUSIC,
        'efq' => Media::TYPE_MUSIC,
        'efs' => Media::TYPE_MUSIC,
        'efv' => Media::TYPE_MUSIC,
        'emd' => Media::TYPE_MUSIC,
        'emp' => Media::TYPE_MUSIC,
        'emx' => Media::TYPE_MUSIC,
        'esps' => Media::TYPE_MUSIC,
        'expressionmap' => Media::TYPE_MUSIC,
        'f2r' => Media::TYPE_MUSIC,
        'f32' => Media::TYPE_MUSIC,
        'f3r' => Media::TYPE_MUSIC,
        'f4a' => Media::TYPE_MUSIC,
        'f64' => Media::TYPE_MUSIC,
        'far' => Media::TYPE_MUSIC,
        'fda' => Media::TYPE_MUSIC,
        'fff' => Media::TYPE_MUSIC,
        'flac' => Media::TYPE_MUSIC,
        'flp' => Media::TYPE_MUSIC,
        'fls' => Media::TYPE_MUSIC,
        'frg' => Media::TYPE_MUSIC,
        'fsm' => Media::TYPE_MUSIC,
        'ftm' => Media::TYPE_MUSIC,
        'fzb' => Media::TYPE_MUSIC,
        'fzf' => Media::TYPE_MUSIC,
        'fzv' => Media::TYPE_MUSIC,
        'g721' => Media::TYPE_MUSIC,
        'g723' => Media::TYPE_MUSIC,
        'g726' => Media::TYPE_MUSIC,
        'gbproj' => Media::TYPE_MUSIC,
        'gbs' => Media::TYPE_MUSIC,
        'gig' => Media::TYPE_MUSIC,
        'gm' => Media::TYPE_MUSIC,
        'gp5' => Media::TYPE_MUSIC,
        'gpbank' => Media::TYPE_MUSIC,
        'gpk' => Media::TYPE_MUSIC,
        'gpx' => Media::TYPE_MUSIC,
        'gro' => Media::TYPE_MUSIC,
        'groove' => Media::TYPE_MUSIC,
        'gsm' => Media::TYPE_MUSIC,
        'h0' => Media::TYPE_MUSIC,
        'hdp' => Media::TYPE_MUSIC,
        'hma' => Media::TYPE_MUSIC,
        'hsb' => Media::TYPE_MUSIC,
        'ics' => Media::TYPE_MUSIC,
        'igp' => Media::TYPE_MUSIC,
        'igr' => Media::TYPE_MUSIC,
        'imf' => Media::TYPE_MUSIC,
        'imp' => Media::TYPE_MUSIC,
        'ins' => Media::TYPE_MUSIC,
        'isma' => Media::TYPE_MUSIC,
        'it' => Media::TYPE_MUSIC,
        'iti' => Media::TYPE_MUSIC,
        'its' => Media::TYPE_MUSIC,
        'jam' => Media::TYPE_MUSIC,
        'jo' => Media::TYPE_MUSIC,
        'jo-7z' => Media::TYPE_MUSIC,
        'k25' => Media::TYPE_MUSIC,
        'k26' => Media::TYPE_MUSIC,
        'kar' => Media::TYPE_MUSIC,
        'kfn' => Media::TYPE_MUSIC,
        'kin' => Media::TYPE_MUSIC,
        'kit' => Media::TYPE_MUSIC,
        'kmp' => Media::TYPE_MUSIC,
        'koz' => Media::TYPE_MUSIC,
        'kpl' => Media::TYPE_MUSIC,
        'krz' => Media::TYPE_MUSIC,
        'ksc' => Media::TYPE_MUSIC,
        'ksf' => Media::TYPE_MUSIC,
        'kt2' => Media::TYPE_MUSIC,
        'kt3' => Media::TYPE_MUSIC,
        'ktp' => Media::TYPE_MUSIC,
        'l' => Media::TYPE_MUSIC,
        'la' => Media::TYPE_MUSIC,
        'lof' => Media::TYPE_MUSIC,
        'lqt' => Media::TYPE_MUSIC,
        'lso' => Media::TYPE_MUSIC,
        'lvp' => Media::TYPE_MUSIC,
        'lwv' => Media::TYPE_MUSIC,
        'm1a' => Media::TYPE_MUSIC,
        'm3u' => Media::TYPE_MUSIC,
        'm3u8' => Media::TYPE_MUSIC,
        'm4a' => Media::TYPE_MUSIC,
        'm4b' => Media::TYPE_MUSIC,
        'm4p' => Media::TYPE_MUSIC,
        'm4r' => Media::TYPE_MUSIC,
        'ma1' => Media::TYPE_MUSIC,
        'mbr' => Media::TYPE_MUSIC,
        'mdl' => Media::TYPE_MUSIC,
        'med' => Media::TYPE_MUSIC,
        'mgv' => Media::TYPE_MUSIC,
        'mid' => Media::TYPE_MUSIC,
        'midi' => Media::TYPE_MUSIC,
        'miniusf' => Media::TYPE_MUSIC,
        'mka' => Media::TYPE_MUSIC,
        'mlp' => Media::TYPE_MUSIC,
        'mmf' => Media::TYPE_MUSIC,
        'mmm' => Media::TYPE_MUSIC,
        'mmp' => Media::TYPE_MUSIC,
        'mo3' => Media::TYPE_MUSIC,
        'mod' => Media::TYPE_MUSIC,
        'mp1' => Media::TYPE_MUSIC,
        'mp2' => Media::TYPE_MUSIC,
        'mp3' => Media::TYPE_MUSIC,
        'mpa' => Media::TYPE_MUSIC,
        'mpc' => Media::TYPE_MUSIC,
        'mpga' => Media::TYPE_MUSIC,
        'mpu' => Media::TYPE_MUSIC,
        'mp_' => Media::TYPE_MUSIC,
        'mscx' => Media::TYPE_MUSIC,
        'mscz' => Media::TYPE_MUSIC,
        'msv' => Media::TYPE_MUSIC,
        'mt2' => Media::TYPE_MUSIC,
        'mt9' => Media::TYPE_MUSIC,
        'mte' => Media::TYPE_MUSIC,
        'mtf' => Media::TYPE_MUSIC,
        'mti' => Media::TYPE_MUSIC,
        'mtm' => Media::TYPE_MUSIC,
        'mtp' => Media::TYPE_MUSIC,
        'mts' => Media::TYPE_MUSIC,
        'mus' => Media::TYPE_MUSIC,
        'mus' => Media::TYPE_MUSIC,
        'musa' => Media::TYPE_MUSIC,
        'mws' => Media::TYPE_MUSIC,
        'mxl' => Media::TYPE_MUSIC,
        'mxmf' => Media::TYPE_MUSIC,
        'mzp' => Media::TYPE_MUSIC,
        'nap' => Media::TYPE_MUSIC,
        'ncw' => Media::TYPE_MUSIC,
        'nkb' => Media::TYPE_MUSIC,
        'nki' => Media::TYPE_MUSIC,
        'nkm' => Media::TYPE_MUSIC,
        'nks' => Media::TYPE_MUSIC,
        'nkx' => Media::TYPE_MUSIC,
        'npl' => Media::TYPE_MUSIC,
        'nra' => Media::TYPE_MUSIC,
        'nrt' => Media::TYPE_MUSIC,
        'nsa' => Media::TYPE_MUSIC,
        'nsf' => Media::TYPE_MUSIC,
        'nst' => Media::TYPE_MUSIC,
        'ntn' => Media::TYPE_MUSIC,
        'nvf' => Media::TYPE_MUSIC,
        'nwc' => Media::TYPE_MUSIC,
        'odm' => Media::TYPE_MUSIC,
        'ofr' => Media::TYPE_MUSIC,
        'oga' => Media::TYPE_MUSIC,
        'ogg' => Media::TYPE_MUSIC,
        'okt' => Media::TYPE_MUSIC,
        'oma' => Media::TYPE_MUSIC,
        'omf' => Media::TYPE_MUSIC,
        'omg' => Media::TYPE_MUSIC,
        'omx' => Media::TYPE_MUSIC,
        'orc' => Media::TYPE_MUSIC,
        'ots' => Media::TYPE_MUSIC,
        'ove' => Media::TYPE_MUSIC,
        'ovw' => Media::TYPE_MUSIC,
        'pac' => Media::TYPE_MUSIC,
        'pat' => Media::TYPE_MUSIC,
        'pbf' => Media::TYPE_MUSIC,
        'pca' => Media::TYPE_MUSIC,
        'pcast' => Media::TYPE_MUSIC,
        'pcg' => Media::TYPE_MUSIC,
        'pcm' => Media::TYPE_MUSIC,
        'pd' => Media::TYPE_MUSIC,
        'peak' => Media::TYPE_MUSIC,
        'pek' => Media::TYPE_MUSIC,
        'pho' => Media::TYPE_MUSIC,
        'phy' => Media::TYPE_MUSIC,
        'pk' => Media::TYPE_MUSIC,
        'pkf' => Media::TYPE_MUSIC,
        'pla' => Media::TYPE_MUSIC,
        'pls' => Media::TYPE_MUSIC,
        'pna' => Media::TYPE_MUSIC,
        'ppc' => Media::TYPE_MUSIC,
        'ppcx' => Media::TYPE_MUSIC,
        'prg' => Media::TYPE_MUSIC,
        'psf' => Media::TYPE_MUSIC,
        'psm' => Media::TYPE_MUSIC,
        'psy' => Media::TYPE_MUSIC,
        'ptf' => Media::TYPE_MUSIC,
        'ptm' => Media::TYPE_MUSIC,
        'pts' => Media::TYPE_MUSIC,
        'pvc' => Media::TYPE_MUSIC,
        'qcp' => Media::TYPE_MUSIC,
        'r' => Media::TYPE_MUSIC,
        'r1m' => Media::TYPE_MUSIC,
        'ra' => Media::TYPE_MUSIC,
        'ram' => Media::TYPE_MUSIC,
        'raw' => Media::TYPE_MUSIC,
        'rax' => Media::TYPE_MUSIC,
        'rbs' => Media::TYPE_MUSIC,
        'rcy' => Media::TYPE_MUSIC,
        'rex' => Media::TYPE_MUSIC,
        'rfl' => Media::TYPE_MUSIC,
        'rip' => Media::TYPE_MUSIC,
        'rmf' => Media::TYPE_MUSIC,
        'rmi' => Media::TYPE_MUSIC,
        'rmj' => Media::TYPE_MUSIC,
        'rmm' => Media::TYPE_MUSIC,
        'rmx' => Media::TYPE_MUSIC,
        'rng' => Media::TYPE_MUSIC,
        'rns' => Media::TYPE_MUSIC,
        'rol' => Media::TYPE_MUSIC,
        'rsn' => Media::TYPE_MUSIC,
        'rso' => Media::TYPE_MUSIC,
        'rti' => Media::TYPE_MUSIC,
        'rtm' => Media::TYPE_MUSIC,
        'rts' => Media::TYPE_MUSIC,
        'rvx' => Media::TYPE_MUSIC,
        'rx2' => Media::TYPE_MUSIC,
        's3i' => Media::TYPE_MUSIC,
        's3m' => Media::TYPE_MUSIC,
        's3z' => Media::TYPE_MUSIC,
        'saf' => Media::TYPE_MUSIC,
        'sam' => Media::TYPE_MUSIC,
        'sap' => Media::TYPE_MUSIC,
        'sb' => Media::TYPE_MUSIC,
        'sbg' => Media::TYPE_MUSIC,
        'sbi' => Media::TYPE_MUSIC,
        'sbk' => Media::TYPE_MUSIC,
        'sc2' => Media::TYPE_MUSIC,
        'sd' => Media::TYPE_MUSIC,
        'sd2' => Media::TYPE_MUSIC,
        'sd2f' => Media::TYPE_MUSIC,
        'sdat' => Media::TYPE_MUSIC,
        'sdii' => Media::TYPE_MUSIC,
        'sds' => Media::TYPE_MUSIC,
        'sdt' => Media::TYPE_MUSIC,
        'sdx' => Media::TYPE_MUSIC,
        'seg' => Media::TYPE_MUSIC,
        'ses' => Media::TYPE_MUSIC,
        'sesx' => Media::TYPE_MUSIC,
        'sf' => Media::TYPE_MUSIC,
        'sf2' => Media::TYPE_MUSIC,
        'sfap0' => Media::TYPE_MUSIC,
        'sfk' => Media::TYPE_MUSIC,
        'sfl' => Media::TYPE_MUSIC,
        'sfs' => Media::TYPE_MUSIC,
        'shn' => Media::TYPE_MUSIC,
        'sib' => Media::TYPE_MUSIC,
        'sid' => Media::TYPE_MUSIC,
        'sid' => Media::TYPE_MUSIC,
        'smf' => Media::TYPE_MUSIC,
        'smp' => Media::TYPE_MUSIC,
        'snd' => Media::TYPE_MUSIC,
        'snd' => Media::TYPE_MUSIC,
        'snd' => Media::TYPE_MUSIC,
        'sng' => Media::TYPE_MUSIC,
        'sng' => Media::TYPE_MUSIC,
        'sou' => Media::TYPE_MUSIC,
        'sppack' => Media::TYPE_MUSIC,
        'sprg' => Media::TYPE_MUSIC,
        'spx' => Media::TYPE_MUSIC,
        'sseq' => Media::TYPE_MUSIC,
        'sseq' => Media::TYPE_MUSIC,
        'ssnd' => Media::TYPE_MUSIC,
        'stap' => Media::TYPE_MUSIC,
        'stm' => Media::TYPE_MUSIC,
        'stx' => Media::TYPE_MUSIC,
        'sty' => Media::TYPE_MUSIC,
        'sty' => Media::TYPE_MUSIC,
        'svd' => Media::TYPE_MUSIC,
        'svx' => Media::TYPE_MUSIC,
        'sw' => Media::TYPE_MUSIC,
        'swa' => Media::TYPE_MUSIC,
        'syh' => Media::TYPE_MUSIC,
        'syn' => Media::TYPE_MUSIC,
        'syn' => Media::TYPE_MUSIC,
        'syw' => Media::TYPE_MUSIC,
        'syx' => Media::TYPE_MUSIC,
        'tak' => Media::TYPE_MUSIC,
        'tak' => Media::TYPE_MUSIC,
        'td0' => Media::TYPE_MUSIC,
        'tfmx' => Media::TYPE_MUSIC,
        'tg' => Media::TYPE_MUSIC,
        'thx' => Media::TYPE_MUSIC,
        'toc' => Media::TYPE_MUSIC,
        'tsp' => Media::TYPE_MUSIC,
        'tta' => Media::TYPE_MUSIC,
        'tun' => Media::TYPE_MUSIC,
        'txw' => Media::TYPE_MUSIC,
        'u' => Media::TYPE_MUSIC,
        'uax' => Media::TYPE_MUSIC,
        'ub' => Media::TYPE_MUSIC,
        'ulaw' => Media::TYPE_MUSIC,
        'ult' => Media::TYPE_MUSIC,
        'ulw' => Media::TYPE_MUSIC,
        'uni' => Media::TYPE_MUSIC,
        'usf' => Media::TYPE_MUSIC,
        'usflib' => Media::TYPE_MUSIC,
        'uw' => Media::TYPE_MUSIC,
        'uwf' => Media::TYPE_MUSIC,
        'vag' => Media::TYPE_MUSIC,
        'val' => Media::TYPE_MUSIC,
        'vap' => Media::TYPE_MUSIC,
        'vb' => Media::TYPE_MUSIC,
        'vc3' => Media::TYPE_MUSIC,
        'vdj' => Media::TYPE_MUSIC,
        'vgm' => Media::TYPE_MUSIC,
        'vgz' => Media::TYPE_MUSIC,
        'vmd' => Media::TYPE_MUSIC,
        'vmf' => Media::TYPE_MUSIC,
        'vmf' => Media::TYPE_MUSIC,
        'voc' => Media::TYPE_MUSIC,
        'voi' => Media::TYPE_MUSIC,
        'vox' => Media::TYPE_MUSIC,
        'vpm' => Media::TYPE_MUSIC,
        'vqf' => Media::TYPE_MUSIC,
        'vrf' => Media::TYPE_MUSIC,
        'vtx' => Media::TYPE_MUSIC,
        'vyf' => Media::TYPE_MUSIC,
        'w01' => Media::TYPE_MUSIC,
        'w64' => Media::TYPE_MUSIC,
        'wav' => Media::TYPE_MUSIC,
        'wav' => Media::TYPE_MUSIC,
        'wave' => Media::TYPE_MUSIC,
        'wax' => Media::TYPE_MUSIC,
        'wfb' => Media::TYPE_MUSIC,
        'wfd' => Media::TYPE_MUSIC,
        'wfp' => Media::TYPE_MUSIC,
        'wma' => Media::TYPE_MUSIC,
        'wow' => Media::TYPE_MUSIC,
        'wpk' => Media::TYPE_MUSIC,
        'wpp' => Media::TYPE_MUSIC,
        'wproj' => Media::TYPE_MUSIC,
        'wrk' => Media::TYPE_MUSIC,
        'wtpl' => Media::TYPE_MUSIC,
        'wtpt' => Media::TYPE_MUSIC,
        'wus' => Media::TYPE_MUSIC,
        'wut' => Media::TYPE_MUSIC,
        'wv' => Media::TYPE_MUSIC,
        'wvc' => Media::TYPE_MUSIC,
        'wve' => Media::TYPE_MUSIC,
        'wwu' => Media::TYPE_MUSIC,
        'wyz' => Media::TYPE_MUSIC,
        'xa' => Media::TYPE_MUSIC,
        'xa' => Media::TYPE_MUSIC,
        'xfs' => Media::TYPE_MUSIC,
        'xi' => Media::TYPE_MUSIC,
        'xm' => Media::TYPE_MUSIC,
        'xmf' => Media::TYPE_MUSIC,
        'xmi' => Media::TYPE_MUSIC,
        'xmz' => Media::TYPE_MUSIC,
        'xp' => Media::TYPE_MUSIC,
        'xrns' => Media::TYPE_MUSIC,
        'xsb' => Media::TYPE_MUSIC,
        'xspf' => Media::TYPE_MUSIC,
        'xt' => Media::TYPE_MUSIC,
        'xwb' => Media::TYPE_MUSIC,
        'ym' => Media::TYPE_MUSIC,
        'zpa' => Media::TYPE_MUSIC,
        'zpl' => Media::TYPE_MUSIC,
        'zvd' => Media::TYPE_MUSIC,
        'zvr' => Media::TYPE_MUSIC,
        '0' => Media::TYPE_ARCHIVE,
        '000' => Media::TYPE_ARCHIVE,
        '7z' => Media::TYPE_ARCHIVE,
        'a00' => Media::TYPE_ARCHIVE,
        'a01' => Media::TYPE_ARCHIVE,
        'a02' => Media::TYPE_ARCHIVE,
        'ace' => Media::TYPE_ARCHIVE,
        'ain' => Media::TYPE_ARCHIVE,
        'alz' => Media::TYPE_ARCHIVE,
        'apz' => Media::TYPE_ARCHIVE,
        'ar' => Media::TYPE_ARCHIVE,
        'arc' => Media::TYPE_ARCHIVE,
        'arh' => Media::TYPE_ARCHIVE,
        'ari' => Media::TYPE_ARCHIVE,
        'arj' => Media::TYPE_ARCHIVE,
        'ark' => Media::TYPE_ARCHIVE,
        'b1' => Media::TYPE_ARCHIVE,
        'b64' => Media::TYPE_ARCHIVE,
        'ba' => Media::TYPE_ARCHIVE,
        'bh' => Media::TYPE_ARCHIVE,
        'boo' => Media::TYPE_ARCHIVE,
        'bz' => Media::TYPE_ARCHIVE,
        'bz2' => Media::TYPE_ARCHIVE,
        'bza' => Media::TYPE_ARCHIVE,
        'bzip' => Media::TYPE_ARCHIVE,
        'bzip2' => Media::TYPE_ARCHIVE,
        'c00' => Media::TYPE_ARCHIVE,
        'c01' => Media::TYPE_ARCHIVE,
        'c02' => Media::TYPE_ARCHIVE,
        'c10' => Media::TYPE_ARCHIVE,
        'car' => Media::TYPE_ARCHIVE,
        'cb7' => Media::TYPE_ARCHIVE,
        'cba' => Media::TYPE_ARCHIVE,
        'cbr' => Media::TYPE_ARCHIVE,
        'cbt' => Media::TYPE_ARCHIVE,
        'cbz' => Media::TYPE_ARCHIVE,
        'cp9' => Media::TYPE_ARCHIVE,
        'cpgz' => Media::TYPE_ARCHIVE,
        'cpt' => Media::TYPE_ARCHIVE,
        'czip' => Media::TYPE_ARCHIVE,
        'dar' => Media::TYPE_ARCHIVE,
        'dd' => Media::TYPE_ARCHIVE,
        'deb' => Media::TYPE_ARCHIVE,
        'dgc' => Media::TYPE_ARCHIVE,
        'dist' => Media::TYPE_ARCHIVE,
        'dl_' => Media::TYPE_ARCHIVE,
        'dz' => Media::TYPE_ARCHIVE,
        'ecs' => Media::TYPE_ARCHIVE,
        'efw' => Media::TYPE_ARCHIVE,
        'epi' => Media::TYPE_ARCHIVE,
        'f' => Media::TYPE_ARCHIVE,
        'fdp' => Media::TYPE_ARCHIVE,
        'gca' => Media::TYPE_ARCHIVE,
        'gz' => Media::TYPE_ARCHIVE,
        'gz2' => Media::TYPE_ARCHIVE,
        'gza' => Media::TYPE_ARCHIVE,
        'gzi' => Media::TYPE_ARCHIVE,
        'gzip' => Media::TYPE_ARCHIVE,
        'ha' => Media::TYPE_ARCHIVE,
        'hbc' => Media::TYPE_ARCHIVE,
        'hbc2' => Media::TYPE_ARCHIVE,
        'hbe' => Media::TYPE_ARCHIVE,
        'hki' => Media::TYPE_ARCHIVE,
        'hki1' => Media::TYPE_ARCHIVE,
        'hki2' => Media::TYPE_ARCHIVE,
        'hki3' => Media::TYPE_ARCHIVE,
        'hpk' => Media::TYPE_ARCHIVE,
        'hyp' => Media::TYPE_ARCHIVE,
        'ice' => Media::TYPE_ARCHIVE,
        'ipg' => Media::TYPE_ARCHIVE,
        'ipk' => Media::TYPE_ARCHIVE,
        'ish' => Media::TYPE_ARCHIVE,
        'ita' => Media::TYPE_ARCHIVE,
        'j' => Media::TYPE_ARCHIVE,
        'jar.pack' => Media::TYPE_ARCHIVE,
        'jgz' => Media::TYPE_ARCHIVE,
        'jic' => Media::TYPE_ARCHIVE,
        'kgb' => Media::TYPE_ARCHIVE,
        'kz' => Media::TYPE_ARCHIVE,
        'lbr' => Media::TYPE_ARCHIVE,
        'lemon' => Media::TYPE_ARCHIVE,
        'lha' => Media::TYPE_ARCHIVE,
        'lnx' => Media::TYPE_ARCHIVE,
        'lqr' => Media::TYPE_ARCHIVE,
        'lz' => Media::TYPE_ARCHIVE,
        'lzh' => Media::TYPE_ARCHIVE,
        'lzm' => Media::TYPE_ARCHIVE,
        'lzma' => Media::TYPE_ARCHIVE,
        'lzo' => Media::TYPE_ARCHIVE,
        'lzx' => Media::TYPE_ARCHIVE,
        'md' => Media::TYPE_ARCHIVE,
        'mint' => Media::TYPE_ARCHIVE,
        'mou' => Media::TYPE_ARCHIVE,
        'mpkg' => Media::TYPE_ARCHIVE,
        'mzp' => Media::TYPE_ARCHIVE,
        'mzp' => Media::TYPE_ARCHIVE,
        'oar' => Media::TYPE_ARCHIVE,
        'oz' => Media::TYPE_ARCHIVE,
        'pack.gz' => Media::TYPE_ARCHIVE,
        'package' => Media::TYPE_ARCHIVE,
        'pae' => Media::TYPE_ARCHIVE,
        'pak' => Media::TYPE_ARCHIVE,
        'paq6' => Media::TYPE_ARCHIVE,
        'paq7' => Media::TYPE_ARCHIVE,
        'paq8' => Media::TYPE_ARCHIVE,
        'paq8f' => Media::TYPE_ARCHIVE,
        'par' => Media::TYPE_ARCHIVE,
        'par2' => Media::TYPE_ARCHIVE,
        'pax' => Media::TYPE_ARCHIVE,
        'pbi' => Media::TYPE_ARCHIVE,
        'pcv' => Media::TYPE_ARCHIVE,
        'pea' => Media::TYPE_ARCHIVE,
        'pet' => Media::TYPE_ARCHIVE,
        'pf' => Media::TYPE_ARCHIVE,
        'pim' => Media::TYPE_ARCHIVE,
        'pit' => Media::TYPE_ARCHIVE,
        'piz' => Media::TYPE_ARCHIVE,
        'pkg' => Media::TYPE_ARCHIVE,
        'pup' => Media::TYPE_ARCHIVE,
        'pup' => Media::TYPE_ARCHIVE,
        'puz' => Media::TYPE_ARCHIVE,
        'pwa' => Media::TYPE_ARCHIVE,
        'qda' => Media::TYPE_ARCHIVE,
        'r0' => Media::TYPE_ARCHIVE,
        'r00' => Media::TYPE_ARCHIVE,
        'r01' => Media::TYPE_ARCHIVE,
        'r02' => Media::TYPE_ARCHIVE,
        'r03' => Media::TYPE_ARCHIVE,
        'r1' => Media::TYPE_ARCHIVE,
        'r2' => Media::TYPE_ARCHIVE,
        'r21' => Media::TYPE_ARCHIVE,
        'r30' => Media::TYPE_ARCHIVE,
        'rar' => Media::TYPE_ARCHIVE,
        'rev' => Media::TYPE_ARCHIVE,
        'rk' => Media::TYPE_ARCHIVE,
        'rnc' => Media::TYPE_ARCHIVE,
        'rp9' => Media::TYPE_ARCHIVE,
        'rpm' => Media::TYPE_ARCHIVE,
        'rte' => Media::TYPE_ARCHIVE,
        'rz' => Media::TYPE_ARCHIVE,
        'rzs' => Media::TYPE_ARCHIVE,
        's00' => Media::TYPE_ARCHIVE,
        's01' => Media::TYPE_ARCHIVE,
        's02' => Media::TYPE_ARCHIVE,
        's7z' => Media::TYPE_ARCHIVE,
        'sar' => Media::TYPE_ARCHIVE,
        'sbx' => Media::TYPE_ARCHIVE,
        'sdc' => Media::TYPE_ARCHIVE,
        'sdn' => Media::TYPE_ARCHIVE,
        'sea' => Media::TYPE_ARCHIVE,
        'sen' => Media::TYPE_ARCHIVE,
        'sfs' => Media::TYPE_ARCHIVE,
        'sfx' => Media::TYPE_ARCHIVE,
        'sh' => Media::TYPE_ARCHIVE,
        'shar' => Media::TYPE_ARCHIVE,
        'shk' => Media::TYPE_ARCHIVE,
        'shr' => Media::TYPE_ARCHIVE,
        'sit' => Media::TYPE_ARCHIVE,
        'sitx' => Media::TYPE_ARCHIVE,
        'spt' => Media::TYPE_ARCHIVE,
        'sqx' => Media::TYPE_ARCHIVE,
        'srep' => Media::TYPE_ARCHIVE,
        'sy_' => Media::TYPE_ARCHIVE,
        'tar.gz' => Media::TYPE_ARCHIVE,
        'tar.gz2' => Media::TYPE_ARCHIVE,
        'tar.lzma' => Media::TYPE_ARCHIVE,
        'tar.xz' => Media::TYPE_ARCHIVE,
        'taz' => Media::TYPE_ARCHIVE,
        'tbz' => Media::TYPE_ARCHIVE,
        'tbz2' => Media::TYPE_ARCHIVE,
        'tg' => Media::TYPE_ARCHIVE,
        'tgz' => Media::TYPE_ARCHIVE,
        'tlz' => Media::TYPE_ARCHIVE,
        'tlzma' => Media::TYPE_ARCHIVE,
        'txz' => Media::TYPE_ARCHIVE,
        'tz' => Media::TYPE_ARCHIVE,
        'uc2' => Media::TYPE_ARCHIVE,
        'ufs.uzip' => Media::TYPE_ARCHIVE,
        'uha' => Media::TYPE_ARCHIVE,
        'uzip' => Media::TYPE_ARCHIVE,
        'vem' => Media::TYPE_ARCHIVE,
        'vsi' => Media::TYPE_ARCHIVE,
        'war' => Media::TYPE_ARCHIVE,
        'wot' => Media::TYPE_ARCHIVE,
        'xef' => Media::TYPE_ARCHIVE,
        'xez' => Media::TYPE_ARCHIVE,
        'xmcdz' => Media::TYPE_ARCHIVE,
        'xx' => Media::TYPE_ARCHIVE,
        'xz' => Media::TYPE_ARCHIVE,
        'y' => Media::TYPE_ARCHIVE,
        'yz' => Media::TYPE_ARCHIVE,
        'yz1' => Media::TYPE_ARCHIVE,
        'z' => Media::TYPE_ARCHIVE,
        'z01' => Media::TYPE_ARCHIVE,
        'z02' => Media::TYPE_ARCHIVE,
        'z03' => Media::TYPE_ARCHIVE,
        'z04' => Media::TYPE_ARCHIVE,
        'zap' => Media::TYPE_ARCHIVE,
        'zfsendtotarget' => Media::TYPE_ARCHIVE,
        'zi' => Media::TYPE_ARCHIVE,
        'zip' => Media::TYPE_ARCHIVE,
        'zipx' => Media::TYPE_ARCHIVE,
        'zix' => Media::TYPE_ARCHIVE,
        'zl' => Media::TYPE_ARCHIVE,
        'zoo' => Media::TYPE_ARCHIVE,
        'zpi' => Media::TYPE_ARCHIVE,
        'zz' => Media::TYPE_ARCHIVE,
        'pdf' => Media::TYPE_PDF,
        'dae' => Media::TYPE_MODEL,
        'obj' => Media::TYPE_MODEL,
        'fbx' => Media::TYPE_MODEL,
        'spx' => Media::TYPE_MODEL,
        '3ds' => Media::TYPE_MODEL,
        '3mf' => Media::TYPE_MODEL,
        'blend' => Media::TYPE_MODEL,
        'awd' => Media::TYPE_MODEL,
        'ply' => Media::TYPE_MODEL,
        'pcd' => Media::TYPE_MODEL,
        'stl' => Media::TYPE_MODEL,
        'skp' => Media::TYPE_MODEL
    );

    /**
     * Unique identifier
     * @var integer $id
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Id of the assigned album
     * @var integer $albumId
     * @ORM\Column(name="albumID", type="integer", nullable=false)
     */
    private $albumId;

    /**
     * Name of the media, also used as a file name
     * @var string $name
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Description for the media.
     * @var string $description
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * Path of the uploaded file.
     * @var string $path
     * @ORM\Column(name="path", type="string", length=255, nullable=false)
     */
    private $path;

    /**
     * Flag for the media type.
     * @var string $type
     * @ORM\Column(name="type", type="string", length=50, nullable=false)
     */
    private $type;

    /**
     * Extension of the uploaded file
     * @var string $extension
     * @ORM\Column(name="extension", type="string", length=20, nullable=false)
     */
    private $extension;

    /**
     * Id of the user, who uploaded the file.
     * @var integer $userId
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $userId;

    /**
     * Creation date of the media
     * @var \DateTime $created
     * @ORM\Column(name="created", type="date", nullable=false)
     */
    private $created;

    /**
     * Internal container for the uploaded file.
     * @var UploadedFile
     */
    private $file;

    /**
    * Filesize of the file in bytes
    * @var integer $filesize
    * @ORM\Column(name="file_size", type="integer", nullable=false)
    */
    private $fileSize;

    /**
    * Width of the file in px if it's an image
    * @var integer $width
    * @ORM\Column(name="width", type="integer", nullable=true)
    */
    private $width;

    /**
    * Height of the file in px if it's an image
    * @var integer $height
    * @ORM\Column(name="height", type="integer", nullable=true)
    */
    private $height;

    /**
     * Assigned album association. Is automatically loaded when the standard functions "find" ... be used,
     * or if the Query Builder is specified with the association.
     * @var \Shopware\Models\Media\Album
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Media\Album", inversedBy="media")
     * @ORM\JoinColumn(name="albumID", referencedColumnName="id")
     */
    private $album;

    /**
     * Contains the thumbnails paths.
     * Contains all created thumbnails
     * @var array
     */
    private $thumbnails;

    /**
     * Contains the high dpi thumbnails paths.
     * @var array
     */
    private $highDpiThumbnails;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Media", mappedBy="media", orphanRemoval=true, cascade={"persist"})
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

    /****************************************************************
     *                  Property Getter & Setter                    *
     ****************************************************************/

    /**
     * Returns the identifier "id"
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the assigned album.
     * @param integer $albumId
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
     * @return integer
     */
    public function getAlbumId()
    {
        return $this->albumId;
    }

    /**
     * Sets the name of the media, also used as file name
     * @param string $name
     * @return \Shopware\Models\Media\Media
     */
    public function setName($name)
    {
        $this->name = $this->removeSpecialCharacters($name);
        return $this;
    }

    /**
     * Returns the name of the media, also used as file name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the description of the media.
     * @param string $description
     * @return \Shopware\Models\Media\Media
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Returns the media description.
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the file path of the media.
     * @param string $path
     * @return \Shopware\Models\Media\Media
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Returns the file path of the media
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the internal type of the media.
     * @param string $type
     * @return \Shopware\Models\Media\Media
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the media type.
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the file extension.
     * @param string $extension
     * @return \Shopware\Models\Media\Media
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * Returns the file extension.
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Sets the id of the user, who uploaded the file.
     * @param integer $userId
     * @return \Shopware\Models\Media\Media
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Returns the id of the user, who uploaded the file.
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the creation date of the media.
     * @param \DateTime $created
     * @return \Shopware\Models\Media\Media
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Returns the creation date of the media.
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets the memory size of the file.
     * @param float $fileSize
     * @return \Shopware\Models\Media\Media
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    /**
     * Returns the filesize of the file in bytes.
     * @return integer
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Returns the filesize of the file in human readable format
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
     * @return \Shopware\Models\Media\Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Sets the assigned album.
     * @param  $album
     * @return \Shopware\Models\Media\Media
     */
    public function setAlbum(Album $album)
    {
        $this->album = $album;
        return $this;
    }

    /**
     * Returns the file
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Setter method for the file property. If the file is set, the file information will be extracted
     * and set into the internal properties.
     * @param  $file \Symfony\Component\HttpFoundation\File\File
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
     * @ORM\PostUpdate
     */
    public function onUpdate()
    {
        //returns a change set for the model, which contains all changed properties with the old and new value.
        $changeSet = Shopware()->Models()->getUnitOfWork()->getEntityChangeSet($this);

        $isNameChanged  = isset($changeSet['name']) && $changeSet['name'][0] !== $changeSet['name'][1];
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
            $mediaService = Shopware()->Container()->get('shopware_media.media_service');
            $newName = $this->getFileName();
            $newPath = $this->getUploadDir() . $newName;

            //rename the file
            $mediaService->rename($this->path, $newPath);

            $newPath = str_replace(Shopware()->DocPath(), '', $newPath);

            //set the new path to save it.
            $this->path = $newPath;
        }
    }

    /**
     * Model event function, which called when the model is loaded.
     * @ORM\PostLoad
     */
    public function onLoad()
    {
        $this->thumbnails = $this->loadThumbnails();
    }

    /**
     * Internal helper function which updates all associated data which has the image path as own property.
     * @return void
     * @internal param $name
     */
    private function updateAssociations()
    {
        /** @var $article \Shopware\Models\Article\Image*/
        foreach ($this->articles as $article) {
            $article->setPath($this->getName());
            Shopware()->Models()->persist($article);
        }
        Shopware()->Models()->flush();
    }

    /**
     * Removes the media files from the file system
     * @ORM\PostRemove
     */
    public function onRemove()
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
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
     * @return void
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
        $sizes[]= $defaultSize;

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
     * @param       $thumbnailSizes
     * @param       $fileName
     */
    public function removeAlbumThumbnails($thumbnailSizes, $fileName)
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }
        if ($thumbnailSizes === null || empty($thumbnailSizes)) {
            return;
        }

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

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
     * @return bool|string
     */
    public function getFileName()
    {
        if ($this->name !== '') {
            return $this->removeSpecialCharacters($this->name) . '.' . $this->extension;
        } else {
            // do whatever you want to generate a unique name
            return uniqid() . '.' . $this->extension;
        }
    }

    /****************************************************************
     *                  Internal functions                          *
     ****************************************************************/

    /**
     * Moves the uploaded file to the correctly directory.
     * @return bool
     */
    private function uploadFile()
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        //move the file to the upload directory
        if ($this->file !== null) {
            //file already exists?
            if ($mediaService->has($this->getPath())) {
                $this->name = $this->name . uniqid();
                // Path in setFileInfo is set, before the file gets a unique ID here
                // Therefore the path is updated here SW-2889
                $this->path = str_replace(Shopware()->DocPath(), '', $this->getUploadDir() . $this->getFileName());

                /**
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
        $generator = Shopware()->Container()->get('thumbnail_manager');

        $generator->createMediaThumbnail($this, $this->defaultThumbnails, true);
    }

    /**
     * Removes the default thumbnail files. The file name have to be passed, because on update the internal
     * file name property is already changed to the new name.
     * @param $fileName
     */
    private function removeDefaultThumbnails($fileName)
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

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
     * Loads the thumbnails paths via the configured thumbnail sizes.
     * @param bool $highDpi - If true, loads high dpi thumbnails instead
     * @return array
     */
    public function loadThumbnails($highDpi = false)
    {
        $thumbnails = $this->getThumbnailFilePaths($highDpi);
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

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
     * @return array
     */
    public function getThumbnailFilePaths($highDpi = false)
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return array();
        }
        $sizes = array();

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
        $thumbnails = array();
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
            $path = str_replace(Shopware()->DocPath(), '', $path);
            if (DIRECTORY_SEPARATOR !== '/') {
                $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
            }
            $thumbnails[$size] = $path;
        }

        return $thumbnails;
    }

    /**
     * Returns the directory to upload
     * @return string
     */
    private function getUploadDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return Shopware()->DocPath('media_' . strtolower($this->type));
    }

    /**
     * Returns the directory of the thumbnail files.
     * @return string
     */
    private function getThumbnailDir()
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        $path = $this->getUploadDir() . 'thumbnail' . DIRECTORY_SEPARATOR;
        $path = $mediaService->normalize($path);

        return $path;
    }

    /**
     * Create a thumbnail file for the internal file with the passed width and height.
     * @param $width
     * @param $height
     * @return bool
     */
    private function createThumbnail($width, $height)
    {
        //create only thumbnails for image media
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        /** @var \Shopware\Components\Thumbnail\Manager $manager */
        $manager = Shopware()->Container()->get('thumbnail_manager');

        $newSize = array(
            'width' => $width,
            'height' => $height
        );

        $manager->createMediaThumbnail($this, array($newSize), true);
    }

    /**
     * Create the new names for the jpg file and the file with the original extension
     * Also returns high dpi paths
     * @param $suffix
     * @param $fileName
     * @return array
     */
    private function getThumbnailNames($suffix, $fileName)
    {
        $jpgName = str_replace('.' . $this->extension, '_' . $suffix . '.jpg', $fileName);
        $jpgHDName = str_replace('.' . $this->extension, '_' . $suffix . '@2x.jpg', $fileName);
        $originalName = str_replace('.' . $this->extension, '_' . $suffix . '.' . $this->extension, $fileName);
        $originalHDName = str_replace('.' . $this->extension, '_' . $suffix . '@2x.' . $this->extension, $fileName);

        return array(
            'jpg' => $this->getThumbnailDir() . $jpgName,
            'jpgHD' => $this->getThumbnailDir() . $jpgHDName,
            'original' => $this->getThumbnailDir() . $originalName,
            'originalHD' => $this->getThumbnailDir() . $originalHDName
        );
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

    /**
     * Calculate image proportion and set the new resolution
     * @param $originalSize
     * @param $width
     * @param $height
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

        return array(
            'width' => $dstWidth,
            'height' => $dstHeight,
            'proportion' => $factor
        );
    }

    /**
     * Creates the image resource
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
            $fileInfo  = pathinfo($this->file->getClientOriginalName());
            $extension = $fileInfo['extension'];
            $name      = $fileInfo['filename'];
        } else {
            $extension = $this->file->guessExtension();
            $name      = $this->file->getBasename();
        }

        // make sure that the name don't contains the file extension.
        $name = str_ireplace('.' . $extension, '', $name);
        if ($extension === 'jpeg') {
            $name = str_ireplace('.jpg', '', $name);
        }

        //set the file type using the type mapping
        if (array_key_exists(strtolower($extension), $this->typeMapping)) {
            $this->type = $this->typeMapping[strtolower($extension)];
        } else {
            $this->type = self::TYPE_UNKNOWN;
        }

        // The filesize in bytes.
        $this->fileSize  = $this->file->getSize();
        $this->name      = $this->removeSpecialCharacters($name);
        $this->extension = str_replace('jpeg', 'jpg', $extension);
        $this->path = str_replace(Shopware()->DocPath(), '', $this->getUploadDir() . $this->getFileName());

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

    /**
     * Searches all album settings for thumbnail sizes
     *
     * @return array
     */
    private function getAllThumbnailSizes()
    {
        $joinedSizes = Shopware()->Container()->get('dbal_connection')
            ->query('SELECT DISTINCT thumbnail_size FROM s_media_album_settings WHERE thumbnail_size != ""')
            ->fetchAll(\PDO::FETCH_COLUMN);

        $sizes = [];
        foreach ($joinedSizes as $sizeItem) {
            $explodedSizes = explode(";", $sizeItem);
            if (empty($explodedSizes)) {
                continue;
            }

            $sizes = array_merge($sizes, array_flip($explodedSizes));
        }

        return array_keys($sizes);
    }
}
