<div class="wrap">
<h2>Amazon Express</h2>

<?php

function isPurchased($status) {
  if(strstr($status, 'Completed') != FALSE)
	return TRUE;
  if(strstr($status, 'Processed') != FALSE)
	return TRUE;
  if(strstr($status, 'Canceled_Reversal') != FALSE)
	return TRUE;
  return FALSE;
}

$cmd = "";
if(isset($_GET['cmd']))
	$cmd = $_GET['cmd'];
if($cmd == 'verify')
{
	$url = 'http://www.rampantlogic.com/paypal/status.php?';
	$url .= 'buyer=' . get_bloginfo('admin_email');
	$url .= '&item=Amazon%20Express';
	$result = file_get_contents($url);
	if(isPurchased($result))
	{
		update_option('amazonx_reg', '1');
		echo "<div id='setting-error-settings_updated' class='updated settings-error'>";
		echo "<p><strong>Upgrade successful!</strong></p></div>";
	}
	else 
	{
		echo "<div id='setting-error-settings_updated' class='updated settings-error'>";
		echo "<p><strong>Registration data not found. If you have already purchased the upgrade, ";
		echo "please try again in a few minutes as there may be a delay in processing due to the ";
		echo "PayPal servers. </strong></p></div>";
	}
}
else if($cmd == 'reset')
{
	update_option('amazonx_reg', '0');
	echo "<div id='setting-error-settings_updated' class='updated settings-error'>";
	echo "<p><strong>Registration data deleted.</strong></p></div>";
}
?>

<script>
// from: http://www.webmasterworld.com/forum91/441.htm
function showdiv() { 
	if (document.getElementById) { // DOM3 = IE5, NS6 
		document.getElementById('orderDiv').style.display = 'block'; 
	} 
	else { 
		if (document.layers) { // Netscape 4 
			document.orderDiv.display = 'block'; 
		} 
		else { // IE 4 
			document.all.orderDiv.style.display = 'block'; 
		} 
	} 
} 
</script>


<div id="orderDiv" style="display: none;">
<table style="width: 600px; margin-top: 15px; margin-left:50px; margin-bottom: 25px; border: 1px solid black;">
<tr>
<td colspan="2" style="padding-left: 10px; padding-right: 10px;">
<p style="font-size: 10px;">
If you really don't want to pay, you can hack the code yourself to change the Associate ID, but I would be really grateful if you paid $5 to support continued development.</p> 

<p style="font-size: 10px;">After making your purchase, <b>return to this page</b> and press the "Verify" button to complete the upgrade (click the (change) link again to open this box if the window gets closed). Note that there may be a short delay in the processing of your order due to the PayPal servers.
</p>
</td></tr>
<tr>
<td style="padding-left: 20px; padding-bottom: 10px;">
	<!--<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_blank">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="custom" value="<?php bloginfo('admin_email'); ?>">
	<input type="hidden" name="hosted_button_id" value="74L9HXKU7A2YQ">
	<input type="submit" class="button-primary" value="<?php _e('Buy Now with PayPal') ?>" />
	<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>-->
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="custom" value="<?php bloginfo('admin_email'); ?>">
	<input type="hidden" name="hosted_button_id" value="8P3MFWDBTCA88">
	<input type="submit" class="button-primary" value="<?php _e('Buy Now with PayPal') ?>" />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

</td>
<td align="right" style="padding-right: 20px; padding-bottom: 10px;">
	<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="hidden" name="page" value="amazonx">
	<input type="hidden" name="cmd" value="verify" />
	<input type="submit" class="button-primary" value="<?php _e('Verify Purchase') ?>" />
	</form>
</td>
</tr>
</table>
</div>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<table class="form-table">

<tr valign="top">
<b>Required Settings</b>
<p>To use Amazon Express, you must have an account with Amazon Web Services. If you do not yet have an account, you can sign up for one <a href="http://aws.amazon.com/">here</a> to obtain your Access Key and Secret Key.</p>
</tr>

<tr valign="top">
<th scope="row">Amazon Access Key</th>
<td><input type="text" name="amazonx_accesskey" size="75" value="<?php echo get_option('amazonx_accesskey'); ?>" /></td>
</tr>
 
<tr valign="top">
<th scope="row">Amazon Secret Key</th>
<td><input type="text" name="amazonx_secretkey" size="75" value="<?php echo get_option('amazonx_secretkey'); ?>" /></td>
</tr>

</table>

<br/><br/>

<table class="form-table">
<b>Optional Settings</b>
<p></p>

<tr valign="top">
<th scope="row">Amazon Associate Tag</th>
<?php $check = get_option('amazonx_reg');
if((int)$check == 1): ?>
<td><input type="text" name="amazonx_assoctag" size="75" value="<?php echo get_option('amazonx_assoctag'); ?>" /></td>
<?php else: ?>
<td><b>rampantlogic-20</b> &nbsp;&nbsp; <a href="javascript:showdiv()">(change)</a> </td>
<?php endif; ?>
</tr>

<tr valign="top">
<th scope="row">Below Product Image</th>
<?php
$selected = array("", "", "");
$index = (int)get_option('amazonx_belowimg');
if($index == "")
  $index = 0;
else if($index < 0 || $index > count($selected))
  $index = 1;
$selected[$index] = "selected "
?>
<td><select name="amazonx_belowimg" style="width: 200px">

<option <?php echo $selected[1]; ?>value="1">Buy from Amazon button</option>
<option <?php echo $selected[2]; ?>value="2">Rating</option>
<option <?php echo $selected[0]; ?>value="0">Nothing</option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row">Product Image Width in Post</th>
<td><input type="text" name="amazonx_postwidth" size="15" value="<?php echo get_option('amazonx_postwidth'); ?>" /> pixels (Default: 200)</td>
</tr>

<tr valign="top">
<th scope="row">Product Image Width in List</th>
<td><input type="text" name="amazonx_listwidth" size="15" value="<?php echo get_option('amazonx_listwidth'); ?>" /> pixels (Default: 100)</td>
</tr>

<tr valign="top">
<th scope="row">Product Box Color</th>
<td><input type="text" name="amazonx_boxcolor" size="15" value="<?php echo get_option('amazonx_boxcolor'); ?>" /> <a href="http://www.w3schools.com/css/css_colors.asp">CSS Color</a> (Default: #F0F0F0)</td>
</tr>

<tr valign="top">
<th scope="row">Product Box Border Color</th>
<td><input type="text" name="amazonx_bordercolor" size="15" value="<?php echo get_option('amazonx_bordercolor'); ?>" /> <a href="http://www.w3schools.com/css/css_colors.asp">CSS Color</a> (Default: #D0D0D0)</td>
</tr>

<tr valign="top">
<th scope="row">Filled Star Color</th>
<td><input type="text" name="amazonx_starcolor" size="15" value="<?php echo get_option('amazonx_starcolor'); ?>" /> <a href="http://www.w3schools.com/css/css_colors.asp">CSS Color</a> (Default: #606060)</td>
</tr>

<tr valign="top">
<th scope="row">Empty Star Color</th>
<td><input type="text" name="amazonx_estarcolor" size="15" value="<?php echo get_option('amazonx_estarcolor'); ?>" /> <a href="http://www.w3schools.com/css/css_colors.asp">CSS Color</a> (Default: #C0C0C0)</td>
</tr>

</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="amazonx_accesskey,amazonx_secretkey,amazonx_assoctag,amazonx_postwidth,amazonx_listwidth,amazonx_boxcolor,amazonx_bordercolor,amazonx_belowimg,amazonx_starcolor,amazonx_estarcolor" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

</form>

</div>