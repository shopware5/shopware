<?php $app->render('header.php', array('tab' => 'database')) ?>

<div id="start">
    <div class="page-header">
        <h2><?php echo $language["step3_header"];?></h2>
    </div>

    <?php
     if ($error == true) {
     ?>
     <div class="alert alert-error">
         <?php echo $error ?>
    </div>
    <?php
     }
    ?>

    <div class="row">
        <div class="span12">
            <form action="<?php echo $app->urlFor('step3', array()); ?>" method="post">
                <input type="hidden" name="action" value="check" />
                <label><?php echo $language["step3_field_host"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_host"]) ? $parameters["c_database_host"] : 'localhost' ?>" name="c_database_host" required="required" />

                <label><?php echo $language["step3_field_port"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_port"]) ? $parameters["c_database_port"] : '3306' ?>" name="c_database_port" required="required" />

                <label class="allowBlank"><?php echo $language["step3_field_socket"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_socket"]) ? $parameters["c_database_socket"] : '' ?>" name="c_database_socket" class="allowBlank" />

                <label><?php echo $language["step3_field_user"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_user"]) ? $parameters["c_database_user"] : '' ?>" name="c_database_user" required="required" />

                <label><?php echo $language["step3_field_password"];?></label>
                <input type="password" value="<?php echo isset($parameters["c_database_password"]) ? $parameters["c_database_password"] : '' ?>" name="c_database_password" required="required" />

                <label><?php echo $language["step3_field_database"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_schema"]) ? $parameters["c_database_schema"] : '' ?>" name="c_database_schema" required="required" />

                <span class="help-block">
                    <?php echo $language["step3_info"];?>
                </span>

                <div class="actions clearfix">
                    <a href="<?php echo $app->urlFor('step2', array()); ?>" class="secondary"><?php echo $language["back"];?></a>
                    <input type="submit" class="right primary database-import" value="<?php echo $language["forward"];?>" />
                </div>
            </form>
        </div>
    </div>
</div>

<?php $app->render('footer.php') ?>
