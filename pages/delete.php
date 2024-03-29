<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
include "../include/resource_functions.php";

$ref=getvalescaped("ref","",true);

if ((isset($allow_resource_deletion) and !$allow_resource_deletion) or (checkperm('D') and !hook('check_single_delete'))){
	include "../include/header.php";
	echo "Error: Resource deletion is disabled.";
	exit;
} else {
$resource=get_resource_data($ref);

# fetch the current search 
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive",0,true);

$modal=(getval("modal","")=="true");
$default_sort_direction="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_direction="ASC";}
$sort=getval("sort",$default_sort_direction);

$error="";

# Not allowed to edit this resource? They shouldn't have been able to get here.
if (!get_edit_access($ref,$resource["archive"],false,$resource)) {exit ("Permission denied.");}

hook("pageevaluation");

if (getval("save","")!="" && enforcePostRequest(getval("ajax", false)))
	{
	if ($delete_requires_password && hash('sha256', md5('RS' . $username . getvalescaped('password', ''))) != $userpassword)
		{
		$error=$lang["wrongpassword"];
		}
	else
		{
		hook("custompredeleteresource");

		delete_resource($ref);
		
		hook("custompostdeleteresource");
		
		echo "<script>
		ModalLoad('" . $baseurl_short . "pages/done.php?text=deleted&refreshcollection=true&search=" . urlencode($search) . "&offset=" . urlencode($offset) . "&order_by=" . urlencode($order_by) . "&sort=" . urlencode($sort) . "&archive=" . urlencode($archive) . "',true);
		</script>";
		exit();
		}
	}
include "../include/header.php";

if (isset($resource['is_transcoding']) && $resource['is_transcoding']==1)
	{
?>
<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
  <h1><?php echo $lang["deleteresource"];render_help_link("user/deleting-resources");?></h1>
  <p class="FormIncorrect"><?php echo $lang["cantdeletewhiletranscoding"]?></p>
</div>
<?php	
	}
else
	{
		
if(!$modal)
		{
		?>
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>"><?php echo LINK_CARET_BACK ?><?php echo $lang["backtoresourceview"]?></a>
		<?php
		}
?>

<div class="BasicsBox"> 
  <h1><?php echo $lang["deleteresource"];render_help_link("user/deleting-resources");?></h1>
  <p><?php if($delete_requires_password){text("introtext");}else{echo $lang["delete__nopassword"];} ?></p>
  
  <?php if ($resource["archive"]==3) { ?><p><strong><?php echo $lang["finaldeletion"] ?></strong></p><?php } ?>
  
	<form method="post" action="<?php echo $baseurl_short?>pages/delete.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>">
	<input type=hidden name=ref value="<?php echo urlencode($ref) ?>">
    <?php generateFormToken("delete_resource"); ?>
	<div class="Question">
	<label><?php echo $lang["resourceid"]?></label>
	<div class="Fixed"><?php echo urlencode($ref) ?></div>
	<div class="clearerleft"> </div>
	</div>
	
	<?php if ($delete_requires_password) { ?>
	<div class="Question">
	<label for="password"><?php echo $lang["yourpassword"]?></label>
	<input type=password class="shrtwidth" name="password" id="password" />
	<div class="clearerleft"> </div>
	<?php if ($error!="") { ?><div class="FormError">!! <?php echo htmlspecialchars($error) ?> !!</div><?php } ?>
	</div>
	<?php } ?>
	
	<div class="QuestionSubmit">
	<input name="save" type="hidden" value="true" />
	<label for="buttons"> </label>			
	<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["deleteresource"]?>&nbsp;&nbsp;"  onclick="return ModalPost(this.form,true);"/>
	</div>
	</form>
	
</div>

<?php
	}

} // end of block to prevent deletion if disabled
	
include "../include/footer.php";

?>
