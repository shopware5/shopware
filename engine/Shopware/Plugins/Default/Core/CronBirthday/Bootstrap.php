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
 */
class Shopware_Plugins_Core_CronBirthday_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        $this->subscribeEvent(
            'Shopware_CronJob_Birthday',
            'onRun'
        );
        return true;
    }

    public static function onRun(Shopware_Components_Cron_CronJob $job)
    {
        $birthdayVoucher = Shopware()->Config()->get('birthdayVoucher', 'birthday');

        $sql = "
            SELECT
                user_id as 'userID',
                company,
                department,
                u.salutation,
                u.customernumber,
                u.firstname,
                u.lastname,
                street,
                zipcode,
                city,
                phone,
                country_id AS 'countryID',
                ustid,
                at.text1,
                at.text2,
                at.text3,
                at.text4,
                at.text5,
                at.text6,
                email,
                paymentID,
                firstlogin,
                lastlogin,
                newsletter,
                affiliate,
                customergroup,
                language,
                subshopID
            FROM s_user u
            LEFT JOIN s_user_addresses ub
            ON u.default_billing_address_id = ub.id
            AND u.id = ub.user_id
            LEFT JOIN s_user_addresses_attributes at
            ON at.address_id = ub.id
            WHERE accountmode = 0
            AND active = 1
            AND user_id = u.id
            AND birthday LIKE ?
        ";
        $users = Shopware()->Db()->fetchAll($sql, array(
            '%-' . date('m-d')
        ));
        if (empty($users)) {
            return 'No birthday users found.';
        }
        $sql = '
            SELECT evc.voucherID
            FROM s_emarketing_vouchers ev, s_emarketing_voucher_codes evc
            WHERE  modus = 1 AND (valid_to >= CURDATE() OR valid_to IS NULL)
            AND evc.voucherID = ev.id
            AND evc.userID IS NULL
            AND evc.cashed = 0
            AND ev.ordercode= ?
        ';
        $voucherId = Shopware()->Db()->fetchOne($sql, array($birthdayVoucher));
        if (empty($voucherId)) {
            return 'No birthday voucher found.';
        }

        foreach ($users as $user) {
            $sql = '
                SELECT evc.id as vouchercodeID, evc.code, ev.value, ev.percental, ev.valid_to, ev.valid_from
                FROM s_emarketing_voucher_codes evc, s_emarketing_vouchers ev
                WHERE evc.voucherID = ?
                AND ev.id = evc.voucherID
                AND evc.userID IS NULL
                AND evc.cashed = 0
            ';
            $voucher = Shopware()->Db()->fetchRow($sql, array($voucherId));
            if (empty($voucher)) {
                return 'No new voucher code found.';
            }
            $sql = '
                UPDATE s_emarketing_voucher_codes evc
                SET userID=?
                WHERE id=?
                AND userID IS NULL
            ';
            $result = Shopware()->Db()->query($sql, array(
                $user['userID'], $voucher['vouchercodeID']
            ));
            if (empty($result)) {
                continue;
            }
            $result = $result->rowCount();
            if (empty($result)) {
                continue;
            }

            /** @var Shopware\Models\Shop\Repository $repository  */
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
            $shopId = is_numeric($user['language']) ? $user['language'] : $user['subshopID'];
            $shop = $repository->getActiveById($shopId);
            $shop->registerResources();

            //language 	subshopID
            $context = array(
                'sUser' => $user,
                'sVoucher' => $voucher,
                'sData' => $job['data']
            );

            $mail = Shopware()->TemplateMail()->createMail('sBIRTHDAY', $context);
            $mail->addTo($user['email']);
            $mail->send();
        }

        return count($users) . ' birthday email(s) with voucher was send.';
    }
}
