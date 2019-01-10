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

class Migrations_Migration1450 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $newCountries = [
            'BG' => ['countryName' => 'Bulgarien', 'areaID' => 3, 'countryNameEng' => 'Bulgaria', 'countryIso' => 'BG', 'countryEn' => 'BULGARIA', 'iso3' => 'BGR'],
            'EE' => ['countryName' => 'Estland', 'areaID' => 3, 'countryNameEng' => 'Estonia', 'countryIso' => 'EE', 'countryEn' => 'ESTONIA', 'iso3' => 'EST'],
            'HR' => ['countryName' => 'Kroatien', 'areaID' => 3, 'countryNameEng' => 'Croatia', 'countryIso' => 'HR', 'countryEn' => 'CROATIA', 'iso3' => 'HRV'],
            'LV' => ['countryName' => 'Lettland', 'areaID' => 3, 'countryNameEng' => 'Latvia', 'countryIso' => 'LV', 'countryEn' => 'LATVIA', 'iso3' => 'LVA'],
            'LT' => ['countryName' => 'Litauen', 'areaID' => 3, 'countryNameEng' => 'Lithuania', 'countryIso' => 'LT', 'countryEn' => 'LITHUANIA', 'iso3' => 'LTU'],
            'MT' => ['countryName' => 'Malta', 'areaID' => 3, 'countryNameEng' => 'Malta', 'countryIso' => 'MT', 'countryEn' => 'MALTA', 'iso3' => 'MLT'],
            'SI' => ['countryName' => 'Slowenien', 'areaID' => 3, 'countryNameEng' => 'Slovenia', 'countryIso' => 'SI', 'countryEn' => 'SLOVENIA', 'iso3' => 'SVN'],
            'CY' => ['countryName' => 'Zypern', 'areaID' => 3, 'countryNameEng' => 'Cyprus', 'countryIso' => 'CY', 'countryEn' => 'CYPRUS', 'iso3' => 'CYP'],
            'AF' => ['countryName' => 'Afghanistan', 'areaID' => 2, 'countryNameEng' => 'Afghanistan', 'countryIso' => 'AF', 'countryEn' => 'AFGHANISTAN', 'iso3' => 'AFG'],
            'AX' => ['countryName' => 'Åland', 'areaID' => 2, 'countryNameEng' => 'Åland Islands', 'countryIso' => 'AX', 'countryEn' => 'ÅLAND ISLANDS', 'iso3' => 'ALA'],
            'AL' => ['countryName' => 'Albanien', 'areaID' => 2, 'countryNameEng' => 'Albania', 'countryIso' => 'AL', 'countryEn' => 'ALBANIA', 'iso3' => 'ALB'],
            'DZ' => ['countryName' => 'Algerien', 'areaID' => 2, 'countryNameEng' => 'Algeria', 'countryIso' => 'DZ', 'countryEn' => 'ALGERIA', 'iso3' => 'DZA'],
            'AS' => ['countryName' => 'Amerikanisch-Samoa', 'areaID' => 2, 'countryNameEng' => 'American Samoa', 'countryIso' => 'AS', 'countryEn' => 'AMERICAN SAMOA', 'iso3' => 'ASM'],
            'AD' => ['countryName' => 'Andorra', 'areaID' => 2, 'countryNameEng' => 'Andorra', 'countryIso' => 'AD', 'countryEn' => 'ANDORRA', 'iso3' => 'AND'],
            'AO' => ['countryName' => 'Angola', 'areaID' => 2, 'countryNameEng' => 'Angola', 'countryIso' => 'AO', 'countryEn' => 'ANGOLA', 'iso3' => 'AGO'],
            'AI' => ['countryName' => 'Anguilla', 'areaID' => 2, 'countryNameEng' => 'Anguilla', 'countryIso' => 'AI', 'countryEn' => 'ANGUILLA', 'iso3' => 'AIA'],
            'AQ' => ['countryName' => 'Antarktika', 'areaID' => 2, 'countryNameEng' => 'Antarctica', 'countryIso' => 'AQ', 'countryEn' => 'ANTARCTICA', 'iso3' => 'ATA'],
            'AG' => ['countryName' => 'Antigua und Barbuda', 'areaID' => 2, 'countryNameEng' => 'Antigua and Barbuda', 'countryIso' => 'AG', 'countryEn' => 'ANTIGUA AND BARBUDA', 'iso3' => 'ATG'],
            'AR' => ['countryName' => 'Argentinien', 'areaID' => 2, 'countryNameEng' => 'Argentina', 'countryIso' => 'AR', 'countryEn' => 'ARGENTINA', 'iso3' => 'ARG'],
            'AM' => ['countryName' => 'Armenien', 'areaID' => 2, 'countryNameEng' => 'Armenia', 'countryIso' => 'AM', 'countryEn' => 'ARMENIA', 'iso3' => 'ARM'],
            'AW' => ['countryName' => 'Aruba', 'areaID' => 2, 'countryNameEng' => 'Aruba', 'countryIso' => 'AW', 'countryEn' => 'ARUBA', 'iso3' => 'ABW'],
            'AZ' => ['countryName' => 'Aserbaidschan', 'areaID' => 2, 'countryNameEng' => 'Azerbaijan', 'countryIso' => 'AZ', 'countryEn' => 'AZERBAIJAN', 'iso3' => 'AZE'],
            'BS' => ['countryName' => 'Bahamas', 'areaID' => 2, 'countryNameEng' => 'Bahamas', 'countryIso' => 'BS', 'countryEn' => 'BAHAMAS', 'iso3' => 'BHS'],
            'BH' => ['countryName' => 'Bahrain', 'areaID' => 2, 'countryNameEng' => 'Bahrain', 'countryIso' => 'BH', 'countryEn' => 'BAHRAIN', 'iso3' => 'BHR'],
            'BD' => ['countryName' => 'Bangladesch', 'areaID' => 2, 'countryNameEng' => 'Bangladesh', 'countryIso' => 'BD', 'countryEn' => 'BANGLADESH', 'iso3' => 'BGD'],
            'BB' => ['countryName' => 'Barbados', 'areaID' => 2, 'countryNameEng' => 'Barbados', 'countryIso' => 'BB', 'countryEn' => 'BARBADOS', 'iso3' => 'BRB'],
            'BY' => ['countryName' => 'Weißrussland', 'areaID' => 2, 'countryNameEng' => 'Belarus', 'countryIso' => 'BY', 'countryEn' => 'BELARUS', 'iso3' => 'BLR'],
            'BZ' => ['countryName' => 'Belize', 'areaID' => 2, 'countryNameEng' => 'Belize', 'countryIso' => 'BZ', 'countryEn' => 'BELIZE', 'iso3' => 'BLZ'],
            'BJ' => ['countryName' => 'Benin', 'areaID' => 2, 'countryNameEng' => 'Benin', 'countryIso' => 'BJ', 'countryEn' => 'BENIN', 'iso3' => 'BEN'],
            'BM' => ['countryName' => 'Bermuda', 'areaID' => 2, 'countryNameEng' => 'Bermuda', 'countryIso' => 'BM', 'countryEn' => 'BERMUDA', 'iso3' => 'BMU'],
            'BT' => ['countryName' => 'Bhutan', 'areaID' => 2, 'countryNameEng' => 'Bhutan', 'countryIso' => 'BT', 'countryEn' => 'BHUTAN', 'iso3' => 'BTN'],
            'BO' => ['countryName' => 'Bolivien', 'areaID' => 2, 'countryNameEng' => 'Bolivia (Plurinational State of)', 'countryIso' => 'BO', 'countryEn' => 'BOLIVIA (PLURINATIONAL STATE OF)', 'iso3' => 'BOL'],
            'BQ' => ['countryName' => 'Bonaire, Sint Eustatius und Saba', 'areaID' => 2, 'countryNameEng' => 'Bonaire, Sint Eustatius and Saba', 'countryIso' => 'BQ', 'countryEn' => 'BONAIRE, SINT EUSTATIUS AND SABA', 'iso3' => 'BES'],
            'BA' => ['countryName' => 'Bosnien und Herzegowina', 'areaID' => 2, 'countryNameEng' => 'Bosnia and Herzegovina', 'countryIso' => 'BA', 'countryEn' => 'BOSNIA AND HERZEGOVINA', 'iso3' => 'BIH'],
            'BW' => ['countryName' => 'Botswana', 'areaID' => 2, 'countryNameEng' => 'Botswana', 'countryIso' => 'BW', 'countryEn' => 'BOTSWANA', 'iso3' => 'BWA'],
            'BV' => ['countryName' => 'Bouvetinsel', 'areaID' => 2, 'countryNameEng' => 'Bouvet Island', 'countryIso' => 'BV', 'countryEn' => 'BOUVET ISLAND', 'iso3' => 'BVT'],
            'IO' => ['countryName' => 'Britisches Territorium im Indischen Ozean', 'areaID' => 2, 'countryNameEng' => 'British Indian Ocean Territory', 'countryIso' => 'IO', 'countryEn' => 'BRITISH INDIAN OCEAN TERRITORY', 'iso3' => 'IOT'],
            'UM' => ['countryName' => 'Kleinere Inselbesitzungen der Vereinigten Staaten', 'areaID' => 2, 'countryNameEng' => 'United States Minor Outlying Islands', 'countryIso' => 'UM', 'countryEn' => 'UNITED STATES MINOR OUTLYING ISLANDS', 'iso3' => 'UMI'],
            'VG' => ['countryName' => 'Britische Jungferninseln', 'areaID' => 2, 'countryNameEng' => 'Virgin Islands (British)', 'countryIso' => 'VG', 'countryEn' => 'VIRGIN ISLANDS (BRITISH)', 'iso3' => 'VGB'],
            'VI' => ['countryName' => 'Amerikanische Jungferninseln', 'areaID' => 2, 'countryNameEng' => 'Virgin Islands (U.S.)', 'countryIso' => 'VI', 'countryEn' => 'VIRGIN ISLANDS (U.S.)', 'iso3' => 'VIR'],
            'BN' => ['countryName' => 'Brunei', 'areaID' => 2, 'countryNameEng' => 'Brunei Darussalam', 'countryIso' => 'BN', 'countryEn' => 'BRUNEI DARUSSALAM', 'iso3' => 'BRN'],
            'BF' => ['countryName' => 'Burkina Faso', 'areaID' => 2, 'countryNameEng' => 'Burkina Faso', 'countryIso' => 'BF', 'countryEn' => 'BURKINA FASO', 'iso3' => 'BFA'],
            'BI' => ['countryName' => 'Burundi', 'areaID' => 2, 'countryNameEng' => 'Burundi', 'countryIso' => 'BI', 'countryEn' => 'BURUNDI', 'iso3' => 'BDI'],
            'KH' => ['countryName' => 'Kambodscha', 'areaID' => 2, 'countryNameEng' => 'Cambodia', 'countryIso' => 'KH', 'countryEn' => 'CAMBODIA', 'iso3' => 'KHM'],
            'CM' => ['countryName' => 'Kamerun', 'areaID' => 2, 'countryNameEng' => 'Cameroon', 'countryIso' => 'CM', 'countryEn' => 'CAMEROON', 'iso3' => 'CMR'],
            'CV' => ['countryName' => 'Kap Verde', 'areaID' => 2, 'countryNameEng' => 'Cabo Verde', 'countryIso' => 'CV', 'countryEn' => 'CABO VERDE', 'iso3' => 'CPV'],
            'KY' => ['countryName' => 'Kaimaninseln', 'areaID' => 2, 'countryNameEng' => 'Cayman Islands', 'countryIso' => 'KY', 'countryEn' => 'CAYMAN ISLANDS', 'iso3' => 'CYM'],
            'CF' => ['countryName' => 'Zentralafrikanische Republik', 'areaID' => 2, 'countryNameEng' => 'Central African Republic', 'countryIso' => 'CF', 'countryEn' => 'CENTRAL AFRICAN REPUBLIC', 'iso3' => 'CAF'],
            'TD' => ['countryName' => 'Tschad', 'areaID' => 2, 'countryNameEng' => 'Chad', 'countryIso' => 'TD', 'countryEn' => 'CHAD', 'iso3' => 'TCD'],
            'CL' => ['countryName' => 'Chile', 'areaID' => 2, 'countryNameEng' => 'Chile', 'countryIso' => 'CL', 'countryEn' => 'CHILE', 'iso3' => 'CHL'],
            'CN' => ['countryName' => 'China', 'areaID' => 2, 'countryNameEng' => 'China', 'countryIso' => 'CN', 'countryEn' => 'CHINA', 'iso3' => 'CHN'],
            'CX' => ['countryName' => 'Weihnachtsinsel', 'areaID' => 2, 'countryNameEng' => 'Christmas Island', 'countryIso' => 'CX', 'countryEn' => 'CHRISTMAS ISLAND', 'iso3' => 'CXR'],
            'CC' => ['countryName' => 'Kokosinseln', 'areaID' => 2, 'countryNameEng' => 'Cocos (Keeling) Islands', 'countryIso' => 'CC', 'countryEn' => 'COCOS (KEELING) ISLANDS', 'iso3' => 'CCK'],
            'CO' => ['countryName' => 'Kolumbien', 'areaID' => 2, 'countryNameEng' => 'Colombia', 'countryIso' => 'CO', 'countryEn' => 'COLOMBIA', 'iso3' => 'COL'],
            'KM' => ['countryName' => 'Union der Komoren', 'areaID' => 2, 'countryNameEng' => 'Comoros', 'countryIso' => 'KM', 'countryEn' => 'COMOROS', 'iso3' => 'COM'],
            'CG' => ['countryName' => 'Kongo', 'areaID' => 2, 'countryNameEng' => 'Congo', 'countryIso' => 'CG', 'countryEn' => 'CONGO', 'iso3' => 'COG'],
            'CD' => ['countryName' => 'Kongo (Dem. Rep.)', 'areaID' => 2, 'countryNameEng' => 'Congo (Democratic Republic of the)', 'countryIso' => 'CD', 'countryEn' => 'CONGO (DEMOCRATIC REPUBLIC OF THE)', 'iso3' => 'COD'],
            'CK' => ['countryName' => 'Cookinseln', 'areaID' => 2, 'countryNameEng' => 'Cook Islands', 'countryIso' => 'CK', 'countryEn' => 'COOK ISLANDS', 'iso3' => 'COK'],
            'CR' => ['countryName' => 'Costa Rica', 'areaID' => 2, 'countryNameEng' => 'Costa Rica', 'countryIso' => 'CR', 'countryEn' => 'COSTA RICA', 'iso3' => 'CRI'],
            'CU' => ['countryName' => 'Kuba', 'areaID' => 2, 'countryNameEng' => 'Cuba', 'countryIso' => 'CU', 'countryEn' => 'CUBA', 'iso3' => 'CUB'],
            'CW' => ['countryName' => 'Curaçao', 'areaID' => 2, 'countryNameEng' => 'Curaçao', 'countryIso' => 'CW', 'countryEn' => 'CURAÇAO', 'iso3' => 'CUW'],
            'DJ' => ['countryName' => 'Dschibuti', 'areaID' => 2, 'countryNameEng' => 'Djibouti', 'countryIso' => 'DJ', 'countryEn' => 'DJIBOUTI', 'iso3' => 'DJI'],
            'DM' => ['countryName' => 'Dominica', 'areaID' => 2, 'countryNameEng' => 'Dominica', 'countryIso' => 'DM', 'countryEn' => 'DOMINICA', 'iso3' => 'DMA'],
            'DO' => ['countryName' => 'Dominikanische Republik', 'areaID' => 2, 'countryNameEng' => 'Dominican Republic', 'countryIso' => 'DO', 'countryEn' => 'DOMINICAN REPUBLIC', 'iso3' => 'DOM'],
            'EC' => ['countryName' => 'Ecuador', 'areaID' => 2, 'countryNameEng' => 'Ecuador', 'countryIso' => 'EC', 'countryEn' => 'ECUADOR', 'iso3' => 'ECU'],
            'EG' => ['countryName' => 'Ägypten', 'areaID' => 2, 'countryNameEng' => 'Egypt', 'countryIso' => 'EG', 'countryEn' => 'EGYPT', 'iso3' => 'EGY'],
            'SV' => ['countryName' => 'El Salvador', 'areaID' => 2, 'countryNameEng' => 'El Salvador', 'countryIso' => 'SV', 'countryEn' => 'EL SALVADOR', 'iso3' => 'SLV'],
            'GQ' => ['countryName' => 'Äquatorial-Guinea', 'areaID' => 2, 'countryNameEng' => 'Equatorial Guinea', 'countryIso' => 'GQ', 'countryEn' => 'EQUATORIAL GUINEA', 'iso3' => 'GNQ'],
            'ER' => ['countryName' => 'Eritrea', 'areaID' => 2, 'countryNameEng' => 'Eritrea', 'countryIso' => 'ER', 'countryEn' => 'ERITREA', 'iso3' => 'ERI'],
            'ET' => ['countryName' => 'Äthiopien', 'areaID' => 2, 'countryNameEng' => 'Ethiopia', 'countryIso' => 'ET', 'countryEn' => 'ETHIOPIA', 'iso3' => 'ETH'],
            'FK' => ['countryName' => 'Falklandinseln', 'areaID' => 2, 'countryNameEng' => 'Falkland Islands (Malvinas)', 'countryIso' => 'FK', 'countryEn' => 'FALKLAND ISLANDS (MALVINAS)', 'iso3' => 'FLK'],
            'FO' => ['countryName' => 'Färöer-Inseln', 'areaID' => 2, 'countryNameEng' => 'Faroe Islands', 'countryIso' => 'FO', 'countryEn' => 'FAROE ISLANDS', 'iso3' => 'FRO'],
            'FJ' => ['countryName' => 'Fidschi', 'areaID' => 2, 'countryNameEng' => 'Fiji', 'countryIso' => 'FJ', 'countryEn' => 'FIJI', 'iso3' => 'FJI'],
            'GF' => ['countryName' => 'Französisch Guyana', 'areaID' => 2, 'countryNameEng' => 'French Guiana', 'countryIso' => 'GF', 'countryEn' => 'FRENCH GUIANA', 'iso3' => 'GUF'],
            'PF' => ['countryName' => 'Französisch-Polynesien', 'areaID' => 2, 'countryNameEng' => 'French Polynesia', 'countryIso' => 'PF', 'countryEn' => 'FRENCH POLYNESIA', 'iso3' => 'PYF'],
            'TF' => ['countryName' => 'Französische Süd- und Antarktisgebiete', 'areaID' => 2, 'countryNameEng' => 'French Southern Territories', 'countryIso' => 'TF', 'countryEn' => 'FRENCH SOUTHERN TERRITORIES', 'iso3' => 'ATF'],
            'GA' => ['countryName' => 'Gabun', 'areaID' => 2, 'countryNameEng' => 'Gabon', 'countryIso' => 'GA', 'countryEn' => 'GABON', 'iso3' => 'GAB'],
            'GM' => ['countryName' => 'Gambia', 'areaID' => 2, 'countryNameEng' => 'Gambia', 'countryIso' => 'GM', 'countryEn' => 'GAMBIA', 'iso3' => 'GMB'],
            'GE' => ['countryName' => 'Georgien', 'areaID' => 2, 'countryNameEng' => 'Georgia', 'countryIso' => 'GE', 'countryEn' => 'GEORGIA', 'iso3' => 'GEO'],
            'GH' => ['countryName' => 'Ghana', 'areaID' => 2, 'countryNameEng' => 'Ghana', 'countryIso' => 'GH', 'countryEn' => 'GHANA', 'iso3' => 'GHA'],
            'GI' => ['countryName' => 'Gibraltar', 'areaID' => 2, 'countryNameEng' => 'Gibraltar', 'countryIso' => 'GI', 'countryEn' => 'GIBRALTAR', 'iso3' => 'GIB'],
            'GL' => ['countryName' => 'Grönland', 'areaID' => 2, 'countryNameEng' => 'Greenland', 'countryIso' => 'GL', 'countryEn' => 'GREENLAND', 'iso3' => 'GRL'],
            'GD' => ['countryName' => 'Grenada', 'areaID' => 2, 'countryNameEng' => 'Grenada', 'countryIso' => 'GD', 'countryEn' => 'GRENADA', 'iso3' => 'GRD'],
            'GP' => ['countryName' => 'Guadeloupe', 'areaID' => 2, 'countryNameEng' => 'Guadeloupe', 'countryIso' => 'GP', 'countryEn' => 'GUADELOUPE', 'iso3' => 'GLP'],
            'GU' => ['countryName' => 'Guam', 'areaID' => 2, 'countryNameEng' => 'Guam', 'countryIso' => 'GU', 'countryEn' => 'GUAM', 'iso3' => 'GUM'],
            'GT' => ['countryName' => 'Guatemala', 'areaID' => 2, 'countryNameEng' => 'Guatemala', 'countryIso' => 'GT', 'countryEn' => 'GUATEMALA', 'iso3' => 'GTM'],
            'GG' => ['countryName' => 'Guernsey', 'areaID' => 2, 'countryNameEng' => 'Guernsey', 'countryIso' => 'GG', 'countryEn' => 'GUERNSEY', 'iso3' => 'GGY'],
            'GN' => ['countryName' => 'Guinea', 'areaID' => 2, 'countryNameEng' => 'Guinea', 'countryIso' => 'GN', 'countryEn' => 'GUINEA', 'iso3' => 'GIN'],
            'GW' => ['countryName' => 'Guinea-Bissau', 'areaID' => 2, 'countryNameEng' => 'Guinea-Bissau', 'countryIso' => 'GW', 'countryEn' => 'GUINEA-BISSAU', 'iso3' => 'GNB'],
            'GY' => ['countryName' => 'Guyana', 'areaID' => 2, 'countryNameEng' => 'Guyana', 'countryIso' => 'GY', 'countryEn' => 'GUYANA', 'iso3' => 'GUY'],
            'HT' => ['countryName' => 'Haiti', 'areaID' => 2, 'countryNameEng' => 'Haiti', 'countryIso' => 'HT', 'countryEn' => 'HAITI', 'iso3' => 'HTI'],
            'HM' => ['countryName' => 'Heard und die McDonaldinseln', 'areaID' => 2, 'countryNameEng' => 'Heard Island and McDonald Islands', 'countryIso' => 'HM', 'countryEn' => 'HEARD ISLAND AND MCDONALD ISLANDS', 'iso3' => 'HMD'],
            'VA' => ['countryName' => 'Heiliger Stuhl', 'areaID' => 2, 'countryNameEng' => 'Holy See', 'countryIso' => 'VA', 'countryEn' => 'HOLY SEE', 'iso3' => 'VAT'],
            'HN' => ['countryName' => 'Honduras', 'areaID' => 2, 'countryNameEng' => 'Honduras', 'countryIso' => 'HN', 'countryEn' => 'HONDURAS', 'iso3' => 'HND'],
            'HK' => ['countryName' => 'Hong Kong', 'areaID' => 2, 'countryNameEng' => 'Hong Kong', 'countryIso' => 'HK', 'countryEn' => 'HONG KONG', 'iso3' => 'HKG'],
            'IN' => ['countryName' => 'Indien', 'areaID' => 2, 'countryNameEng' => 'India', 'countryIso' => 'IN', 'countryEn' => 'INDIA', 'iso3' => 'IND'],
            'ID' => ['countryName' => 'Indonesien', 'areaID' => 2, 'countryNameEng' => 'Indonesia', 'countryIso' => 'ID', 'countryEn' => 'INDONESIA', 'iso3' => 'IDN'],
            'CI' => ['countryName' => 'Elfenbeinküste', 'areaID' => 2, 'countryNameEng' => 'Côte d\'Ivoire', 'countryIso' => 'CI', 'countryEn' => 'CÔTE D\'IVOIRE', 'iso3' => 'CIV'],
            'IR' => ['countryName' => 'Iran', 'areaID' => 2, 'countryNameEng' => 'Iran (Islamic Republic of)', 'countryIso' => 'IR', 'countryEn' => 'IRAN (ISLAMIC REPUBLIC OF)', 'iso3' => 'IRN'],
            'IQ' => ['countryName' => 'Irak', 'areaID' => 2, 'countryNameEng' => 'Iraq', 'countryIso' => 'IQ', 'countryEn' => 'IRAQ', 'iso3' => 'IRQ'],
            'IM' => ['countryName' => 'Insel Man', 'areaID' => 2, 'countryNameEng' => 'Isle of Man', 'countryIso' => 'IM', 'countryEn' => 'ISLE OF MAN', 'iso3' => 'IMN'],
            'JM' => ['countryName' => 'Jamaika', 'areaID' => 2, 'countryNameEng' => 'Jamaica', 'countryIso' => 'JM', 'countryEn' => 'JAMAICA', 'iso3' => 'JAM'],
            'JE' => ['countryName' => 'Jersey', 'areaID' => 2, 'countryNameEng' => 'Jersey', 'countryIso' => 'JE', 'countryEn' => 'JERSEY', 'iso3' => 'JEY'],
            'JO' => ['countryName' => 'Jordanien', 'areaID' => 2, 'countryNameEng' => 'Jordan', 'countryIso' => 'JO', 'countryEn' => 'JORDAN', 'iso3' => 'JOR'],
            'KZ' => ['countryName' => 'Kasachstan', 'areaID' => 2, 'countryNameEng' => 'Kazakhstan', 'countryIso' => 'KZ', 'countryEn' => 'KAZAKHSTAN', 'iso3' => 'KAZ'],
            'KE' => ['countryName' => 'Kenia', 'areaID' => 2, 'countryNameEng' => 'Kenya', 'countryIso' => 'KE', 'countryEn' => 'KENYA', 'iso3' => 'KEN'],
            'KI' => ['countryName' => 'Kiribati', 'areaID' => 2, 'countryNameEng' => 'Kiribati', 'countryIso' => 'KI', 'countryEn' => 'KIRIBATI', 'iso3' => 'KIR'],
            'KW' => ['countryName' => 'Kuwait', 'areaID' => 2, 'countryNameEng' => 'Kuwait', 'countryIso' => 'KW', 'countryEn' => 'KUWAIT', 'iso3' => 'KWT'],
            'KG' => ['countryName' => 'Kirgisistan', 'areaID' => 2, 'countryNameEng' => 'Kyrgyzstan', 'countryIso' => 'KG', 'countryEn' => 'KYRGYZSTAN', 'iso3' => 'KGZ'],
            'LA' => ['countryName' => 'Laos', 'areaID' => 2, 'countryNameEng' => 'Lao People\'s Democratic Republic', 'countryIso' => 'LA', 'countryEn' => 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC', 'iso3' => 'LAO'],
            'LB' => ['countryName' => 'Libanon', 'areaID' => 2, 'countryNameEng' => 'Lebanon', 'countryIso' => 'LB', 'countryEn' => 'LEBANON', 'iso3' => 'LBN'],
            'LS' => ['countryName' => 'Lesotho', 'areaID' => 2, 'countryNameEng' => 'Lesotho', 'countryIso' => 'LS', 'countryEn' => 'LESOTHO', 'iso3' => 'LSO'],
            'LR' => ['countryName' => 'Liberia', 'areaID' => 2, 'countryNameEng' => 'Liberia', 'countryIso' => 'LR', 'countryEn' => 'LIBERIA', 'iso3' => 'LBR'],
            'LY' => ['countryName' => 'Libyen', 'areaID' => 2, 'countryNameEng' => 'Libya', 'countryIso' => 'LY', 'countryEn' => 'LIBYA', 'iso3' => 'LBY'],
            'MO' => ['countryName' => 'Macao', 'areaID' => 2, 'countryNameEng' => 'Macao', 'countryIso' => 'MO', 'countryEn' => 'MACAO', 'iso3' => 'MAC'],
            'MK' => ['countryName' => 'Mazedonien', 'areaID' => 2, 'countryNameEng' => 'Macedonia (the former Yugoslav Republic of)', 'countryIso' => 'MK', 'countryEn' => 'MACEDONIA (THE FORMER YUGOSLAV REPUBLIC OF)', 'iso3' => 'MKD'],
            'MG' => ['countryName' => 'Madagaskar', 'areaID' => 2, 'countryNameEng' => 'Madagascar', 'countryIso' => 'MG', 'countryEn' => 'MADAGASCAR', 'iso3' => 'MDG'],
            'MW' => ['countryName' => 'Malawi', 'areaID' => 2, 'countryNameEng' => 'Malawi', 'countryIso' => 'MW', 'countryEn' => 'MALAWI', 'iso3' => 'MWI'],
            'MY' => ['countryName' => 'Malaysia', 'areaID' => 2, 'countryNameEng' => 'Malaysia', 'countryIso' => 'MY', 'countryEn' => 'MALAYSIA', 'iso3' => 'MYS'],
            'MV' => ['countryName' => 'Malediven', 'areaID' => 2, 'countryNameEng' => 'Maldives', 'countryIso' => 'MV', 'countryEn' => 'MALDIVES', 'iso3' => 'MDV'],
            'ML' => ['countryName' => 'Mali', 'areaID' => 2, 'countryNameEng' => 'Mali', 'countryIso' => 'ML', 'countryEn' => 'MALI', 'iso3' => 'MLI'],
            'MH' => ['countryName' => 'Marshallinseln', 'areaID' => 2, 'countryNameEng' => 'Marshall Islands', 'countryIso' => 'MH', 'countryEn' => 'MARSHALL ISLANDS', 'iso3' => 'MHL'],
            'MQ' => ['countryName' => 'Martinique', 'areaID' => 2, 'countryNameEng' => 'Martinique', 'countryIso' => 'MQ', 'countryEn' => 'MARTINIQUE', 'iso3' => 'MTQ'],
            'MR' => ['countryName' => 'Mauretanien', 'areaID' => 2, 'countryNameEng' => 'Mauritania', 'countryIso' => 'MR', 'countryEn' => 'MAURITANIA', 'iso3' => 'MRT'],
            'MU' => ['countryName' => 'Mauritius', 'areaID' => 2, 'countryNameEng' => 'Mauritius', 'countryIso' => 'MU', 'countryEn' => 'MAURITIUS', 'iso3' => 'MUS'],
            'YT' => ['countryName' => 'Mayotte', 'areaID' => 2, 'countryNameEng' => 'Mayotte', 'countryIso' => 'YT', 'countryEn' => 'MAYOTTE', 'iso3' => 'MYT'],
            'MX' => ['countryName' => 'Mexiko', 'areaID' => 2, 'countryNameEng' => 'Mexico', 'countryIso' => 'MX', 'countryEn' => 'MEXICO', 'iso3' => 'MEX'],
            'FM' => ['countryName' => 'Mikronesien', 'areaID' => 2, 'countryNameEng' => 'Micronesia (Federated States of)', 'countryIso' => 'FM', 'countryEn' => 'MICRONESIA (FEDERATED STATES OF)', 'iso3' => 'FSM'],
            'MD' => ['countryName' => 'Moldawie', 'areaID' => 2, 'countryNameEng' => 'Moldova (Republic of)', 'countryIso' => 'MD', 'countryEn' => 'MOLDOVA (REPUBLIC OF)', 'iso3' => 'MDA'],
            'MC' => ['countryName' => 'Monaco', 'areaID' => 2, 'countryNameEng' => 'Monaco', 'countryIso' => 'MC', 'countryEn' => 'MONACO', 'iso3' => 'MCO'],
            'MN' => ['countryName' => 'Mongolei', 'areaID' => 2, 'countryNameEng' => 'Mongolia', 'countryIso' => 'MN', 'countryEn' => 'MONGOLIA', 'iso3' => 'MNG'],
            'ME' => ['countryName' => 'Montenegro', 'areaID' => 2, 'countryNameEng' => 'Montenegro', 'countryIso' => 'ME', 'countryEn' => 'MONTENEGRO', 'iso3' => 'MNE'],
            'MS' => ['countryName' => 'Montserrat', 'areaID' => 2, 'countryNameEng' => 'Montserrat', 'countryIso' => 'MS', 'countryEn' => 'MONTSERRAT', 'iso3' => 'MSR'],
            'MA' => ['countryName' => 'Marokko', 'areaID' => 2, 'countryNameEng' => 'Morocco', 'countryIso' => 'MA', 'countryEn' => 'MOROCCO', 'iso3' => 'MAR'],
            'MZ' => ['countryName' => 'Mosambik', 'areaID' => 2, 'countryNameEng' => 'Mozambique', 'countryIso' => 'MZ', 'countryEn' => 'MOZAMBIQUE', 'iso3' => 'MOZ'],
            'MM' => ['countryName' => 'Myanmar', 'areaID' => 2, 'countryNameEng' => 'Myanmar', 'countryIso' => 'MM', 'countryEn' => 'MYANMAR', 'iso3' => 'MMR'],
            'NR' => ['countryName' => 'Nauru', 'areaID' => 2, 'countryNameEng' => 'Nauru', 'countryIso' => 'NR', 'countryEn' => 'NAURU', 'iso3' => 'NRU'],
            'NP' => ['countryName' => 'Népal', 'areaID' => 2, 'countryNameEng' => 'Nepal', 'countryIso' => 'NP', 'countryEn' => 'NEPAL', 'iso3' => 'NPL'],
            'NC' => ['countryName' => 'Neukaledonien', 'areaID' => 2, 'countryNameEng' => 'New Caledonia', 'countryIso' => 'NC', 'countryEn' => 'NEW CALEDONIA', 'iso3' => 'NCL'],
            'NZ' => ['countryName' => 'Neuseeland', 'areaID' => 2, 'countryNameEng' => 'New Zealand', 'countryIso' => 'NZ', 'countryEn' => 'NEW ZEALAND', 'iso3' => 'NZL'],
            'NI' => ['countryName' => 'Nicaragua', 'areaID' => 2, 'countryNameEng' => 'Nicaragua', 'countryIso' => 'NI', 'countryEn' => 'NICARAGUA', 'iso3' => 'NIC'],
            'NE' => ['countryName' => 'Niger', 'areaID' => 2, 'countryNameEng' => 'Niger', 'countryIso' => 'NE', 'countryEn' => 'NIGER', 'iso3' => 'NER'],
            'NG' => ['countryName' => 'Nigeria', 'areaID' => 2, 'countryNameEng' => 'Nigeria', 'countryIso' => 'NG', 'countryEn' => 'NIGERIA', 'iso3' => 'NGA'],
            'NU' => ['countryName' => 'Niue', 'areaID' => 2, 'countryNameEng' => 'Niue', 'countryIso' => 'NU', 'countryEn' => 'NIUE', 'iso3' => 'NIU'],
            'NF' => ['countryName' => 'Norfolkinsel', 'areaID' => 2, 'countryNameEng' => 'Norfolk Island', 'countryIso' => 'NF', 'countryEn' => 'NORFOLK ISLAND', 'iso3' => 'NFK'],
            'KP' => ['countryName' => 'Nordkorea', 'areaID' => 2, 'countryNameEng' => 'Korea (Democratic People\'s Republic of)', 'countryIso' => 'KP', 'countryEn' => 'KOREA (DEMOCRATIC PEOPLE\'S REPUBLIC OF)', 'iso3' => 'PRK'],
            'MP' => ['countryName' => 'Nördliche Marianen', 'areaID' => 2, 'countryNameEng' => 'Northern Mariana Islands', 'countryIso' => 'MP', 'countryEn' => 'NORTHERN MARIANA ISLANDS', 'iso3' => 'MNP'],
            'OM' => ['countryName' => 'Oman', 'areaID' => 2, 'countryNameEng' => 'Oman', 'countryIso' => 'OM', 'countryEn' => 'OMAN', 'iso3' => 'OMN'],
            'PK' => ['countryName' => 'Pakistan', 'areaID' => 2, 'countryNameEng' => 'Pakistan', 'countryIso' => 'PK', 'countryEn' => 'PAKISTAN', 'iso3' => 'PAK'],
            'PW' => ['countryName' => 'Palau', 'areaID' => 2, 'countryNameEng' => 'Palau', 'countryIso' => 'PW', 'countryEn' => 'PALAU', 'iso3' => 'PLW'],
            'PS' => ['countryName' => 'Palästina', 'areaID' => 2, 'countryNameEng' => 'Palestine, State of', 'countryIso' => 'PS', 'countryEn' => 'PALESTINE, STATE OF', 'iso3' => 'PSE'],
            'PA' => ['countryName' => 'Panama', 'areaID' => 2, 'countryNameEng' => 'Panama', 'countryIso' => 'PA', 'countryEn' => 'PANAMA', 'iso3' => 'PAN'],
            'PG' => ['countryName' => 'Papua-Neuguinea', 'areaID' => 2, 'countryNameEng' => 'Papua New Guinea', 'countryIso' => 'PG', 'countryEn' => 'PAPUA NEW GUINEA', 'iso3' => 'PNG'],
            'PY' => ['countryName' => 'Paraguay', 'areaID' => 2, 'countryNameEng' => 'Paraguay', 'countryIso' => 'PY', 'countryEn' => 'PARAGUAY', 'iso3' => 'PRY'],
            'PE' => ['countryName' => 'Peru', 'areaID' => 2, 'countryNameEng' => 'Peru', 'countryIso' => 'PE', 'countryEn' => 'PERU', 'iso3' => 'PER'],
            'PH' => ['countryName' => 'Philippinen', 'areaID' => 2, 'countryNameEng' => 'Philippines', 'countryIso' => 'PH', 'countryEn' => 'PHILIPPINES', 'iso3' => 'PHL'],
            'PN' => ['countryName' => 'Pitcairn', 'areaID' => 2, 'countryNameEng' => 'Pitcairn', 'countryIso' => 'PN', 'countryEn' => 'PITCAIRN', 'iso3' => 'PCN'],
            'PR' => ['countryName' => 'Puerto Rico', 'areaID' => 2, 'countryNameEng' => 'Puerto Rico', 'countryIso' => 'PR', 'countryEn' => 'PUERTO RICO', 'iso3' => 'PRI'],
            'QA' => ['countryName' => 'Katar', 'areaID' => 2, 'countryNameEng' => 'Qatar', 'countryIso' => 'QA', 'countryEn' => 'QATAR', 'iso3' => 'QAT'],
            'XK' => ['countryName' => 'Republic of Kosovo', 'areaID' => 2, 'countryNameEng' => 'Republic of Kosovo', 'countryIso' => 'XK', 'countryEn' => 'REPUBLIC OF KOSOVO', 'iso3' => 'KOS'],
            'RE' => ['countryName' => 'Réunion', 'areaID' => 2, 'countryNameEng' => 'Réunion', 'countryIso' => 'RE', 'countryEn' => 'RÉUNION', 'iso3' => 'REU'],
            'RU' => ['countryName' => 'Russland', 'areaID' => 2, 'countryNameEng' => 'Russian Federation', 'countryIso' => 'RU', 'countryEn' => 'RUSSIAN FEDERATION', 'iso3' => 'RUS'],
            'RW' => ['countryName' => 'Ruanda', 'areaID' => 2, 'countryNameEng' => 'Rwanda', 'countryIso' => 'RW', 'countryEn' => 'RWANDA', 'iso3' => 'RWA'],
            'BL' => ['countryName' => 'Saint-Barthélemy', 'areaID' => 2, 'countryNameEng' => 'Saint Barthélemy', 'countryIso' => 'BL', 'countryEn' => 'SAINT BARTHÉLEMY', 'iso3' => 'BLM'],
            'SH' => ['countryName' => 'Sankt Helena', 'areaID' => 2, 'countryNameEng' => 'Saint Helena, Ascension and Tristan da Cunha', 'countryIso' => 'SH', 'countryEn' => 'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA', 'iso3' => 'SHN'],
            'KN' => ['countryName' => 'St. Kitts und Nevis', 'areaID' => 2, 'countryNameEng' => 'Saint Kitts and Nevis', 'countryIso' => 'KN', 'countryEn' => 'SAINT KITTS AND NEVIS', 'iso3' => 'KNA'],
            'LC' => ['countryName' => 'Saint Lucia', 'areaID' => 2, 'countryNameEng' => 'Saint Lucia', 'countryIso' => 'LC', 'countryEn' => 'SAINT LUCIA', 'iso3' => 'LCA'],
            'MF' => ['countryName' => 'Saint Martin', 'areaID' => 2, 'countryNameEng' => 'Saint Martin (French part)', 'countryIso' => 'MF', 'countryEn' => 'SAINT MARTIN (FRENCH PART)', 'iso3' => 'MAF'],
            'PM' => ['countryName' => 'Saint-Pierre und Miquelon', 'areaID' => 2, 'countryNameEng' => 'Saint Pierre and Miquelon', 'countryIso' => 'PM', 'countryEn' => 'SAINT PIERRE AND MIQUELON', 'iso3' => 'SPM'],
            'VC' => ['countryName' => 'Saint Vincent und die Grenadinen', 'areaID' => 2, 'countryNameEng' => 'Saint Vincent and the Grenadines', 'countryIso' => 'VC', 'countryEn' => 'SAINT VINCENT AND THE GRENADINES', 'iso3' => 'VCT'],
            'WS' => ['countryName' => 'Samoa', 'areaID' => 2, 'countryNameEng' => 'Samoa', 'countryIso' => 'WS', 'countryEn' => 'SAMOA', 'iso3' => 'WSM'],
            'SM' => ['countryName' => 'San Marino', 'areaID' => 2, 'countryNameEng' => 'San Marino', 'countryIso' => 'SM', 'countryEn' => 'SAN MARINO', 'iso3' => 'SMR'],
            'ST' => ['countryName' => 'São Tomé und Príncipe', 'areaID' => 2, 'countryNameEng' => 'Sao Tome and Principe', 'countryIso' => 'ST', 'countryEn' => 'SAO TOME AND PRINCIPE', 'iso3' => 'STP'],
            'SA' => ['countryName' => 'Saudi-Arabien', 'areaID' => 2, 'countryNameEng' => 'Saudi Arabia', 'countryIso' => 'SA', 'countryEn' => 'SAUDI ARABIA', 'iso3' => 'SAU'],
            'SN' => ['countryName' => 'Senegal', 'areaID' => 2, 'countryNameEng' => 'Senegal', 'countryIso' => 'SN', 'countryEn' => 'SENEGAL', 'iso3' => 'SEN'],
            'RS' => ['countryName' => 'Serbien', 'areaID' => 2, 'countryNameEng' => 'Serbia', 'countryIso' => 'RS', 'countryEn' => 'SERBIA', 'iso3' => 'SRB'],
            'SC' => ['countryName' => 'Seychellen', 'areaID' => 2, 'countryNameEng' => 'Seychelles', 'countryIso' => 'SC', 'countryEn' => 'SEYCHELLES', 'iso3' => 'SYC'],
            'SL' => ['countryName' => 'Sierra Leone', 'areaID' => 2, 'countryNameEng' => 'Sierra Leone', 'countryIso' => 'SL', 'countryEn' => 'SIERRA LEONE', 'iso3' => 'SLE'],
            'SG' => ['countryName' => 'Singapur', 'areaID' => 2, 'countryNameEng' => 'Singapore', 'countryIso' => 'SG', 'countryEn' => 'SINGAPORE', 'iso3' => 'SGP'],
            'SX' => ['countryName' => 'Sint Maarten (niederl. Teil)', 'areaID' => 2, 'countryNameEng' => 'Sint Maarten (Dutch part)', 'countryIso' => 'SX', 'countryEn' => 'SINT MAARTEN (DUTCH PART)', 'iso3' => 'SXM'],
            'SB' => ['countryName' => 'Salomonen', 'areaID' => 2, 'countryNameEng' => 'Solomon Islands', 'countryIso' => 'SB', 'countryEn' => 'SOLOMON ISLANDS', 'iso3' => 'SLB'],
            'SO' => ['countryName' => 'Somalia', 'areaID' => 2, 'countryNameEng' => 'Somalia', 'countryIso' => 'SO', 'countryEn' => 'SOMALIA', 'iso3' => 'SOM'],
            'ZA' => ['countryName' => 'Republik Südafrika', 'areaID' => 2, 'countryNameEng' => 'South Africa', 'countryIso' => 'ZA', 'countryEn' => 'SOUTH AFRICA', 'iso3' => 'ZAF'],
            'GS' => ['countryName' => 'Südgeorgien und die Südlichen Sandwichinseln', 'areaID' => 2, 'countryNameEng' => 'South Georgia and the South Sandwich Islands', 'countryIso' => 'GS', 'countryEn' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'iso3' => 'SGS'],
            'KR' => ['countryName' => 'Südkorea', 'areaID' => 2, 'countryNameEng' => 'Korea (Republic of)', 'countryIso' => 'KR', 'countryEn' => 'KOREA (REPUBLIC OF)', 'iso3' => 'KOR'],
            'SS' => ['countryName' => 'Südsudan', 'areaID' => 2, 'countryNameEng' => 'South Sudan', 'countryIso' => 'SS', 'countryEn' => 'SOUTH SUDAN', 'iso3' => 'SSD'],
            'LK' => ['countryName' => 'Sri Lanka', 'areaID' => 2, 'countryNameEng' => 'Sri Lanka', 'countryIso' => 'LK', 'countryEn' => 'SRI LANKA', 'iso3' => 'LKA'],
            'SD' => ['countryName' => 'Sudan', 'areaID' => 2, 'countryNameEng' => 'Sudan', 'countryIso' => 'SD', 'countryEn' => 'SUDAN', 'iso3' => 'SDN'],
            'SR' => ['countryName' => 'Suriname', 'areaID' => 2, 'countryNameEng' => 'Suriname', 'countryIso' => 'SR', 'countryEn' => 'SURINAME', 'iso3' => 'SUR'],
            'SJ' => ['countryName' => 'Svalbard und Jan Mayen', 'areaID' => 2, 'countryNameEng' => 'Svalbard and Jan Mayen', 'countryIso' => 'SJ', 'countryEn' => 'SVALBARD AND JAN MAYEN', 'iso3' => 'SJM'],
            'SZ' => ['countryName' => 'Swasiland', 'areaID' => 2, 'countryNameEng' => 'Swaziland', 'countryIso' => 'SZ', 'countryEn' => 'SWAZILAND', 'iso3' => 'SWZ'],
            'SY' => ['countryName' => 'Syrien', 'areaID' => 2, 'countryNameEng' => 'Syrian Arab Republic', 'countryIso' => 'SY', 'countryEn' => 'SYRIAN ARAB REPUBLIC', 'iso3' => 'SYR'],
            'TW' => ['countryName' => 'Taiwan', 'areaID' => 2, 'countryNameEng' => 'Taiwan', 'countryIso' => 'TW', 'countryEn' => 'TAIWAN', 'iso3' => 'TWN'],
            'TJ' => ['countryName' => 'Tadschikistan', 'areaID' => 2, 'countryNameEng' => 'Tajikistan', 'countryIso' => 'TJ', 'countryEn' => 'TAJIKISTAN', 'iso3' => 'TJK'],
            'TZ' => ['countryName' => 'Tansania', 'areaID' => 2, 'countryNameEng' => 'Tanzania, United Republic of', 'countryIso' => 'TZ', 'countryEn' => 'TANZANIA, UNITED REPUBLIC OF', 'iso3' => 'TZA'],
            'TH' => ['countryName' => 'Thailand', 'areaID' => 2, 'countryNameEng' => 'Thailand', 'countryIso' => 'TH', 'countryEn' => 'THAILAND', 'iso3' => 'THA'],
            'TL' => ['countryName' => 'Timor-Leste', 'areaID' => 2, 'countryNameEng' => 'Timor-Leste', 'countryIso' => 'TL', 'countryEn' => 'TIMOR-LESTE', 'iso3' => 'TLS'],
            'TG' => ['countryName' => 'Togo', 'areaID' => 2, 'countryNameEng' => 'Togo', 'countryIso' => 'TG', 'countryEn' => 'TOGO', 'iso3' => 'TGO'],
            'TK' => ['countryName' => 'Tokelau', 'areaID' => 2, 'countryNameEng' => 'Tokelau', 'countryIso' => 'TK', 'countryEn' => 'TOKELAU', 'iso3' => 'TKL'],
            'TO' => ['countryName' => 'Tonga', 'areaID' => 2, 'countryNameEng' => 'Tonga', 'countryIso' => 'TO', 'countryEn' => 'TONGA', 'iso3' => 'TON'],
            'TT' => ['countryName' => 'Trinidad und Tobago', 'areaID' => 2, 'countryNameEng' => 'Trinidad and Tobago', 'countryIso' => 'TT', 'countryEn' => 'TRINIDAD AND TOBAGO', 'iso3' => 'TTO'],
            'TN' => ['countryName' => 'Tunesien', 'areaID' => 2, 'countryNameEng' => 'Tunisia', 'countryIso' => 'TN', 'countryEn' => 'TUNISIA', 'iso3' => 'TUN'],
            'TM' => ['countryName' => 'Turkmenistan', 'areaID' => 2, 'countryNameEng' => 'Turkmenistan', 'countryIso' => 'TM', 'countryEn' => 'TURKMENISTAN', 'iso3' => 'TKM'],
            'TC' => ['countryName' => 'Turks- und Caicosinseln', 'areaID' => 2, 'countryNameEng' => 'Turks and Caicos Islands', 'countryIso' => 'TC', 'countryEn' => 'TURKS AND CAICOS ISLANDS', 'iso3' => 'TCA'],
            'TV' => ['countryName' => 'Tuvalu', 'areaID' => 2, 'countryNameEng' => 'Tuvalu', 'countryIso' => 'TV', 'countryEn' => 'TUVALU', 'iso3' => 'TUV'],
            'UG' => ['countryName' => 'Uganda', 'areaID' => 2, 'countryNameEng' => 'Uganda', 'countryIso' => 'UG', 'countryEn' => 'UGANDA', 'iso3' => 'UGA'],
            'UA' => ['countryName' => 'Ukraine', 'areaID' => 2, 'countryNameEng' => 'Ukraine', 'countryIso' => 'UA', 'countryEn' => 'UKRAINE', 'iso3' => 'UKR'],
            'UY' => ['countryName' => 'Uruguay', 'areaID' => 2, 'countryNameEng' => 'Uruguay', 'countryIso' => 'UY', 'countryEn' => 'URUGUAY', 'iso3' => 'URY'],
            'UZ' => ['countryName' => 'Usbekistan', 'areaID' => 2, 'countryNameEng' => 'Uzbekistan', 'countryIso' => 'UZ', 'countryEn' => 'UZBEKISTAN', 'iso3' => 'UZB'],
            'VU' => ['countryName' => 'Vanuatu', 'areaID' => 2, 'countryNameEng' => 'Vanuatu', 'countryIso' => 'VU', 'countryEn' => 'VANUATU', 'iso3' => 'VUT'],
            'VE' => ['countryName' => 'Venezuela', 'areaID' => 2, 'countryNameEng' => 'Venezuela (Bolivarian Republic of)', 'countryIso' => 'VE', 'countryEn' => 'VENEZUELA (BOLIVARIAN REPUBLIC OF)', 'iso3' => 'VEN'],
            'VN' => ['countryName' => 'Vietnam', 'areaID' => 2, 'countryNameEng' => 'Viet Nam', 'countryIso' => 'VN', 'countryEn' => 'VIET NAM', 'iso3' => 'VNM'],
            'WF' => ['countryName' => 'Wallis und Futuna', 'areaID' => 2, 'countryNameEng' => 'Wallis and Futuna', 'countryIso' => 'WF', 'countryEn' => 'WALLIS AND FUTUNA', 'iso3' => 'WLF'],
            'EH' => ['countryName' => 'Westsahara', 'areaID' => 2, 'countryNameEng' => 'Western Sahara', 'countryIso' => 'EH', 'countryEn' => 'WESTERN SAHARA', 'iso3' => 'ESH'],
            'YE' => ['countryName' => 'Jemen', 'areaID' => 2, 'countryNameEng' => 'Yemen', 'countryIso' => 'YE', 'countryEn' => 'YEMEN', 'iso3' => 'YEM'],
            'ZM' => ['countryName' => 'Sambia', 'areaID' => 2, 'countryNameEng' => 'Zambia', 'countryIso' => 'ZM', 'countryEn' => 'ZAMBIA', 'iso3' => 'ZMB'],
            'ZW' => ['countryName' => 'Simbabwe', 'areaID' => 2, 'countryNameEng' => 'Zimbabwe', 'countryIso' => 'ZW', 'countryEn' => 'ZIMBABWE', 'iso3' => 'ZWE'],
        ];

        $newCountryIsos = array_keys($newCountries);

        $currentCountryStatement = $this->connection->query('SELECT `countryiso` FROM `s_core_countries`;');
        $currentCountryStatement->execute();

        $currentCountryList = $currentCountryStatement->fetchAll(PDO::FETCH_COLUMN);

        $defaultLocaleIdStatement = $this->connection->query('SELECT MID(`locale`, 1, 2) AS localePrefix
            FROM `s_core_locales`
            WHERE `id` = (
                SELECT locale_id
                FROM `s_core_shops`
                WHERE `default` = \'1\'
                LIMIT 1
            )
            LIMIT 1'
        );

        $defaultLocaleIdStatement->execute();
        $defaultLocale = $defaultLocaleIdStatement->fetchColumn();

        $insertStatement = $this->connection->prepare('INSERT INTO `s_core_countries` (`countryname`, `countryiso`, `areaID`, `countryen`, `position`, `notice`, `taxfree`, `taxfree_ustid`, `taxfree_ustid_checked`, `active`, `iso3`, `display_state_in_registration`, `force_state_in_registration`, `allow_shipping`) ' .
            ' VALUES (:countryName, :countryIso, :areaID, :countryEn, 10, "", 0, 0, 0, 0, :iso3, 0, 0, 1)');

        foreach (array_diff($newCountryIsos, $currentCountryList) as $missingIso) {
            $newCountry = $newCountries[$missingIso];
            $this->saveNewCountry($insertStatement, $newCountry, $defaultLocale);
        }
    }

    /**
     * @param PDOStatement $statement
     * @param array        $newCountry
     * @param string       $defaultLocale
     */
    private function saveNewCountry(PDOStatement $statement, array $newCountry, $defaultLocale)
    {
        if ($defaultLocale === 'en') {
            $newCountry['countryName'] = $newCountry['countryNameEng'];
        }

        unset($newCountry['countryNameEng']);

        $statement->execute($newCountry);
    }
}
