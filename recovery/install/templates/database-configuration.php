<?php $app->render('_header.php'); ?>

<h2><?= $t->t('database-configuration_header'); ?></h2>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= $error; ?>
    </div>
<?php endif; ?>

<p>
    <?= $t->t('database-configuration_info'); ?>
</p>

<form
    action="<?= $menuHelper->getCurrentUrl(); ?>"
    method="post"
    data-ajaxDatabaseSelection="true"
    data-url="<?= $app->urlFor('database'); ?>">
    <p>
        <label for="c_database_host"><?= $t->t('database-configuration_field_host'); ?></label>
        <input type="text" value="<?= isset($parameters['c_database_host']) ? $parameters['c_database_host'] : 'localhost'; ?>" name="c_database_host" id="c_database_host" required="required" />
    </p>

    <p>
        <label for="c_database_user"><?= $t->t('database-configuration_field_user'); ?></label>
        <input type="text" value="<?= isset($parameters['c_database_user']) ? $parameters['c_database_user'] : ''; ?>" name="c_database_user" id="c_database_user" required="required" />
    </p>

    <p>
        <label for="c_database_password"><?= $t->t('database-configuration_field_password'); ?></label>
        <input type="password" value="<?= isset($parameters['c_database_password']) ? $parameters['c_database_password'] : ''; ?>" name="c_database_password" id="c_database_password" />
    </p>

    <p>
        <input type="checkbox" id="c_advanced" class="toggle" data-href="#advanced-settings" />
        <label for="c_advanced" class="toggle width-auto"><?= $t->t('database-configuration_advanced_settings'); ?></label>
    </p>

    <div class="is--hidden" id="advanced-settings">
        <p>
            <label for="c_database_port"><?= $t->t('database-configuration_field_port'); ?></label>
            <input type="text" value="<?= isset($parameters['c_database_port']) ? $parameters['c_database_port'] : '3306'; ?>" name="c_database_port" id="c_database_port" required="required" />
        </p>

        <p>
            <label for="c_database_socket"><?= $t->t('database-configuration_field_socket'); ?></label>
            <input type="text" value="<?= isset($parameters['c_database_socket']) ? $parameters['c_database_socket'] : ''; ?>" name="c_database_socket" id="c_database_socket" />
        </p>
    </div>

    <p>
        <label for="c_database_schema"><?= $t->t('database-configuration_field_database'); ?></label>
        <input
            data-ajaxDatabaseSelection="true"
            data-url="<?= $app->urlFor('database'); ?>"
            type="text"
            value="<?= isset($parameters['c_database_schema']) ? $parameters['c_database_schema'] : ''; ?>"
            name="c_database_schema"
            id="c_database_schema"
            required="required" />
    </p>

    <div class="actions clearfix">
        <a href="<?= $menuHelper->getPreviousUrl(); ?>" class="btn btn-default btn-arrow-left"><?= $t->t('back'); ?></a>
        <button type="submit" class="btn btn-primary btn-arrow-right is--right"><?= $t->t('forward'); ?></button>
    </div>
</form>

<?php $app->render('_footer.php'); ?>
