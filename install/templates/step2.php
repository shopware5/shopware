<?php
if (!defined("installer")) {
    exit;
}
?>
<!-- Start page -->
<div id="start">

    <?php
    if ($error == true) {
    ?>
    <div class="alert alert-error">
        <?php echo $language["step2_error"];?>
   </div>
   <?php
    }
   ?>

    <form action="<?php echo $app->urlFor('step2', array()); ?>" method="post">
        <input type="hidden" name="action" value="check" />
        <div class="page-header">
               <h2><?php echo $language["step2_header_files"];?></h2>
        </div>
        <span class="help-block">
            <?php echo $language["step2_files_info"];?>
        </span>
        <table class="table table-striped">
                   <thead>
                       <tr>
                           <th><?php echo $language["step2_tablefiles_colcheck"];?></th>
                        <th><?php echo $language["step2_tablefiles_colstatus"];?></th>
                       </tr>
                   </thead>
            <tbody>
                <?php
                   foreach ($systemCheckResultsWritePermissions as $systemCheckResult) {
                ?>
                    <?php
                    if ($systemCheckResult["existsAndWriteable"] == true) {
                        $class = "success";
                    } else {
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

        <div class="page-header">
                <h2><?php echo $language["system_requirements_header"];?></h2>
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
               foreach ($systemCheckResults as $systemCheckResult) {
            ?>
                <?php
                if ($systemCheckResult["result"] == true) {
                    $class = "success";
                } else {
                    if ($systemCheckResult["error"] == true) {
                        $class = "error";
                    } else {
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
                 if (!empty($systemCheckResult["notice"])) {
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
        <div class="actions clearfix">
            <a href="<?php echo $app->urlFor('step1', array()); ?>" class="secondary"><?php echo $language["back"];?></a>
            <input type="submit" class="right primary" value="<?php echo $language["forward"];?>"" />
        </div>
    </form>
</div>
