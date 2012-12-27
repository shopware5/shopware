<?php
function log_output($filename, $text)
{
        if(Shopware()->Plugins()->Frontend()->PiPaymorrowPayment()->Config()->sandbox_mode)
        {
		$fout = fopen(dirname(__FILE__) . "/../../log/" . $filename, "a+");
		fwrite($fout, $text);
		fclose($fout);
	}
}