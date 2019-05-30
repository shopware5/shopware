<?php $app->render('_header.php'); ?>

<h2><?= $t->t('configuration_header'); ?></h2>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= $error; ?>
    </div>
<?php endif; ?>

<form action="<?= $menuHelper->getCurrentUrl(); ?>" method="post">
    <p>
        <?= $t->t('configuration_sconfig_text'); ?>
    </p>

    <p>
        <label for="c_config_shopName"><?= $t->t('configuration_sconfig_name'); ?></label>
        <input type="text"
               value="<?= isset($parameters['c_config_shopName']) ? $parameters['c_config_shopName'] : 'Demoshop'; ?>"
               name="c_config_shopName"
               id="c_config_shopName"
               required="required"
               autofocus/>

        <span class="help-block">
           <?= $t->t('configuration_sconfig_name_info'); ?>
        </span>
    </p>

    <p>
        <label for="c_config_mail"><?= $t->t('configuration_sconfig_mail'); ?></label>
        <input type="email"
               value="<?= isset($parameters['c_config_mail']) ? $parameters['c_config_mail'] : 'your.email@shop.com'; ?>"
               name="c_config_mail"
               id="c_config_mail"
               required="required"/>

        <span class="help-block">
           <?= $t->t('configuration_sconfig_mail_info'); ?>
        </span>
    </p>

    <p>
        <label for="c_config_shop_language"><?= $t->t('configuration_sconfig_language'); ?></label>
        <select name="c_config_shop_language" id="c_config_shop_language">
            <option
                value="de_DE" <?= $parameters['c_config_shop_language'] == 'de_DE' ? 'selected' : ''; ?>><?= $t->t('configuration_admin_language_de'); ?></option>
            <option
                value="en_GB" <?= $parameters['c_config_shop_language'] == 'en_GB' ? 'selected' : ''; ?>><?= $t->t('configuration_admin_language_en'); ?></option>
        </select>
    </p>

    <p>
        <label for="c_config_shop_currency"><?= $t->t('configuration_sconfig_currency'); ?></label>
        <select name="c_config_shop_currency" id="c_config_shop_currency">
            <option
                value="EUR" <?= $parameters['c_config_shop_currency'] == 'EUR' ? 'selected' : ''; ?>><?= $t->t('configuration_admin_currency_eur'); ?></option>
            <option
                value="USD" <?= $parameters['c_config_shop_currency'] == 'USD' ? 'selected' : ''; ?>><?= $t->t('configuration_admin_currency_usd'); ?></option>
            <option
                value="GBP" <?= $parameters['c_config_shop_currency'] == 'GBP' ? 'selected' : ''; ?>><?= $t->t('configuration_admin_currency_gbp'); ?></option>
        </select>
        <span class="help-block">
           <?= $t->t('configuration_sconfig_currency_info'); ?>
        </span>
    </p>

    <p>
        <label for="c_config_admin_name"><?= $t->t('configuration_admin_name'); ?></label>
        <input type="text"
               value="<?= isset($parameters['c_config_admin_name']) ? $parameters['c_config_admin_name'] : 'Demo-Admin'; ?>"
               name="c_config_admin_name"
               id="c_config_admin_name"
               required="required"/>
    </p>

    <p>
        <label for="c_config_admin_username"><?= $t->t('configuration_admin_username'); ?></label>
        <input type="text"
               value="<?= isset($parameters['c_config_admin_username']) ? $parameters['c_config_admin_username'] : 'demo'; ?>"
               name="c_config_admin_username"
               id="c_config_admin_username"
               required="required"/>
    </p>

    <p>
        <label for="c_config_admin_email"><?= $t->t('configuration_admin_mail'); ?></label>
        <input type="email"
               value="<?= isset($parameters['c_config_admin_email']) ? $parameters['c_config_admin_email'] : 'demo@demo.de'; ?>"
               name="c_config_admin_email"
               id="c_config_admin_email"
               required="required"/>
    </p>

    <p>
        <label for="c_config_admin_password"><?= $t->t('configuration_admin_password'); ?></label>
        <input type="password"
               value="<?= isset($parameters['c_config_admin_password']) ? $parameters['c_config_admin_password'] : ''; ?>"
               name="c_config_admin_password"
               id="c_config_admin_password"
               required="required"/>
    </p>

    <div class="actions clearfix">
        <a href="<?= $menuHelper->getPreviousUrl(); ?>"
           class="btn btn-default btn-arrow-left"><?= $t->t('back'); ?></a>
        <button type="submit"
                class="btn btn-primary btn-arrow-right is--right"><?= $t->t('forward'); ?></button>
    </div>
</form>

<?php $app->render('_footer.php'); ?>
