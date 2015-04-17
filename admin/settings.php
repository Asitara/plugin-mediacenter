<?php
/*
 * Project:     EQdkp mediacenter
 * License:     Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:        http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:       2008
 * Date:        $Date: 2012-10-13 22:48:23 +0200 (Sa, 13. Okt 2012) $
 * -----------------------------------------------------------------------
 * @author      $Author: godmod $
 * @copyright   2008-2011 Aderyn
 * @link        http://eqdkp-plus.com
 * @package     mediacenter
 * @version     $Rev: 12273 $
 *
 * $Id: settings.php 12273 2012-10-13 20:48:23Z godmod $
 */

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'mediacenter');

$eqdkp_root_path = './../../../';
include_once($eqdkp_root_path.'common.php');


/*+----------------------------------------------------------------------------
  | mediacenterSettings
  +--------------------------------------------------------------------------*/
class MediaCenterSettings extends page_generic
{
  /**
   * __dependencies
   * Get module dependencies
   */
  public static function __shortcuts()
  {
    $shortcuts = array('pm', 'user', 'config', 'core', 'in', 'jquery', 'html', 'tpl');
    return array_merge(parent::$shortcuts, $shortcuts);
  }

  /**
   * Constructor
   */
  public function __construct()
  {
    // plugin installed?
    if (!$this->pm->check('mediacenter', PLUGIN_INSTALLED))
      message_die($this->user->lang('mc_plugin_not_installed'));

    $handler = array(
      'save' => array('process' => 'save', 'csrf' => true),
    );
	
	$this->user->check_auth('a_mediacenter_settings');  
	
    parent::__construct(null, $handler);

    $this->process();
  }
  
  private $arrData = false;

  /**
   * save
   * Save the configuration
   */
  public function save()
  {

  	$objForm = register('form', array('mc_settings'));
  	$objForm->langPrefix = 'mc_';
  	$objForm->validate = true;
  	$objForm->add_fieldsets($this->fields());
  	$arrValues = $objForm->return_values();

  	if ($objForm->error){
  		$this->arrData = $arrValues;
  	} else {
  		if (!$arrValues['watermark_logo']) $arrValues['watermark_logo'] = $this->config->get('watermark_logo', 'mediacenter');
  		
	  	// update configuration
	    $this->config->set($arrValues, '', 'mediacenter');
	    // Success message
	    $messages[] = $this->user->lang('mc_config_saved');
	
	    $this->display($messages);
  	}
   
  }
  
  
  private function fields(){
  	$arrFields = array(
  		'defaults' => array(
  			'per_page' => array(
  				'type' => 'spinner',
  				'max'  => 50,
  				'min'  => 5,
  				'step' => 5,
  				'onlyinteger' => true,
  				'default' => 25,
  			),	
  		),
  		'index_page' => array(
  			'show_featured' => array(
  				'type' => 'radio',
  			),
  			'show_newest' => array(
  				'type' => 'radio',
  			),
  			'show_categories' => array(
  				'type' => 'radio',
  			),
  			'show_mostviewed' => array(
  				'type' => 'radio',
  			),
  			'show_latestcomments' => array(
  				'type' => 'radio',
  			),
  		),
  		'extensions' => array(
	  		'extensions_image' => array(
	  			'type' => 'text',
	  			'size' => 50,
	  		),
	  		
	  		'extensions_file' => array(
	  			'type' => 'text',
	  			'size' => 50,
	  		),
	  		
	  		'extensions_video' => array(
	  			'type' => 'text',
	  			'size' => 50,
	  		),
  		),
  		'watermark' => array(
	  		'watermark_enabled' => array(
  				'type' 			=> 'radio',
	  			'dependency'	=> array(1 => array('watermark_logo', 'watermark_position', 'watermark_transparency')),
  			),
  			'watermark_logo' => array(
  				'type'	=> 'file',
  				'preview' => true,
  				'extensions'	=> array('jpg', 'png'),
  				'mimetypes'		=> array(
  						'image/jpeg',
  						'image/png',
  				),
  				'folder'		=> $this->pfh->FolderPath('watermarks', 'mediacenter'),
  				'numerate'		=> true,
	  		),
  			'watermark_position' => array(
  				'type' => 'dropdown',
  				'options' => $this->user->lang('mc_watermark_positions'),
  			),
  			'watermark_transparency' => array(
  				'type'	=> 'slider',
  				'min'	=> 0,
  				'max'	=> 100,
  				'value' => 0,
  				'width'	=> '300px',
  				'label' => $this->user->lang('mc_watermark_transparency'),
  				'range' => false,
  			),
	  	),
  	);
  
  	return $arrFields;
  }
  

  /**
   * display
   * Display the page
   *
   * @param    array  $messages   Array of Messages to output
   */
  public function display($messages=array())
  {
    // -- Messages ------------------------------------------------------------
    if ($messages)
    {
      foreach($messages as $name)
        $this->core->message($name, $this->user->lang('mediacenter'), 'green');
    }
    
    $arrValues = $this->config->get_config('mediacenter');
    if ($this->arrData !== false) $arrValues = $this->arrData;
    
    if (strlen($arrValues['watermark_logo'])) $arrValues['watermark_logo'] = $this->root_path.$arrValues['watermark_logo'];

    // -- Template ------------------------------------------------------------
	// initialize form class
	$objForm = register('form', array('mc_settings'));
	$objForm->reset_fields();
  	$objForm->lang_prefix = 'mc_';
  	$objForm->validate = true;
  	$objForm->use_fieldsets = true;
  	$objForm->use_dependency = true;
  	$objForm->add_fieldsets($this->fields());
		
	// Output the form, pass values in
	$objForm->output($arrValues);

    // -- EQDKP ---------------------------------------------------------------
    $this->core->set_vars(array(
      'page_title'    => $this->user->lang('mediacenter').' '.$this->user->lang('settings'),
      'template_path' => $this->pm->get_data('mediacenter', 'template_path'),
      'template_file' => 'admin/settings.html',
      'display'       => true
    ));
  }
}

registry::register('MediaCenterSettings');

?>