<?php $app->render('header.php', array('tab' => 'configuration')) ?>

<div id="start">
    <div class="page-header">
        <h2><?php echo $language["step6_header"];?></h2>
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

    <form action="<?php echo $app->urlFor('step6', array()); ?>" method="post">
        <input type="hidden" name="action" value="check" />

        <div class="row">
            <div class="span12">
                <div class="page-header">
                    <h3><?php echo $language["step6_sconfig_header"];?></h3>
                </div>

                <label><?php echo $language["step6_sconfig_name"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_config_shopName"]) ? $parameters["c_config_shopName"] : 'Demoshop' ?>" name="c_config_shopName" required="required" />
                <span class="help-block">
                   <?php echo $language["step6_sconfig_name_info"];?>
                 </span>
                <label><?php echo $language["step6_sconfig_mail"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_config_mail"]) ? $parameters["c_config_mail"] : 'your.email@shop.com' ?>" name="c_config_mail" required="required" />
                <span class="help-block">
                   <?php echo $language["step6_sconfig_mail_info"];?>
                </span>
                <label><?php echo $language["step6_sconfig_domain"];?></label>
                <input type="text" value="<?php echo isset($shop["domain"]) ? $shop["domain"] : 'Error: Could not resolve domain' ?>" name="c_config_shop_host" disabled  />
                <div class="row">
                    <div class="span6">
                <label><?php echo $language["step6_sconfig_language"];?></label>
                    <select name="c_config_shop_language">
                        <option value="de_DE" <?php echo $parameters["c_config_shop_language"] == "de_DE" ? "selected" : ""?>><?php echo $language["step6_admin_language_de"];?></option>
                        <option value="en_GB" <?php echo $parameters["c_config_shop_language"] == "en_GB" ? "selected" : ""?>><?php echo $language["step6_admin_language_en"];?></option>
                    </select>
                     </div>
                <div class="span6">
                <label><?php echo $language["step6_sconfig_currency"];?></label>
                    <select name="c_config_shop_currency">
                        <?php
                        foreach ($currencies as $currency) {
                        ?>
                            <option value="<?php echo $currency["id"] ?>" <?php echo $parameters["c_config_shop_currency"] == $currency["id"] ? "selected" : ""?>><?php echo $currency["currency"] ?></option>
                        <?php
                        }
                        ?>
                   </select>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="span12">

                <div class="page-header">
                    <h3><?php echo $language["step6_admin_title"];?></h3>
                </div>
                <div class="row">
                    <div class="span6">
                    <label><?php echo $language["step6_admin_login"];?></label>
                    <input type="text" value="<?php echo isset($parameters["c_config_admin_user"]) ? $parameters["c_config_admin_user"] : 'demo' ?>" name="c_config_admin_user" required="required" />

                    <label><?php echo $language["step6_admin_mail"];?></label>
                    <input type="text" value="<?php echo isset($parameters["c_config_admin_email"]) ? $parameters["c_config_admin_email"] : 'demo@demo.de' ?>" name="c_config_admin_email" required="required" />
                    </div>
                    <div class="span6">
                    <label><?php echo $language["step6_admin_name"];?></label>
                    <input type="text" value="<?php echo isset($parameters["c_config_admin_name"]) ? $parameters["c_config_admin_name"] : 'Demo-Admin' ?>" name="c_config_admin_name" required="required" />

                    <label><?php echo $language["step6_admin_language"];?></label>
                        <select name="c_config_admin_language">
                           <option value="de_DE" <?php echo $parameters["c_config_admin_language"] == "de_DE" ? "selected" : ""?>><?php echo $language["step6_admin_language_de"];?></option>
                           <option value="en_GB" <?php echo $parameters["c_config_admin_language"] == "en_GB" ? "selected" : ""?>><?php echo $language["step6_admin_language_en"];?></option>
                       </select>
                    </div>
               </div>
            </div>
        </div>

        <div class="row">
            <div class="span6">
                <label><?php echo $language["step6_admin_password"];?></label>
                <input type="password" value="<?php echo isset($parameters["c_config_admin_password"]) ? $parameters["c_config_admin_password"] : 'demo' ?>" name="c_config_admin_password" required="required" />
            </div>

            <div class="span6">

                <label><?php echo $language["step6_admin_password_repeat"];?></label>
                <input type="password" value="<?php echo isset($parameters["c_config_admin_password2"]) ? $parameters["c_config_admin_password2"] : 'demo' ?>" name="c_config_admin_password2" required="required" />
            </div>
        </div>

        <div class="actions clearfix">
            <a href="<?php echo $app->urlFor('step5', array()); ?>" class="secondary"><?php echo $language["back"];?></a>
            <input type="submit" class="right primary" value="<?php echo $language["forward"];?>" />
        </div>
    </form>
</div>

<?php $app->render('footer.php') ?>
