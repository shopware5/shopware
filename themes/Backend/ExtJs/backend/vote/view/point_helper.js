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
 *
 * @category   Shopware
 * @package    Vote
 * @subpackage App
 * @version    $Id$
 * @author shopware AG
 */

//{block name="backend/vote/view/point_helper"}
Ext.define('Shopware.apps.Vote.view.PointHelper', {
    renderPoints: function(points) {
        var html = '';
        var count = 0;
        var i;

        for(i=0; i<points; i++){
            if((i-points) == -0.5) {
                //create half-star
                html = html + '<div style="height: 16px; width: 16px; display: inline-block;" class="sprite-star-half"></div>';
            }else{
                //create full stars
                html = html + '<div style="height: 16px; width: 16px; display: inline-block;" class="sprite-star"></div>';
            }
            count++;
        }

        //add empty stars, so 5 stars are displayed
        for(i=0; i<(5-count); i++){
            html = html + '<div style="height: 16px; width: 16px; display: inline-block;" class="sprite-star-empty"></div>';
        }
        return html;
    }
});
//{/block}
