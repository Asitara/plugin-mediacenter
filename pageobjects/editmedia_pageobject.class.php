<?php
/*
 * Project:     EQdkp guildrequest
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:        http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2012-10-13 22:48:23 +0200 (Sa, 13. Okt 2012) $
 * -----------------------------------------------------------------------
 * @author      $Author: godmod $
 * @copyright   2008-2011 Aderyn
 * @link        http://eqdkp-plus.com
 * @package     guildrequest
 * @version     $Rev: 12273 $
 *
 * $Id: archive.php 12273 2012-10-13 20:48:23Z godmod $
 */


class editmedia_pageobject extends pageobject {
  /**
   * __dependencies
   * Get module dependencies
   */
  public static function __shortcuts()
  {
    $shortcuts = array();
   	return array_merge(parent::__shortcuts(), $shortcuts);
  }  

  private $blnAdminMode = false;
  
  /**
   * Constructor
   */
  public function __construct()
  {
    // plugin installed?
    if (!$this->pm->check('mediacenter', PLUGIN_INSTALLED))
      message_die($this->user->lang('mc_plugin_not_installed'));
    
    //Check Permissions
    if (!$this->user->check_auth('u_mediacenter_view', false) || !$this->user->is_signedin()){
    	$this->user->check_auth('u_mediacenter_something');
    }
    
    $this->blnAdminMode = ($this->in->get('admin', 0) && $this->user->check_auth('a_mediacenter_manage', false)) ? 1 : 0;
    
    $handler = array(
      'save' => array('process' => 'save', 'csrf' => true),
      'reload_albums' => array('process' => 'ajax_reload_albums'),
      'media_types' => array('process' => 'ajax_media_types'),
      'upload' => array('process' => 'upload_file'),
      'massupload' => array('process' => 'upload_massupload'),
      'imageedit' => array(
				array('process' => 'ajax_imageedit_rotate', 'value' => 'rotate'),
				array('process' => 'ajax_imageedit_resize', 'value' => 'resize'),
				array('process' => 'ajax_imageedit_restore', 'value' => 'restore'),
      			array('process' => 'ajax_imageedit_mirror', 'value' => 'mirror'),
	  ),
      'del_votes' => array('process' => 'delete_votes', 'csrf' => true),
      'del_comments' => array('process' => 'delete_comments', 'csrf' => true),
    );
    parent::__construct(false, $handler);

    $this->process();
  }
  
  private $arrData = array();
  
  public function delete_comments(){
  	$intMediaID = $this->url_id;
  	if (!$this->user->check_auth('a_mediacenter_manage', false)) return false;
  	
  	if ($intMediaID) {
  		$this->pdh->put('comment', 'delete_attach_id', array('mediacenter', $intMediaID));
  		$this->pdh->process_hook_queue();
  		$this->logs->add('action_mediacenter_reset_comments', array(), $intMediaID, $this->pdh->get('mediacenter_media', 'name', array($intMediaID)), 1, 'mediacenter');
  		$this->core->message($this->user->lang('mc_f_delete_comments'), $this->user->lang('success'), 'green');
  	}

  }
  
  public function delete_votes(){
  	$intMediaID = $this->url_id;
  	if (!$this->user->check_auth('a_mediacenter_manage', false)) return false;
  	
  	
  	if ($intMediaID) {
  		$blnResult = $this->pdh->put('mediacenter_media', 'reset_votes', array($intMediaID));
  		if ($blnResult){
  			$this->core->message($this->user->lang('mc_f_delete_votes'), $this->user->lang('success'), 'green');
  			$this->pdh->process_hook_queue();
  		}
  	}

  }
 
  public function ajax_imageedit_rotate(){
  	$intMediaID = $this->in->get('id', 0);
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  		echo "error";
  		return false;
  	}
  	
  	$dir = $this->in->get('dir', 'r');
  	 
  	if (!$intMediaID) {
  		echo "error";
  		return false;
  	}
  	$image = $this->pfh->FolderPath('thumbs', 'mediacenter').$this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID));
  	 
  	$imageInfo		= GetImageSize($image);
  	if (!$imageInfo) {
  		echo "error";
  		return false;
  	}
  	 
  	switch($imageInfo[2]){
  		case 1:	$imgOld = ImageCreateFromGIF($image);	break;	// GIF
  		case 2:	$imgOld = ImageCreateFromJPEG($image);	break;	// JPG
  		case 3:
  			$imgOld = ImageCreateFromPNG($image);
  			imageAlphaBlending($imgOld, false);
  			imageSaveAlpha($imgOld, true);
  			break;	// PNG
  	}
  	$rotang = ($dir == 'r') ? 270 : 90;
  	$rotation = imagerotate($imgOld, $rotang, imageColorAllocateAlpha($imgOld, 0, 0, 0, 127));
  	imagealphablending($rotation, false);
  	imagesavealpha($rotation, true);
  	
  	switch($imageInfo[2]){
  		case 1:	ImageGIF($rotation,	$image);	break;	// GIF
  		case 2:	ImageJPEG($rotation,	$image, 100);	break;	// JPG
  		case 3:	ImagePNG($rotation,	$image, 0);	break;	// PNG
  	}
  	
  	imagedestroy($rotation);
  	imagedestroy($imgOld);
  	
  	//Create new Thumbnails
  	$strExtension = pathinfo($this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID)), PATHINFO_EXTENSION);
  	$filename = pathinfo($this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID)), PATHINFO_FILENAME);
  	$this->pfh->thumbnail($image, $this->pfh->FolderPath('thumbs', 'mediacenter'), $filename.'.64.'.$strExtension, 64);
  	$this->pfh->thumbnail($image, $this->pfh->FolderPath('thumbs', 'mediacenter'), $filename.'.240.'.$strExtension, 240);		
  	
  	echo $image;
  	exit;
  }
  
  public function ajax_imageedit_restore(){
  	$intMediaID = $this->in->get('id', 0);
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  		echo "error";
  		return false;
  	}
  	
  	$src = $this->pfh->FolderPath('files', 'mediacenter').$this->pdh->get('mediacenter_media', 'localfile', array($intMediaID));
  	$dest = $this->pfh->FolderPath('thumbs', 'mediacenter').$this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID));
  	$this->pfh->copy($src, $dest);
  	
  	//Create new Thumbnails
  	$strExtension = pathinfo($this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID)), PATHINFO_EXTENSION);
  	$filename = pathinfo($this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID)), PATHINFO_FILENAME);
  	$this->pfh->thumbnail($image, $this->pfh->FolderPath('thumbs', 'mediacenter'), $filename.'.64.'.$strExtension, 64);
  	$this->pfh->thumbnail($image, $this->pfh->FolderPath('thumbs', 'mediacenter'), $filename.'.240.'.$strExtension, 240);
  	
  	echo "true";
  	exit;
  }
  
  public function ajax_imageedit_resize(){
  	$intMediaID = $this->in->get('id', 0);
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  		echo "error";
  		return false;
  	}
  	
  	$x = $this->in->get('x', 0);
  	$y = $this->in->get('y', 0);
  	$w = $this->in->get('w', 0);
  	$h = $this->in->get('h', 0);
  	
  	if (!$intMediaID) {
  		echo "error";
  		return false;
  	}
  	$image = $this->pfh->FolderPath('thumbs', 'mediacenter').$this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID));
  	
  	$imageInfo		= GetImageSize($image);
  	if (!$imageInfo) {
  		echo "error";
  		return false;
  	}
  	
  	switch($imageInfo[2]){
  		case 1:	$imgOld = ImageCreateFromGIF($image);	break;	// GIF
  		case 2:	$imgOld = ImageCreateFromJPEG($image);	break;	// JPG
  		case 3:
  			$imgOld = ImageCreateFromPNG($image);
  			imageAlphaBlending($imgOld, false);
  			imageSaveAlpha($imgOld, true);
  			break;	// PNG
  	}
  	
  	$dst = ImageCreateTrueColor( $w, $h );
  	
  	imagecopyresampled($dst,$imgOld,0,0,$x,$y,
  	$w,$h,$w,$h);
  	
  	switch($imageInfo[2]){
  		case 1:	ImageGIF($dst,	$image);	break;	// GIF
  		case 2:	ImageJPEG($dst,	$image, 100);	break;	// JPG
  		case 3:	ImagePNG($dst,	$image, 0);	break;	// PNG
  	}
  	imagedestroy($imgOld);
  	imagedestroy($dst);
  	
  	//Create new Thumbnails
  	$strExtension = pathinfo($this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID)), PATHINFO_EXTENSION);
  	$filename = pathinfo($this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID)), PATHINFO_FILENAME);
  	$this->pfh->thumbnail($image, $this->pfh->FolderPath('thumbs', 'mediacenter'), $filename.'.64.'.$strExtension, 64);
  	$this->pfh->thumbnail($image, $this->pfh->FolderPath('thumbs', 'mediacenter'), $filename.'.240.'.$strExtension, 240);
  	
  	echo $image;
  	exit;
  }
  
  public function ajax_imageedit_mirror(){
  	$intMediaID = $this->in->get('id', 0);
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  		echo "error";
  		return false;
  	}
  	
  	$dir = $this->in->get('dir', 'h');
  	
  	if (!$intMediaID) {
  		echo "error";
  		return false;
  	}
  	$image = $this->pfh->FolderPath('thumbs', 'mediacenter').$this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID));
  	
  	$imageInfo		= GetImageSize($image);
  	if (!$imageInfo) {
  		echo "error";
  		return false;
  	}
  	
  	switch($imageInfo[2]){
  		case 1:	$imgOld = ImageCreateFromGIF($image);	break;	// GIF
  		case 2:	$imgOld = ImageCreateFromJPEG($image);	break;	// JPG
  		case 3:
  			$imgOld = ImageCreateFromPNG($image);
  			imageAlphaBlending($imgOld, false);
  			imageSaveAlpha($imgOld, true);
  			break;	// PNG
  	}
  	
  	// Flip it
  	imageflip($imgOld, ($dir == 'h') ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
  	
  	switch($imageInfo[2]){
  		case 1:	ImageGIF($imgOld,	$image);	break;	// GIF
  		case 2:	ImageJPEG($imgOld,	$image, 100);	break;	// JPG
  		case 3:	ImagePNG($imgOld,	$image, 0);	break;	// PNG
  	}
  	imagedestroy($imgOld);
  	
  	//Create new Thumbnails
  	$strExtension = pathinfo($this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID)), PATHINFO_EXTENSION);
  	$filename = pathinfo($this->pdh->get('mediacenter_media', 'previewimage', array($intMediaID)), PATHINFO_FILENAME);
  	$this->pfh->thumbnail($image, $this->pfh->FolderPath('thumbs', 'mediacenter'), $filename.'.64.'.$strExtension, 64);
  	$this->pfh->thumbnail($image, $this->pfh->FolderPath('thumbs', 'mediacenter'), $filename.'.240.'.$strExtension, 240);
  	
  	echo $image;
  	exit;
  }
  
  public function ajax_reload_albums(){
  	header('content-type: text/html; charset=UTF-8');
 	
  	$arrAlbumIDs = $this->pdh->get('mediacenter_albums', 'id_list');
  	$arrAlbums = array();
  	foreach ($arrAlbumIDs as $albumID){
  		$arrAlbums[$albumID] = $this->pdh->get('mediacenter_albums', 'name', array($albumID)).' ('.$this->pdh->get('mediacenter_categories', 'name', array($this->pdh->get('mediacenter_albums', 'category_id', array($albumID)))).')';
  	}
  	
  	echo new hdropdown('album_id', array('options' => $arrAlbums));
  	
  	exit;
  }
  
  public function ajax_media_types(){
  	header('content-type: text/html; charset=UTF-8');
  	$intAlbumID = $this->in->get('album', 0);
  	$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
  	$arrTypes = $this->pdh->get('mediacenter_categories', 'types', array($intCategoryID));
  	$myArray = $this->user->lang('mc_types');
  	if (count($arrTypes) == 1){
  		$tmp = array();
  		$tmp[$arrTypes[0]] = $myArray[$arrTypes[0]];
  		$myArray = $tmp;
  	} elseif(count($arrTypes) > 1) {
		$tmp = array();
		foreach($arrTypes as $typeid){
			$tmp[$typeid] = $myArray[$typeid];
		}
		$myArray = $tmp;
  	}
  	echo new hdropdown('type', array('js' => 'onchange="handle_type(this.value)"', 'options' => $myArray));
  	exit;
  }
  
  public function upload_massupload(){
  	//TODO: Make this an Setting
  	$arrAllowedExtensions = array('jpg', 'jpeg', 'png', 'zip');
  	
  	$intAlbumID = $this->in->get('album_id', 0);
  	if (!$intAlbumID) {
  		echo "error";
  		exit();
  	}
  	
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array($intAlbumID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if (!$arrPermissions || !$arrPermissions['create']){
  		echo "error";
  		exit();
  	}
  	
  	//Check if Personal Album
  	if ($this->pdh->get('mediacenter_albums', 'personal_album', array($intAlbumID))){
  		if (!$this->user->check_auth('a_mediacenter_manage', false) && $this->user->id != $this->pdh->get('mediacenter_albums', 'user_id', array($intAlbumID))) {
  			echo "error";
  			exit();
  		};
  	}
  	  	 
  	$folder = $this->pfh->FolderPath('files', 'mediacenter');
  	$this->pfh->secure_folder('files', 'mediacenter');
  	 
  	$tempname		= $_FILES['file']['tmp_name'];
  	$filename		= $_FILES['file']['name'];
  	$filetype		= $_FILES['file']['type'];
  	if ($tempname == '') {
  		echo "error";
  		exit();
  	}
  	 
  	$fileEnding		= strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  	if (!in_array($fileEnding, $arrAllowedExtensions)) {
  		echo "error";
  		exit();
  	}
  	 
  	$new_filename = md5(rand().rand().rand().unique_id());
  	 
  	$this->pfh->FileMove($tempname, $folder.$new_filename, true);
  	
  	$result = $this->pdh->put('mediacenter_media', 'add_massupdate', array($intAlbumID, $filename, $new_filename));
  	if (!$result) {
  		echo "error result"; exit();
  	}
  	
  	$this->pdh->process_hook_queue();
  	die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
  	exit;
  }
  
  public function upload_file(){
  	//TODO: Make this an Setting
  	$arrAllowedExtensions = array('jpg', 'jpeg', 'png', 'zip');
  	
  	$folder = $this->pfh->FolderPath('files', 'mediacenter');
  	$this->pfh->secure_folder('files', 'mediacenter');
  	
  	$tempname		= $_FILES['file']['tmp_name'];
  	$filename		= $_FILES['file']['name'];
  	$filetype		= $_FILES['file']['type'];
  	if ($tempname == '') {
  		echo "error";
  		exit();
  	}
  	
  	$fileEnding		= strtolower(pathinfo($filename, PATHINFO_EXTENSION));		
  	if (!in_array($fileEnding, $arrAllowedExtensions)) {
  		echo "error";
  		exit();
  	}
  	
  	$new_filename = md5(rand().rand().rand().unique_id());
  	
  	$this->pfh->FileMove($tempname, $folder.$new_filename, true);
  	
  	header('content-type: text/html; charset=UTF-8');
  	echo register('encrypt')->encrypt($new_filename);
  	  	
  	exit;
  }
  
  public function save(){
  	$objForm = register('form', array('editalbum'));
  	$objForm->langPrefix = 'mc_';
  	$objForm->validate = true;
  	$objForm->add_fields($this->fields());
  	$arrValues = $objForm->return_values();
  	$mixResult = false;
  	
  	//Check if Personal Album
  	if ($this->pdh->get('mediacenter_albums', 'personal_album', array((int)$arrValues['album_id']))){
  		if (!$this->user->check_auth('a_mediacenter_manage', false) && $this->user->id != $this->pdh->get('mediacenter_albums', 'user_id', array((int)$arrValues['album_id']))) {
  			$this->user->check_auth('u_mediacenter_something');
  		};
  	}
  	
  	if ($objForm->error){
  		$this->arrData = $arrValues;
  		$this->display();
  	} else {
  		if ($this->url_id) {
  			
  			//Check Permissions
  			$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array((int)$arrValues['album_id']));
  			$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  			if ((!$arrPermissions || !$arrPermissions['update']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  				$this->user->check_auth('u_mediacenter_something');
  			}
  			
  			if ($this->blnAdminMode) {
  				//$intAlbumID, $strName, $strDescription, $intType, $strExternalLink, $strPreviewimage, $strTags, $strFile, $strFilename
  				//$intPublished=false, $intFeatured=false, $intUserID=false, $intViews=false, $intReported=false
	  			$mixResult = $this->pdh->put('mediacenter_media', 'update_media', array(
	  					$this->url_id, (int)$arrValues['album_id'], $arrValues['name'], $arrValues['description'], (int)$arrValues['type'], $arrValues['externalfile'], $arrValues['previewimage'], $arrValues['tags'], $this->in->get('localfile'), $this->in->get('filename'),
	  					(int)$arrValues['published'], (int)$arrValues['featured'], (int)$arrValues['user_id'], (int)$arrValues['views'],(int)$arrValues['reported'], (int)$arrValues['downloads']
	  			));
  			}else {
  				$mixResult = $this->pdh->put('mediacenter_media', 'update_media', array(
  						$this->url_id, (int)$arrValues['album_id'], $arrValues['name'], $arrValues['description'], (int)$arrValues['type'], $arrValues['externalfile'], $arrValues['previewimage'], $arrValues['tags'], $this->in->get('localfile'), $this->in->get('filename')
  				));
  			}
  			
  		} else {
  			//Check Permissions
  			$intCategoryID = $this->pdh->get('mediacenter_albums', 'category_id', array((int)$arrValues['album_id']));
  			$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  			if ((!$arrPermissions || !$arrPermissions['create']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  				$this->user->check_auth('u_mediacenter_something');
  			}
  			
  			if ($this->blnAdminMode){
  			
	  			//$intAlbumID, $strName, $strDescription, $intType, $strExternalLink, $strPreviewimage, $strTags, $strFile, $strFilename
  				//$intPublished=false, $intFeatured=false, $intUserID=false, $intViews=false
	  			$mixResult = $this->pdh->put('mediacenter_media', 'insert_media', array(
	  				(int)$arrValues['album_id'], $arrValues['name'], $arrValues['description'], (int)$arrValues['type'], $arrValues['externalfile'], $arrValues['previewimage'], $arrValues['tags'], $this->in->get('localfile'), $this->in->get('filename'),
	  				(int)$arrValues['published'], (int)$arrValues['featured'], (int)$arrValues['user_id'], (int)$arrValues['views']
	  			));
  			
  			} else {
  				$mixResult = $this->pdh->put('mediacenter_media', 'insert_media', array(
  						(int)$arrValues['album_id'], $arrValues['name'], $arrValues['description'], (int)$arrValues['type'], $arrValues['externalfile'], $arrValues['previewimage'], $arrValues['tags'], $this->in->get('localfile'), $this->in->get('filename')
  				));
  			}
  		}
  		$this->pdh->process_hook_queue();
  	}
  	
  	if ($mixResult){
  		$this->core->message($this->user->lang('save_suc'), $this->user->lang('success'), 'green');
  	}
  	
  	if ($this->in->get('simple_head')){
  		$this->tpl->add_js('$.FrameDialog.closeDialog();', 'docready');
  	}
  }
  
  public function delete(){
  	$intMediaID = $this->url_id;
  	 
  	//Check Permissions
  	$intCategoryID = $this->pdh->get('mediacenter_media', 'category_id', array($intMediaID));
  	$arrPermissions = $this->pdh->get('mediacenter_categories', 'user_permissions', array($intCategoryID, $this->user->id));
  	if ((!$arrPermissions || !$arrPermissions['delete']) && !$this->user->check_auth('a_mediacenter_manage', false)){
  		return false;
  	}
  	
  	$blnResult = $this->pdh->put('mediacenter_media', 'delete', array($intMediaID));
  	if ($blnResult){
  		$this->pdh->process_hook_queue();
  		$this->core->message($this->user->lang('del_suc'), $this->user->lang('success'), 'green');
  	}
  }
  
  
  public function display(){
	$objForm = register('form', array('editalbum'));
	$objForm->langPrefix = 'mc_';
	$objForm->validate = true;
	$objForm->add_fields($this->fields());
	
	
	$arrValues = array();
  	if ($this->url_id) {
  		$arrValues = $this->pdh->get('mediacenter_media', 'data', array($this->url_id));
  		$arrValues['tags'] = implode(", ", unserialize($arrValues['tags']));
  		$arrValues['previewimage'] = (strlen($arrValues['previewimage'])) ? $this->pfh->FolderPath('thumbs', 'mediacenter', 'absolute').$arrValues['previewimage'] : false;
  		
  		$this->tpl->assign_vars(array(
  			'S_EDIT'		=> true,
  			'LOCALFILE'		=> $arrValues['filename'],
  			'S_TYPE_IMAGE'	=> ($arrValues['type'] == 2 && ($arrPermissions['update'] || $this->user->check_auth('a_mediacenter_manage', false))) ? true : false,
  			'LOCAL_IMAGE'	=> $arrValues['previewimage'],
  			'IMAGE_ID'		=> $this->url_id,
  		));
  	}

  	$this->jquery->Tab_header('editmedia_tab');
  	$this->tpl->assign_vars(array(
  		'DD_ALBUMS' => new hdropdown('albums_massupload', array('options' => $this->pdh->get('mediacenter_albums', 'category_tree'), 'value' => $this->in->get('aid', 0), 'js' => 'onchange="set_albums(this.value)"')),
  		'ADMINMODE'	=> $this->blnAdminMode,
  	));
  	
	//Output, with Values
	if (count($this->arrData)) $arrValues = $this->arrData;
	
	//Set Album ID
	if ($this->in->get('aid', 0)) $arrValues['album_id'] = $this->in->get('aid', 0);
	
	$objForm->output($arrValues);
	
	$this->jquery->Dialog('addalbum', $this->user->lang('mc_new_album'), array('url'=> $this->controller_path.'AddAlbum/'.$this->SID.'&simple_head=1', 'width'=>'640', 'height'=>'520', 'onclosejs'=>'reload_albums();'));
	
    // -- EQDKP ---------------------------------------------------------------
    $this->core->set_vars(array (
      'page_title'    => $this->user->lang('mc_edit_media'),
      'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
      'template_file' => 'media_edit.html',
      'display'       => true
    ));	
  }
  
  
  //Get Fields for Form
  private function fields(){
  	$arrAlbums = $this->pdh->get('mediacenter_albums', 'category_tree');
  	
  	$arrMediaTypes = array();
  	  	
  	$arrFields = array(
  		'album_id' => array(
  			'type' => 'dropdown',
  			'lang' => 'mc_f_album',
  			'text2' => '<button onclick="addalbum()" type="button"><i class="fa fa-plus"></i> '.$this->user->lang('mc_new_album').'</button>',
  			'options' => $arrAlbums,
  			'js'	=> 'onchange="load_mediatypes();"'
  		),
  		'name' => array(
  			'type'	=> 'text',
  			'size'	=> 40,
  			'required' => true,
  			'lang' => 'mc_f_name',
  		),
  		'type' => array(
  			'type' => 'dropdown',
  			'options' => $this->user->lang('mc_types'),
  			'lang' => 'mc_f_type',
  			'js'	=> 'onchange="handle_type(this.value)"'
  		),
  		'description' => array(
  			'type' => 'bbcodeeditor',
  			'lang' => 'mc_f_description',
  		),
  		'externalfile' => array(
  			'type'	=> 'text',
  			'size'	=> 40,
  			'lang' => 'mc_f_externalfile',
  		),
  		'previewimage' => array(
  			'type'	=> 'file',
  			'lang'	=> 'mc_f_previewimage',
  			'preview' => true,
  			'extensions'	=> array('jpg', 'png'),
			'mimetypes'		=> array(
					'image/jpeg',
					'image/png',
			),
			'folder'		=> $this->pfh->FolderPath('previewimages', 'mediacenter'),
			'numerate'		=> true,
  		),
  		'tags' => array(
  			'type'	=> 'text',
  			'size'	=> 40,
  			'lang' => 'mc_f_tags',
  		),
  	);
  	
  	if ($this->blnAdminMode){
  		$arrUser = $this->pdh->aget('user', 'name', 0, array($this->pdh->get('user', 'id_list')));
  		natcasesort($arrUser);
  		$arrFields['user_id'] = array(
  				'type'		=> 'dropdown',
  				'options'	=> $arrUser,
  				'lang'		=> 'user',
  		);
  		$arrFields['published'] = array(
  				'type'		=> 'radio',
  				'lang'		=> 'mc_f_published',
  		);
  		$arrFields['featured'] = array(
  				'type'		=> 'radio',
  				'lang'		=> 'mc_f_featured',
  		);
  		if ($this->url_id){
  			$arrFields['views'] = array(
  					'type'		=> 'int',
  					'lang'		=> 'mc_f_views',
  			);
  			$arrFields['downloads'] = array(
  					'type'		=> 'int',
  					'lang'		=> 'mc_f_downloads',
  			);
  			
	  		$arrFields['reported'] = array(
	  				'type'		=> 'radio',
	  				'lang'		=> 'mc_f_reported',
	  		);
	  		$arrFields['del_comments'] = array(
	  				'type'		=> 'button',
	  				'lang'		=> 'mc_f_delete_comments',
	  				'buttontype' => 'submit',
	  				'buttonvalue' => '<i class="fa fa-trash"></i> '.$this->user->lang('mc_f_delete_comments'),
	  		);
	  		$arrFields['del_votes'] = array(
	  				'type'		=> 'button',
	  				'lang'		=> 'mc_f_delete_votes',
	  				'buttontype' => 'submit',
	  				'buttonvalue' => '<i class="fa fa-trash"></i> '.$this->user->lang('mc_f_delete_votes'),	
	  		);
  		}
  	}
  	  	
  	return $arrFields;
  }
}
?>