<?php
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php"; if (!checkperm("u")) {exit ("Permission denied.");}

$plugin_name = 'discount_code';
if(!in_array($plugin_name, $plugins))
    {plugin_activate_for_setup($plugin_name);}

$errorfields=array();

$delete_code=getval("delete_code","");
if ($delete_code!="" && enforcePostRequest(false))
    {
    # Delete discount code.
    $delete_code_escaped = escape_check($delete_code);
    sql_query("delete from discount_code where code='$delete_code_escaped'");
    }
elseif (getval("add","")!="" && enforcePostRequest(false))
    {
    $dateParsed=date_create_from_format("Y-m-d",getval("expires",""));
    if($dateParsed)
        {
        # Add discount code.
        sql_query("delete from discount_code where code='" . getvalescaped("code","") . "'"); # Clear any existing matching code.
        sql_query("insert into discount_code(code,percent,expires) values ('" . getvalescaped("code","") . "','" . getvalescaped("percent","") . "','" . getvalescaped("expires","") . "');");
        }
    else
        {
        $errorfields[]="code-expires";   
        }
    }

$discount_codes=sql_query("select code,percent,expires from discount_code order by code");

include "../../../include/header.php";
?>
<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
  <h1><?php echo $lang["discount_code_configuration"] ?></h1>

  <div class="VerticalNav">

<table class="InfoTable">
<tr>
<td><strong><?php echo $lang["code"] ?></strong></th>
<td><strong><?php echo $lang["discount-percentage"] ?></strong></th>
<td><strong><?php echo $lang["code-expires"] ?></strong></th>
<td> &nbsp; </td>
</tr>
<?php foreach ($discount_codes as $discount_code) { ?>
<tr>
<td><?php echo $discount_code["code"] ?></td>
<td><?php echo $discount_code["percent"] ?></td>
<td><?php echo $discount_code["expires"] ?></td>
<td><a href="#" onClick="if (confirm('<?php echo $lang["confirm-delete-code"] ?>')) {document.getElementById('delete_code').value='<?php echo $discount_code["code"] ?>';document.getElementById('discountform').submit();}">&gt;&nbsp;<?php echo $lang["action-delete"] ?></a></td>
</tr>
<?php } ?>
</table>

<Br><br><br>
<h2><?php echo $lang["add_discount_code"] ?></h2>
<form id="discountform" name="discountform" method="post" action="">
    <?php generateFormToken("discountform"); ?>
<input type="hidden" name="delete_code" id="delete_code" value="">

<?php echo $lang["code"] . ": " ?><input type="text" name="code" size="20" value="<?php echo $lang["newcode"] ?>">
&nbsp;
<?php echo $lang["discount"] . ": " ?><input type="text" name="percent" size="2" value="10">%
&nbsp;
<?php echo $lang["code-expires-yyyymmdd"] . " " ?><input type="text" id="code-expires" name="expires" size="12" value="<?php echo date("Y-m-d",time()+60*60*24*100) ?>">
<input type="submit" name="add" value="<?php echo $lang["add_code"] ?>">   
</form>

</div>  
</div>  
<?php
if (count($errorfields)>0)
    {
    echo "<div class=\"PageInformal\">";
    foreach($errorfields as $errorfield)
        {
        echo $lang["error_" . $errorfield] . PHP_EOL;
        ?>
        <script>
        jQuery(document).ready(function(){
            jQuery('#<?php echo $errorfield; ?>').addClass('highlighted');
        });
        </script>
        <?php
        }
    echo "</div>";
    }
include "../../../include/footer.php";
?>
