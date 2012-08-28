
<img src="{link file='frontend/_resources/images/logo.jpg' fullPath}" />

<div style="height:20px;">&nbsp;</div>
<table width="560" style="background-color:#e14900; color:#fff;">
<tr>
        	<td colspan="3">
            
            <div>
            	<table border="0" cellpadding="0" cellspacing="0" style="color:#fff;">
  <tr height="26" style="border-right:1px solid #e7702e; color:#fff; font-size:11px; font-weight:bold;">
    		            <td align="center" style="padding: 0 5px 0 5px; color:#fff; border-right:1px solid #e7702e;">
            			    <a href="{url module='frontend'}" target="_blank" title="Home" style="color:#fff;text-decoration:none;">{se name='NewsletterHeaderLinkHome'}Home{/se}</a>
            			 </td>
       		          	{foreach from=$sMainCategories item=sMainCategory}
                		<td align="center" style="padding: 0 5px 0 5px; border-right:1px solid #e7702e;" >
			                <a href="{$sMainCategory.link|rewrite:$sMainCategory.description}" style="color:#fff;text-decoration:none;" target="_blank" title="{$sMainCategory.description}">
            			    {$sMainCategory.description}</a>
            			    </td>
        		        {/foreach}</tr>
                </table>
                </div>
               </td>
		</tr>
		</table>
<div style="height: 25px;">&nbsp;</div>