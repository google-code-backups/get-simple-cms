<?php
/**
 * Upload Files
 *
 * Displays and uploads files to the website
 *
 * @package GetSimple
 * @subpackage Files
 * @todo Remove relative paths
 */

 
// Setup inclusions
$load['plugin'] = true;

// Include common.php
include('inc/common.php');

// Variable settings
login_cookie_check();
$path = tsl("../data/uploads/"); #needs to stay a relative path

// if a file was uploaded
if (isset($_FILES["file"]))
{
	if ($_FILES["file"]["error"] > 0)
	{
		$error = i18n_r('ERROR_UPLOAD');
	} 
	else 
	{
		//set variables
		$count = '1';
		$file_loc = $path . clean_img_name($_FILES["file"]["name"]);
		$base = $_FILES["file"]["name"];
		
		//prevent overwriting
		while ( file_exists($file_loc) )
		{
			$file_loc = $path . $count.'-'. clean_img_name($_FILES["file"]["name"]);
			$base = $count.'-'. clean_img_name($_FILES["file"]["name"]);
			$count++;
		}
		
		//create file
		move_uploaded_file($_FILES["file"]["tmp_name"], $file_loc);
		chmod($targetFile, 0644);   
		exec_action('file-uploaded');
		
		//successfull message
		$success = i18n_r('FILE_SUCCESS_MSG').': <a href="'. $SITEURL .'data/uploads/'.$base.'">'. $SITEURL .'data/uploads/'.$base.'</a>';
	}
}


?>

<?php get_template('header', cl($SITENAME).' &raquo; '.i18n_r('FILE_MANAGEMENT')); ?>
	
	<h1><a href="<?php echo $SITEURL; ?>" target="_blank" ><?php echo cl($SITENAME); ?></a> <span>&raquo;</span> <?php i18n('FILE_MANAGEMENT'); ?></h1>
	
	<?php include('template/include-nav.php');?>
	<?php include('template/error_checking.php');?>

	<div class="bodycontent">
	
	<div id="maincontent">
		<div class="main" >
		<h3 class="floated"><?php i18n('UPLOADED_FILES'); ?><span id="filetypetoggle">&nbsp;&nbsp;/&nbsp;&nbsp;<?php i18n('SHOW_ALL'); ?></span></h3>
		
		<div id="file_load">
		<?php

			$count="0";
			$counter = "0";
			$totalsize = 0;
			$filesArray = array();
			$filenames = getFiles($path);

			if (count($filenames) != 0) { 
				foreach ($filenames as $file) {
					if ($file == "." || $file == ".." || is_dir($path . $file) || $file == ".htaccess"  ) {
						// not a upload file
					} else {
						$filesArray[$count]['name'] = $file;
							$ext = substr($file, strrpos($file, '.') + 1);
						$extention = get_FileType($ext);
						$filesArray[$count]['type'] = $extention;
						clearstatcache();
						$ss = stat($path . $file);
						$filesArray[$count]['date'] = date('M j, Y',$ss['ctime']);
						$filesArray[$count]['size'] = fSize($ss['size']);
						$totalsize = $totalsize + $ss['size'];
						$count++;
					}
				}
	
				$filesSorted = subval_sort($filesArray,'name');
			}
				
			echo '<div class="edit-nav" >';
			echo '<select id="imageFilter">';
			echo '<option value="All">'.i18n_r('SHOW_ALL').'</option>';
				
				foreach ($filesSorted as $filter) {
					$filterArr[] = $filter['type'];
				}
				if (count($filterArr) != 0) { 
					$filterArray = array_unique($filterArr);
					$filterArray = subval_sort($filterArray,'type');
					foreach ($filterArray as $type) {
						echo '<option value="'.$type.'">'.$type.'</option>';
					}
				}
			echo '</select><div class="clear" ></div></div>';
			
			if (count($filesSorted) != 0) { 
				echo '<table class="highlight" id="imageTable">';
				foreach ($filesSorted as $upload) {
					$counter++;
					if ($upload['type'] == i18n_r('IMAGES')) {
						$cclass = 'iimage';
					} else {
						$cclass = '';
					}
					echo '<tr class="All '.$upload['type'].' '.$cclass.'" >';
					echo '<td class="imgthumb" >';
					if ($upload['type'] == i18n_r('IMAGES')) {
						$gallery = 'rel="facybox"';
						$pathlink = 'image.php?i='.$upload['name'];
						if (file_exists('../data/thumbs/thumbsm.'.$upload['name'])) {
							echo '<a href="'. $path . $upload['name'] .'" title="'. $upload['name'] .'" rel="facybox" ><img src="../data/thumbs/thumbsm.'.$upload['name'].'" /></a>';
						} else {
							echo '<a href="'. $path . $upload['name'] .'" title="'. $upload['name'] .'" rel="facybox" ><img src="inc/thumb.php?src='. $upload['name'] .'&amp;dest=thumbsm.'. $upload['name'] .'&amp;x=65&amp;f=1" /></a>';
						}
					} else {
						$gallery = '';
						$controlpanel = '';
						$pathlink = $path . $upload['name'];
					}

					echo '</td><td><a title="'.i18n_r('VIEW_FILE').': '. htmlspecialchars($upload['name']) .'" href="'. $pathlink .'" class="primarylink">'.htmlspecialchars($upload['name']) .'</a></td>';
					echo '<td style="width:80px;text-align:right;" ><span>'. $upload['size'] .'</span></td>';
					echo '<td style="width:80px;text-align:right;" ><span>'. shtDate($upload['date']) .'</span></td>';
					echo '<td class="delete" ><a class="delconfirm" title="'.i18n_r('DELETE_FILE').': '. htmlspecialchars($upload['name']) .'" href="deletefile.php?file='. $upload['name'] .'&amp;nonce='.get_nonce("delete", "deletefile.php").'">X</a></td>';
					echo '</tr>';
					exec_action('file-extras');
				}
				echo '</table>';
				echo '<p><em><b>'. $counter .'</b> '.i18n_r('TOTAL_FILES').' ('. fSize($totalsize) .')</em></p>';
			} else {
				echo '<div id="imageTable"></div>';
			}
		?>	
		</div>
	
		</div>
	</div>
	
		<div id="sidebar" >
		<?php include('template/sidebar-files.php'); ?>
		</div>	

	<div class="clear"></div>
	</div>
<?php get_template('footer'); ?>