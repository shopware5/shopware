<?php $this->display('header.php');?>
<div id="start">

<?php if ($error){ ?>
    <div class="alert alert-error">
        <?php echo $translation["step2_error"];?>
   </div>
<?php } ?>

    <form action="<?php echo $app->urlFor('database', array()); ?>" method="post">

        <div class="page-header">
                <h2><?php echo $translation["system_requirements_header"];?></h2>
        </div>
        <span class="help-block">
            <?php echo $translation["step2_php_info"];?>
        </span>
        <table class="table table-striped">
        	<thead>
        		<tr>
        			<th><?php echo $translation["step2_system_colcheck"];?></th>
                    <th><?php echo $translation["step2_system_colrequired"];?></th>
                    <th><?php echo $translation["step2_system_colfound"];?></th>
                    <th><?php echo $translation["step2_system_colstatus"];?></th>
        		</tr>
        	</thead>

        	<tbody>
            <?php
               foreach ($system as $result){
            ?>
                <?php
                if ($result["result"]){
                    $class = "success";
                } else {
                    if ($result["error"]){
                        $class = "error";
                    }else {
                        $class = "warning";
                    }
                }
                ?>
                <tr class="<?php echo $class; ?>">
                    <td><?php echo $result["name"] ?></td>
                    <td><?php echo $result["required"] ?></td>
                    <td><?php echo empty($result["version"]) ? "0" : $result["version"] ?></td>
                    <td><?php echo $result["result"] ? '<i class="icon-ok-sign"></i>' : '<i class="icon-minus-sign"></i>' ?></td>
                </tr>
    <?php if(!empty($result["notice"])){ ?>
               <tr class="notice-text">
                   <td colspan="4">
                        <p><i class="icon-info-sign"></i> <?php echo $result["notice"] ?></p>
                   </td>
               </tr>
    <?php } ?>
<?php } ?>
        	</tbody>
        </table>
        <div class="actions clearfix">
            <a href="<?php echo $app->urlFor('index', array()); ?>" class="secondary"><?php echo $translation["back"];?></a>
            <input type="submit" class="right primary" value="<?php echo $translation["forward"];?>" />
        </div>
    </form>
</div>
<?php $this->display('footer.php');?>