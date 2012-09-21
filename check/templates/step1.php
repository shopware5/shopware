<?php
if (!defined("installer")){
    exit;
}
?>
<!-- Start page -->
<div id="start" xmlns="http://www.w3.org/1999/html">


      <div class="alert alert-error">
          <strong>Wichtiger Hinweis:</strong><br />
          Löschen Sie das Check-Script vom Server, nachdem Sie Ihre Konfiguration überprüft haben!
     </div>


    <?php
    if ($errorRequirements == true || $errorFiles == true){
    ?>
    <div class="alert alert-error">
        <strong>Ergebnis:</strong> <br/>
        <?php echo $language["step2_error"];?>
   </div>
   <?php
    }
   ?>


    <div class="page-header" style="cursor:pointer">
           <h2 <?php echo $errorFiles == true ? "style=\"color:#F00\"" : "" ?>><?php echo $language["step2_header_files"];?></h2>
        <i class="icon-chevron-down"></i>
    </div>
    <span class="help-block">
        <?php echo $language["step2_files_info"];?>
    </span>
    <table class="table table-striped" >
            <thead>
                <tr>
                    <th><?php echo $language["step2_tablefiles_colcheck"];?></th>
                    <th><?php echo $language["step2_tablefiles_colstatus"];?></th>
                </tr>
            </thead>
        <tbody>
            <?php
               foreach ($systemCheckResultsWritePermissions as $systemCheckResult){
            ?>
                <?php
                if ($systemCheckResult["existsAndWriteable"] == true){
                    $class = "success";
                }else {
                    $class = "error";
                }
                ?>
                <tr class="<?php echo $class; ?>">
                    <td><?php echo $systemCheckResult["name"] ?></td>
                    <td><?php echo $systemCheckResult["existsAndWriteable"] == true ? '<i class="icon-ok-sign"></i>' : '<i class="icon-minus-sign"></i>' ?></td>
                </tr>
            <?php
               }
            ?>
            </tbody>
    </table>

    <div class="page-header" style="cursor:pointer">
        <h2 <?php echo $errorRequirements == true ? "style=\"color:#F00\"" : "" ?>><?php echo $language["system_requirements_header"];?></h2>
        <i class="icon-chevron-down"></i>
    </div>
    <span class="help-block">
        <?php echo $language["step2_php_info"];?>
    </span>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php echo $language["step2_system_colcheck"];?></th>
                <th><?php echo $language["step2_system_colrequired"];?></th>
                <th><?php echo $language["step2_system_colfound"];?></th>
                <th><?php echo $language["step2_system_colstatus"];?></th>
            </tr>
        </thead>

        <tbody>
        <?php
           foreach ($systemCheckResults as $systemCheckResult){
        ?>
            <?php
            if ($systemCheckResult["result"] == true){
                $class = "success";
            }else {
                if ($systemCheckResult["error"] == true){
                    $class = "error";
                }else {
                    $class = "warning";
                }
            }
            ?>
            <tr class="<?php echo $class; ?>">
                <td><?php echo $systemCheckResult["name"] ?></td>
                <td><?php echo $systemCheckResult["required"] ?></td>
                <td><?php echo empty($systemCheckResult["version"]) ? "0" : $systemCheckResult["version"] ?></td>
                <td><?php echo $systemCheckResult["result"] == true ? '<i class="icon-ok-sign"></i>' : '<i class="icon-minus-sign"></i>' ?></td>
            </tr>
            <?php
             if($systemCheckResult["notice"]){
            ?>
           <tr class="notice-text">
               <td colspan="4">
                    <p><i class="icon-info-sign"></i> <?php echo $systemCheckResult["notice"] ?></p>
               </td>
           </tr>
             <?php
             }
             ?>
        <?php
           }
        ?>
        </tbody>
    </table>
    <?php
          if ($databaseError == true){
          ?>
          <div class="alert alert-error">
              <?php echo $databaseError; ?>
         </div>
         <?php
          }
         ?>
    <div class="page-header">
    <h2 <?php echo $databaseError == true ? "style=\"color:#F00\"" : "" ?>><?php echo $language["step3_header"];?></h2>
    </div>

    <div class="row">
        <div class="span12">
            <form action="<?php echo $app->urlFor('step1', array()); ?>" method="post">
                <input type="hidden" name="action" value="check" />
                <label><?php echo $language["step3_field_host"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_host"]) ? $parameters["c_database_host"] : 'localhost' ?>" name="c_database_host" required="required" />

                <label><?php echo $language["step3_field_port"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_port"]) ? $parameters["c_database_port"] : '3306' ?>" name="c_database_port" required="required" />

                <label><?php echo $language["step3_field_socket"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_socket"]) ? $parameters["c_database_socket"] : '' ?>" name="c_database_socket" class="allowBlank" />

                <label><?php echo $language["step3_field_user"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_user"]) ? $parameters["c_database_user"] : '' ?>" name="c_database_user" required="required" />

                <label><?php echo $language["step3_field_password"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_password"]) ? $parameters["c_database_password"] : '' ?>" name="c_database_password" required="required" />

                <label><?php echo $language["step3_field_database"];?></label>
                <input type="text" value="<?php echo isset($parameters["c_database_schema"]) ? $parameters["c_database_schema"] : '' ?>" name="c_database_schema" required="required" />

                <span class="help-block">
                    <?php echo $language["step3_info"];?>
                </span>

                <div class="actions clearfix">

                    <input type="submit" class="right primary database-import" value="Check" />
                </div>
            </form>
        </div>
    </div>
</div>
