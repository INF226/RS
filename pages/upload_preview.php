<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php"; if ($disable_upload_preview || checkperm("F*")) {exit ("Permission denied.");}
include "../include/image_processing.php";
include "../include/resource_functions.php";

$ref=getvalescaped("ref","",true);
$status="";
$error = false;
$resource=get_resource_data($ref);
# Not allowed to edit this resource?
if (!get_edit_access($ref,$resource["archive"],false,$resource)) {
		$error=$lang['error-permissiondenied'];
		error_alert($error);
		exit();
		}

# fetch the current search 
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive",0,true);

$default_sort_direction="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_direction="ASC";}
$sort=getval("sort",$default_sort_direction);

#handle posts
if (array_key_exists("userfile",$_FILES) && enforcePostRequest(false))
    {
	$status=upload_preview($ref);
    if($status !== false)
        {
        redirect($baseurl_short."pages/edit.php?refreshcollectionframe=true&ref=" . urlencode($ref)."&search=".urlencode($search)."&offset=".urlencode($offset)."&order_by=".urlencode($order_by)."&sort=".urlencode($sort)."&archive=".urlencode($archive));
        exit();
        }
    $error = true;
    }
    
include "../include/header.php";
?>

<div class="BasicsBox"> 
<h1><?php echo $lang["uploadpreview"];render_help_link("user/edit-resource-preview");?></h1>
<p><?php echo text("introtext")?></p>
<script language="JavaScript">
// Check allowed extensions:
function check(filename) {
	var allowedExtensions='jpg,jpeg';
	var ext = filename.substr(filename.lastIndexOf('.'));
	ext =ext.substr(1).toLowerCase();
	if (allowedExtensions.indexOf(ext)==-1){ return false;} else {return true;}
}
</script>
<form method="post" class="form" enctype="multipart/form-data" action="upload_preview.php">
<?php generateFormToken("upload_preview"); ?>
<input type="hidden" name="ref" value="<?php echo htmlspecialchars($ref)?>">
<br/>
<?php if ($status!="") { ?><?php echo $status?><?php } ?>
<div id="invalid" <?php if (!$error) {echo "style=\"display:none;\"";} ?> class="FormIncorrect"><?php echo str_replace_formatted_placeholder("%extensions", "JPG", $lang['invalidextension_mustbe-extensions']); ?></div>
<div class="Question">
<label for="userfile"><?php echo $lang["clickbrowsetolocate"]?></label>
<input type=file name=userfile id=userfile>
<div class="clearerleft"> </div>
</div>

<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="save" type="submit" onclick="if (!check(this.form.userfile.value)){document.getElementById('invalid').style.display='block';return false;}else {document.getElementById('invalid').style.display='none';}" value="&nbsp;&nbsp;<?php echo $lang["upload_file"]?>&nbsp;&nbsp;" />
</div>

<p><a onClick="return CentralSpaceLoad(this,true);" href="edit.php?ref=<?php echo urlencode($ref)?>"><?php echo LINK_CARET_BACK ?><?php echo $lang["backtoeditresource"]?></a></p>

</form>
</div>

<?php
include "../include/footer.php";
?>
