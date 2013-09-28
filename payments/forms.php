<?php
	$fulldomain = '';//fill in full domain (i.e. https://url.com)
	
if ($financial_form == 'stripe-give') {
?>
<div id="CCloadImg" style="position:absolute;z-index:999;display:table-cell;width:<?=$mwidth?>;height:<?=$mheight?>px;background:#fff;text-align:center;vertical-align:middle;"><div><img src="<?=$fulldomain?>/payments/images/loading.gif" /></div></div><iframe src="<?=$fulldomain?>/payments/index-cc.php<?=$mobile?>" frameborder="0" <? if ($detect->isMobile()) {}else{?>scrolling="no"<? }?> height="<?=$mheight?>" width="<?=$mwidth?>" <? if ($detect->isMobile()) {?>align="middle"<? }?> onload="document.getElementById('CCloadImg').style.display='none';"></iframe><br />
<?
} else{

?>
<p style="text-align:center; font-weight:bold; padding-top:20px;">ERROR: No form was selected in include or selected form does not exist!</p>
<?
}
