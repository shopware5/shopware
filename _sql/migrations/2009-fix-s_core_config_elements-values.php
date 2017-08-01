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

class Migrations_Migration2009 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql(sprintf(
            "UPDATE s_core_config_elements SET value = '%s'",
            serialize('Zuschlag für Zahlungsart')
        ));

        $this->addSql(sprintf(
            "UPDATE s_core_config_elements SET value = '%s'",
            serialize('ab,die,der,und,in,zu,den,das,nicht,von,sie,ist,des,sich,mit,dem,dass,er,es,ein,ich,auf,so,eine,auch,als,an,nach,wie,im,für,einen,um,werden,mehr,zum,aus,ihrem,style,oder,neue,spieler,können,wird,sind,ihre,einem,of,du,sind,einer,über,alle,neuen,bei,durch,kann,hat,nur,noch,zur,gegen,bis,aber,haben,vor,seine,ihren,jetzt,ihr,dir,etc,bzw,nach,deine,the,warum,machen,0,sowie,am')
        ));
    }
}
