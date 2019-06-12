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

    public function onRun(Shopware_Components_Cron_CronJob $job)
    {
        $birthdayVoucher = Shopware()->Config()->get('birthdayVoucher', 'birthday');

        $sql = "
            SELECT
                u.id AS 'userID',
                u.salutation,
                u.customernumber,
                u.firstname,
                u.lastname,
                u.email,
                u.paymentID,
                u.firstlogin,
                u.lastlogin,
                u.newsletter,
                u.affiliate,
                u.customergroup,
                u.language,
                u.subshopID,
                ub.street,
                ub.zipcode,
                ub.city,
                ub.phone,
                ub.country_id AS 'countryID',
                ub.ustid,
                ub.company,
                ub.department,
                at.text1,
                at.text2,
                at.text3,
                at.text4,
                at.text5,
                at.text6
            FROM s_user u
            JOIN s_user_addresses ub
              ON u.default_billing_address_id = ub.id
                AND u.id = ub.user_id
            LEFT JOIN s_user_addresses_attributes at
              ON at.address_id = ub.id
            WHERE u.accountmode = 0
              AND u.active = 1
              AND u.birthday LIKE ?
        ";
        $users = Shopware()->Db()->fetchAll($sql, [
            '%-' . date('m-d'),
        ]);
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
        $voucherId = Shopware()->Db()->fetchOne($sql, [$birthdayVoucher]);
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
            $voucher = Shopware()->Db()->fetchRow($sql, [$voucherId]);
            if (empty($voucher)) {
                return 'No new voucher code found.';
            }
            $sql = '
                UPDATE s_emarketing_voucher_codes evc
                SET userID=?
                WHERE id=?
                AND userID IS NULL
            ';
            $result = Shopware()->Db()->query($sql, [
                $user['userID'], $voucher['vouchercodeID'],
            ]);
            if (empty($result)) {
                continue;
            }
            $result = $result->rowCount();
            if (empty($result)) {
                continue;
            }

            /** @var Shopware\Models\Shop\Repository $repository */
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
            $shopId = is_numeric($user['language']) ? $user['language'] : $user['subshopID'];
            $shop = $repository->getActiveById($shopId);
            $this->get('shopware.components.shop_registration_service')->registerShop($shop);

            //language subshopID
            $context = [
                'sUser' => $user,
                'sVoucher' => $voucher,
                'sData' => $job['data'],
            ];

            $mail = Shopware()->TemplateMail()->createMail('sBIRTHDAY', $context);
            $mail->addTo($user['email']);
            $mail->send();
        }

        return count($users) . ' birthday email(s) with voucher was send.';
    }
}
