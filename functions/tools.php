<?php

function dolimenu($name, $traduction, $right, $content) {


}

function doliversion($version) {
$ret = false;
if (!empty(get_site_option('dolibarr_public_url')) && !empty(get_site_option('dolibarr_private_key'))) {
$dolibarr = callDoliApi("GET", "/status", null, dolidelay('dolibarr'));
$versiondoli = explode("-", $dolibarr->success->dolibarr_version);
if ( is_object($dolibarr) && version_compare($versiondoli[0], $version) >= 0 ) {
$ret = $versiondoli[0];
}
}
return $ret;
}
add_action( 'admin_init', 'doliversion', 5, 1); 

function dolipage($object, $url, $page = 0, $limit = 8) {

if (empty($object) || isset($object->error)) {
$count = 0;
} else {
$count = count($object);
}

$pagination = "<nav aria-label='Page navigation example'><ul class='pagination pagination-sm'>";
if ($page > '1') {
$pagination .= '<li class="page-item">
      <a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page)), $url) ).'" aria-label="Previous">
        <span aria-hidden="true">'.__( 'Previous', 'doliconnect').'</span>
        <span class="sr-only">'.__( 'Previous', 'doliconnect').'</span>
     </a>
  </li>';
}
if ($page > 0) {
$pagination .= '<li class="page-item"><a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page)), $url) ).'">'.esc_attr($page).'</a></li>';
}    
$pagination .= '<li class="page-item active"><a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page+1)), $url) ).'">'.esc_attr($page+1).'</a></li>';
if ($count >= $limit) {
$pagination .= '<li class="page-item"><a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page+2)), $url) ).'">'.esc_attr($page+2).'</a></li>';
if ($page < 1) {
//$pagination .= '<li class="page-item"><a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page+3)), $url) ).'">'.esc_attr($page+3).'</a></li>';
} 
$pagination .= '<li class="page-item">
      <a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page+2)), $url) ).'" aria-label="Next">
        <span aria-hidden="true">'.__( 'Next', 'doliconnect').'</span>
        <span class="sr-only">'.__( 'Next', 'doliconnect').'</span>
      </a>
  </li>';
}
$pagination .= "</ul></nav>";
return $pagination;
}

function doliconnect_image($module, $id, $options = array(), $refresh = null) {

if (is_numeric($id)) {
$imgs = callDoliApi("GET", "/documents?modulepart=".$module."&id=".$id, null, dolidelay('document', $refresh), $options['entity']);   
$image = "<div class='row'>";
$subdir = '';
$dir = '/'.$id;
if ($module == 'category') {
$num = preg_replace('/([^0-9])/i', '', $id);
$subdir = substr($num, 1, 1).'/'.substr($num, 0, 1).'/'.$id.'/';
$dir = '/'.substr($num, 1, 1).'/'.substr($num, 0, 1).'/'.$id;
}
if ( !isset($imgs->error) && $imgs != null ) {
$imgs = array_slice((array) $imgs, 0, $options['limit']);
foreach ($imgs as $img) {
$up_dir = wp_upload_dir();
$image .= "<div class='col'>";
$file=$up_dir['basedir'].'/doliconnect/'.$module.$dir.'/'.$img->relativename;
if (!is_file($file)) {
$imgj =  callDoliApi("GET", "/documents/download?modulepart=".$module."&original_file=".$subdir.$img->level1name."/".$img->relativename, null, dolidelay('document', $refresh), $options['entity']);
//$image .= var_dump($imgj);
$imgj = (array) $imgj; 
if (is_array($imgj) && !isset($imgj['error']) && preg_match('/^image/', $imgj['content-type'])) {
//$data = "data:".$imgj['content-type'].";".$imgj['encoding'].",".$imgj['content'];

if (!is_dir($up_dir['basedir'].'/doliconnect/'.$module.$dir)) {
mkdir($up_dir['basedir'].'/doliconnect/'.$module.$dir, 0755, true);
}
//$files = glob($up_dir['basedir'].'/doliconnect/'.$module.'/'.$id."/*");
//foreach($files as $file){
//if(is_file($file))
//unlink($file); 
//}
$file=$up_dir['basedir'].'/doliconnect/'.$module.$dir.'/'.$img->relativename;
file_put_contents($file, base64_decode($imgj['content']));

if (!is_file($up_dir['basedir'].'/doliconnect/'.$module.$dir.'/'.explode('.', $img->relativename, 2)[0].'-'.$options['size'].'.'.explode('.', $img->relativename, 2)[1])) {
$imgy = wp_get_image_editor($file); 
$imgy->resize( 350, 350, true );
$avatar = $imgy->generate_filename($options['size'],$up_dir['basedir']."/doliconnect/".$module.$dir."/", NULL );
$imgy->save($avatar);
}
$image .= "<img src='".$up_dir['baseurl'].'/doliconnect/'.$module.$dir.'/'.explode('.', $img->relativename, 2)[0].'-'.$options['size'].'.'.explode('.', $img->relativename, 2)[1]."' class='img-fluid rounded-lg' loading='lazy' alt='".$img->relativename."'>";

} else {
$image .= "<i class='fa fa-cube fa-fw fa-2x'></i>";
}
} else {
$picture = '/doliconnect/'.$module.$dir.'/'.$img->relativename;
if (isset($options['size'])) {
$picture2 = '/doliconnect/'.$module.$dir.'/'.explode('.', $img->relativename, 2)[0].'-'.$options['size'].'.'.explode('.', $img->relativename, 2)[1];
$picture = $picture2;
}
if (isset($options['size']) && !is_file($up_dir['basedir'].$picture)) {
$imgy = wp_get_image_editor($file); 
$imgy->resize( 350, 350, true );
$avatar = $imgy->generate_filename($options['size'],$up_dir['basedir']."/doliconnect/".$module.$dir."/", NULL );
$imgy->save($avatar);
}
$image .= "<img src='".$up_dir['baseurl'].$picture."' class='img-fluid rounded-lg' loading='lazy' alt='".$img->relativename."'>";

}
$image .= "</div>";
}} elseif ($module == 'product' || $module == 'category') {
$image .= "<div class='col'><i class='fa fa-cube fa-fw fa-2x'></i></div>";
}
$image .= "</div>";
} else {
$up_dir = wp_upload_dir();
$file=$up_dir['basedir'].'/doliconnect/'.$module.'/'.$id;
if (!is_file($file)) {
$imgj =  callDoliApi("GET", "/documents/download?modulepart=".$module."&original_file=".$id, null, dolidelay('document', $refresh), $options['entity']);
//$image .= var_dump($imgj);
$imgj = (array) $imgj; 
if (is_array($imgj) && preg_match('/^image/', $imgj['content-type'])) {
//$data = "data:".$imgj['content-type'].";".$imgj['encoding'].",".$imgj['content'];

if (!is_dir($up_dir['basedir'].'/doliconnect/'.$module.'/'.$id)) {
mkdir($up_dir['basedir'].'/doliconnect/'.$module.'/'.explode('/'.$imgj['filename'], $id, 2)[0], 0755, true);
}
//$files = glob($up_dir['basedir'].'/doliconnect/'.$module.'/'.$id."/*");
//foreach($files as $file){
//if(is_file($file))
//unlink($file); 
//}
$file=$up_dir['basedir'].'/doliconnect/'.$module.'/'.$id;
file_put_contents($file, base64_decode($imgj['content']));
$image = "<img src='".$up_dir['baseurl'].'/doliconnect/'.$module.'/'.$id."' class='img-fluid rounded-lg' loading='lazy' alt='".$imgj['filename']."'>"; 
} else {
$image = "<i class='fa fa-cube fa-fw fa-2x'></i>";
}
} else {
$image = "<img src='".$up_dir['baseurl'].'/doliconnect/'.$module.'/'.$id."' class='img-fluid rounded-lg' loading='lazy' alt='".$up_dir['baseurl'].'/doliconnect/'.$module.'/'.$id."'>";
}
}
return $image;
}

function doliconnect_categories($type, $object, $url = null){
$cats = "";

if ( !empty(doliconst('MAIN_MODULE_CATEGORIE')) ) {
$categories =  callDoliApi("GET", "/categories/object/".$type."/".$object->id."?sortfield=s.rowid&sortorder=ASC", null, dolidelay($type));

if ( !isset($categories->error) && $categories != null ) {
$cats .= "<small><i class='fas fa-tags fa-fw'></i> ".__( 'Categories:', 'doliconnect' )." ";
$i = 0;
foreach ($categories as $category) {
if (!empty($i)) { $cats .= " "; }
if (!empty($url)) {
$cats .= "<a href='".esc_url( add_query_arg( 'category', $category->id, $url) )."'";
} else { 
$cats .= "<span ";
}
$cats .= "class='badge badge-pill badge-secondary'>";

$cats .= doliproduct($category, 'label');
if (!empty($url)) {
$cats .= "</a>";
} else {
$cats .= "</span>";
}
$i++;
}
$cats .= "</small>";
}
}
return $cats;
}

function socialconnect( $url ) {
$connect = null;

include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/autoload.php');
include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/config.php');

$hybridauth = new Hybridauth\Hybridauth($config);
$adapters = $hybridauth->getConnectedAdapters();

foreach ($hybridauth->getProviders() as $name) {

if (!isset($adapters[$name])) {
$connect .= "<a href='".doliconnecturl('doliaccount')."?provider=".$name."' onclick='loadingLoginModal()' role='button' class='btn btn-block btn-outline-dark' title='".__( 'Sign in with', 'doliconnect')." ".$name."'><b><i class='fab fa-".strtolower($name)." fa-lg float-left'></i> ".__( 'Sign in with', 'doliconnect')." ".$name."</b></a>";
}
}
if (!empty($hybridauth->getProviders())) {
$connect .= '<div><div style="display:inline-block;width:46%;float:left"><hr width="90%" /></div><div style="display:inline-block;width: 8%;text-align: center;vertical-align:90%"><small class="text-muted">'.__( 'or', 'doliconnect').'</small></div><div style="display:inline-block;width:46%;float:right" ><hr width="90%"/></div></div>';
}

return $connect;
}

function dolipasswordform($user, $url){
if (doliconnector($user, 'fk_user') > 0){  
$request = "/users/".doliconnector($user, 'fk_user');
$doliuser = callDoliApi("GET", $request, null, dolidelay('thirdparty'));
}

$password = "<div id='DoliRpwAlert' class='text-danger font-weight-bolder'></div><form id='dolirpw-form' method='post' class='was-validated' action='".admin_url('admin-ajax.php')."'>";
$password .= "<input type='hidden' name='action' value='dolirpw_request'>";
$password .= "<input type='hidden' name='dolirpw-nonce' value='".wp_create_nonce( 'dolirpw-nonce')."'>";
if (isset($_GET["key"]) && isset($_GET["login"])) {
$password .= "<input type='hidden' name='key' value='".esc_attr($_GET["key"])."'><input type='hidden' name='login' value='".esc_attr($_GET["login"])."'>";
}

$password .= "<script>";
$password .= 'jQuery(document).ready(function($) {
	
	jQuery("#dolirpw-form").on("submit", function(e) {
  jQuery("#DoliconnectLoadingModal").modal("show");
	e.preventDefault();
    
	var $form = $(this);
  var url = "'.$url.'";  
jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function (e) { 
		$.post($form.attr("action"), $form.serialize(), function(response) {
      if (response.success) {
      document.location = url;
      } else {
      if (document.getElementById("DoliRpwAlert")) {
      document.getElementById("DoliRpwAlert").innerHTML = response.data;      
      }
      }
jQuery("#DoliconnectLoadingModal").modal("hide");

		}, "json");  
  });
});
});';
$password .= "</script>";

$password .= "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if ( doliconnector($user, 'fk_user') > '0' ) {
$password .= "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Your password will be synchronized with your Dolibarr account', 'doliconnect')."</b></li>";
} elseif  ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Password cannot be modified in demo mode', 'doliconnect')."</b></li>";
} 
$password .= '<li class="list-group-item list-group-item-light">';
if (is_user_logged_in() && $user) {
$password .= '<div class="form-group"><div class="row"><div class="col-12"><label for="passwordHelpBlock1"><small>'.__( 'Confirm your current password', 'doliconnect').'</small></label>
<div class="input-group mb-2"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-key fa-fw"></i></div></div><input type="password" id="pwd0" name="pwd0" class="form-control" aria-describedby="passwordHelpBlock1" autocomplete="off" placeholder="'.__( 'Confirm your current password', 'doliconnect').'" ';
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= ' readonly';
} else {
$password .= ' required';
}
$password .= '></div></div></div></div>';
}
$password .= '<div class="form-group"><div class="row"><div class="col-12"><label for="passwordHelpBlock2"><small>'.__( 'New password', 'doliconnect').'</small></label>
<div class="input-group mb-2"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-key fa-fw"></i></div></div><input type="password" id="pwd1" name="pwd1" class="form-control" aria-describedby="passwordHelpBlock2" autocomplete="off" placeholder="'.__( 'Enter your new password', 'doliconnect').'" ';
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= ' readonly';
} else {
$password .= ' required';
}
$password .= '></div><small id="passwordHelpBlock3" class="form-text text-justify text-muted">
'.__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect').'
</small><div class="invalid-feedback">'.__( 'This field is required.', 'doliconnect').'</div></div></div><div class="row"><div class="col-12"><label for="passwordHelpBlock3"><small>'.__( 'New password', 'doliconnect').'</small></label>';
$password .= '<div class="input-group mb-2"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-key fa-fw"></i></div></div><input type="password" id="pwd2" name="pwd2"  class="form-control" aria-describedby="passwordHelpBlock3" autocomplete="off" placeholder="'.__( 'Confirm your new password', 'doliconnect').'" ';
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= ' readonly';
} else {
$password .= ' required';
}
$password .= '></div></div></div></li>';
$password .= "</ul><div class='card-body'><button class='btn btn-danger btn-block' type='submit' ";
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= ' disabled';
}
$password .= ">".__( 'Update', 'doliconnect')."</button></div><div class='card-footer text-muted'>";
$password .= "<small><div class='float-left'>";
if ( isset($request) ) $password .= dolirefresh($request, $url, dolidelay('thirdparty'));
$password .= "</div><div class='float-right'>";
$password .= dolihelp('ISSUE');
$password .= "</div></small>";
$password .= '</div></div>';

return $password;
}

function doliuserform($object, $delay, $mode) {
global $current_user;

if ( is_object($object) && $object->id > 0 ) {
$idobject=$mode."[".$object->id."]";
} else { $idobject=$mode; }

print "<ul class='list-group list-group-flush'>";

if ( ! isset($object) && in_array($mode, array('thirdparty')) && empty(get_option('doliconnect_disablepro')) ) {
print "<li class='list-group-item'><div class='form-row'><div class='col-12'>";
if ( isset($_GET["morphy"]) && $_GET["morphy"] == 'mor' && get_option('doliconnect_disablepro') != 'mor' ) {
print "<a href='".wp_registration_url(get_permalink())."&morphy=phy' role='button' title='".__( 'Create a personnal account', 'doliconnect')."'><small>(".__( 'Create a personnal account', 'doliconnect')."?)</small></a>";                                                                                                                                                                                                                                                                                                                                     
print "<input type='hidden' id='morphy' name='".$idobject."[morphy]' value='mor'>";
}
elseif (get_option('doliconnect_disablepro') != 'phy') {
print "<a href='".wp_registration_url(get_permalink())."&morphy=mor' role='button' title='".__( 'Create a enterprise / supplier account', 'doliconnect')."'><small>(".__( 'Create a enterprise / supplier account', 'doliconnect')."?)</small></a>";
print "<input type='hidden' id='morphy' name='".$idobject."[morphy]' value='phy'>";
}
print "</div></div></li><li class='list-group-item'>";
} elseif ( isset($object) && in_array($mode, array('thirdparty')) && empty(get_option('doliconnect_disablepro')) ) { //|| $mode == 'member'
print "<li class='list-group-item'><div class='form-row'><div class='col-12'><label for='inputMorphy'><small><i class='fas fa-user-tag fa-fw'></i> ".__( 'Type of account', 'doliconnect')."</small></label><br>";
print "<div class='custom-control custom-radio custom-control-inline'><input type='radio' id='morphy1' name='".$idobject."[morphy]' value='phy' class='custom-control-input'";
if ( $current_user->billing_type != 'mor' || empty($current_user->billing_type) ) { print " checked"; }
print " required><label class='custom-control-label' for='morphy1'>".__( 'Personnal account', 'doliconnect')."</label>
</div>
<div class='custom-control custom-radio custom-control-inline'><input type='radio' id='morphy2' name='".$idobject."[morphy]' value='mor' class='custom-control-input'";
if ( $current_user->billing_type == 'mor' ) { print " checked"; }
print " required><label class='custom-control-label' for='morphy2'>".__( 'Entreprise account', 'doliconnect')."</label>
</div>";
print "</div></div></li><li class='list-group-item'>";
} elseif ( in_array($mode, array('thirdparty')) ) { //|| $mode == 'member'
print "<li class='list-group-item'><input type='hidden' id='morphy' name='".$idobject."[morphy]' value='phy'>";
} elseif ( !is_user_logged_in() && in_array($mode, array('linkthirdparty')) ) {

print '<li class="list-group-item"><div class="form-group">
  <label for="FormCustomer"><small><i class="fas fa-user-tie"></i> '.__( 'Customer', 'doliconnect').'</small></label><div class="input-group" id="FormCustomer">
  <input type="text" aria-label="Last name" name="code_client" placeholder="'.__( 'Customer code', 'doliconnect').'" class="form-control" required>
</div><div>';
print '<div class="form-group">
  <label for="FormObject"><small><i class="fas fa-file-invoice"></i> '.__( 'Order or Invoice', 'doliconnect').'</small></label><div class="input-group" id="FormObject">
  <input type="text" aria-label="Reference" name="reference" placeholder="'.__( 'Reference', 'doliconnect').'" class="form-control" required>
  <input type="number" aria-label="Amount" name="amount" placeholder="'.__( 'Total incl. tax', 'doliconnect').'" class="form-control" required>
</div><div><li class="list-group-item">';

} else {
print "<li class='list-group-item'>";
}

if ( in_array($mode, array('member')) ) {
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-user-tag fa-fw'></i> ".__( 'Type', 'doliconnect')."</small></label><select class='custom-select' id='typeid'  name='".$idobject."[typeid]' required>";
$typeadhesion = callDoliApi("GET", "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'".$object->morphy."')", null, $delay);
//print $typeadhesion;
print "<option value='' disabled ";
if ( empty($object->typeid) ) {
print "selected ";}
print ">".__( '- Select -', 'doliconnect')."</option>";
if ( !isset($typeadhesion->error) ) {
foreach ($typeadhesion as $postadh) {
print "<option value ='".$postadh->id."' ";
if ( isset($object->typeid) && $object->typeid == $postadh->id && $object->typeid != null ) {
print "selected ";
} elseif ( $postadh->family == '1' || $postadh->automatic_renew != '1' || $postadh->automatic != '1' ) { print "disabled "; }
print ">".$postadh->label;
if (! empty ($postadh->duration_value)) print " - ".doliduration($postadh);
print " ";
//if ( ! empty($postadh->note) ) { print ", ".$postadh->note; }
$tx=1;
if ( ( ($postadh->welcome > '0') && ($object->datefin == null )) || (($postadh->welcome > '0') && (current_time( 'timestamp',1) > $object->next_subscription_valid) && (current_time( 'timestamp',1) > $object->datefin) && $object->next_subscription_valid != $object->datefin ) ) { 
print " (";
print doliprice(($tx*$postadh->price)+$postadh->welcome)." ";
print __( 'then', 'doliconnect-pro' )." ".doliprice($postadh->price)." ".__( 'yearly', 'doliconnect-pro' ).")"; 
} else {
print " (".doliprice($postadh->price);
print " ".__( 'yearly', 'doliconnect-pro' ).")";
} 

print "</option>";
}}
print "</select></div></div></li><li class='list-group-item'>";
}

if ( in_array($mode, array('thirdparty', 'donation')) && ($current_user->billing_type == 'mor' || ( isset($_GET["morphy"]) && $_GET["morphy"] == 'mor') || get_option('doliconnect_disablepro') == 'mor' ) ) {
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-building fa-fw'></i> ".__( 'Name of company', 'doliconnect')."</small></label><input type='text' class='form-control' id='inputcompany' placeholder='".__( 'Name of company', 'doliconnect')."' name='".$idobject."[name]' value='".$object->name."' required></div></div>";  //$current_user->billing_company
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-building fa-fw'></i> ".__( 'Professional ID', 'doliconnect')."</small></label><input type='text' class='form-control' id='inputcompany' placeholder='".__( 'Professional ID', 'doliconnect')."' name='".$idobject."[idprof1]' value='".$object->idprof1."' required></div></div>";  //$current_user->billing_company
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-landmark fa-fw'></i> ".__( 'VAT number', 'doliconnect')."</small></label><input type='text' class='form-control' id='inputcompany' placeholder='".__( 'VAT number', 'doliconnect')."' name='".$idobject."[tva_intra]' value='".$object->tva_intra."'></div></div>";
print "</li><li class='list-group-item'>";
}

print "<div class='form-row'><div class='col-12 col-md-3'><label for='inputCivility'><small><i class='fas fa-user fa-fw'></i> ".__( 'Civility', 'doliconnect')."</small></label>";
if ( doliversion('10.0.0') ) {
$civility = callDoliApi("GET", "/setup/dictionary/civilities?sortfield=code&sortorder=ASC&limit=100", null, $delay);
} else {
$civility = callDoliApi("GET", "/setup/dictionary/civility?sortfield=code&sortorder=ASC&limit=100", null, $delay);
}
print "<select class='custom-select' id='".$idobject."[civility_id]'  name='".$idobject."[civility_id]' required>";
print "<option value='' disabled ";
if ( empty($object->civility_id) ) {
print "selected ";}
print ">".__( '- Select -', 'doliconnect')."</option>";
if ( !isset($civility->error ) && $civility != null ) { 
foreach ( $civility as $postv ) {

print "<option value='".$postv->code."' ";
if ( (isset($object->civility_id) ? $object->civility_id : $current_user->civility_id) == $postv->code && (isset($object->civility_id) ? $object->civility_id : $current_user->civility_id) != null) {
print "selected ";}
print ">".$postv->label."</option>";

}} else {
print "<option value='MME' ";
if ( $current_user->civility_id == 'MME' && $object->civility_id != null) {
print "selected ";}
print ">".__( 'Miss', 'doliconnect')."</option>";
print  "<option value='MR' ";
if ( $current_user->civility_id == 'MR' && $object->civility_id != null) {
print "selected ";}
print ">".__( 'Mister', 'doliconnect')."</option>";
}
print "</select>";
print "</div>
    <div class='col-12 col-md-4'>
      <label for='".$idobject."[firstname]'><small><i class='fas fa-user fa-fw'></i> ".__( 'Firstname', 'doliconnect')."</small></label>
      <input type='text' name='".$idobject."[firstname]' class='form-control' placeholder='".__( 'Firstname', 'doliconnect')."' value='".(isset($object->firstname) ? $object->firstname : stripslashes(htmlspecialchars($current_user->user_firstname, ENT_QUOTES)))."' required>
    </div>
    <div class='col-12 col-md-5'>
      <label for='".$idobject."[lastname]'><small><i class='fas fa-user fa-fw'></i> ".__( 'Lastname', 'doliconnect')."</small></label>
      <input type='text' name='".$idobject."[lastname]' class='form-control' placeholder='".__( 'Lastname', 'doliconnect')."' value='".(isset($object->lastname) ? $object->lastname : stripslashes(htmlspecialchars($current_user->user_lastname, ENT_QUOTES)))."' required>
    </div></div>";

if ( !in_array($mode, array('donation')) ) {
if ( !empty($object->birth) ) { $birth = wp_date('Y-m-d', $object->birth); }
print "<div class='form-row'><div class='col'><label for='".$idobject."[birth]'><small><i class='fas fa-birthday-cake fa-fw'></i> ".__( 'Birthday', 'doliconnect')."</small></label><input type='date' name='".$idobject."[birth]' class='form-control' value='".(isset($birth) ? $birth : $current_user->billing_birth)."' id='".$idobject."[birth]' placeholder='yyyy-mm-dd' autocomplete='off'";
if ( $mode != 'contact' ) { print " required"; } 
print "></div>";
print "<div class='col-12 col-md-7'>";
if ( $mode != 'contact' ) {
print "<label for='inputnickname'><small><i class='fas fa-user-secret fa-fw'></i> ".__( 'Display name', 'doliconnect')."</small></label><input type='text' class='form-control' id='inputnickname' placeholder='".__( 'Nickname', 'doliconnect')."' name='user_nicename' value='".stripslashes(htmlspecialchars($current_user->nickname, ENT_QUOTES))."' autocomplete='off' required >";
} else {
print "<label for='".$idobject."[poste]'><small><i class='fas fa-user-secret fa-fw'></i> ".__( 'Title / Job', 'doliconnect')."</small></label><input type='text' class='form-control' id='".$idobject."[poste]' placeholder='".__( 'Title / Job', 'doliconnect')."' name='".$idobject."[poste]' value='".stripslashes(htmlspecialchars(isset($object->poste) ? $object->poste : null, ENT_QUOTES))."' autocomplete='off'>";
}
print "</div></div>";
}

print "<div class='form-row'><div class='col'><label for='".$idobject."[email]'><small><i class='fas fa-at fa-fw'></i> ".__( 'Email', 'doliconnect')."</small></label><input type='email' class='form-control' id='".$idobject."[email]' placeholder='email@example.com' name='".$idobject."[email]' value='".(isset($object->email) ? $object->email : $current_user->user_email)."' autocomplete='off' ";

if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $current_user->ID && is_user_logged_in() && in_array($mode, array('thirdparty')) ) {
print " readonly";
} else {
print " required";
}
print "></div>";
if ( ( !is_user_logged_in() && ((isset($_GET["morphy"])&& $_GET["morphy"] == "mor" && get_option('doliconnect_disablepro') != 'phy') || get_option('doliconnect_disablepro') == 'mor' || (function_exists('dolikiosk') && ! empty(dolikiosk())) ) && in_array($mode, array('thirdparty'))) || (is_user_logged_in() && in_array($mode, array('thirdparty','contact','member','donation'))) ) {
print "<div class='col-12 col-md-5'><label for='".$idobject."[phone]'><small><i class='fas fa-phone fa-fw'></i> ".__( 'Phone', 'doliconnect')."</small></label><input type='tel' class='form-control' id='".$idobject."[phone]' placeholder='".__( 'Phone', 'doliconnect')."' name='".$idobject."[phone]' value='".(isset($object->phone) ? $object->phone : (isset($object->phone_pro) ? $object->phone_pro: null))."' autocomplete='off'></div>";
}
print "</div></li>";

if ( ( !is_user_logged_in() && ((isset($_GET["morphy"])&& $_GET["morphy"] == "mor" && get_option('doliconnect_disablepro') != 'phy') || get_option('doliconnect_disablepro') == 'mor' || (function_exists('dolikiosk') && ! empty(dolikiosk())) ) && in_array($mode, array('thirdparty'))) || (is_user_logged_in() && in_array($mode, array('thirdparty','contact','member','donation'))) ) {       
print "<li class='list-group-item'>";
 
print "<div class='form-row'><div class='col-12'><label for='".$idobject."[address]'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Address', 'doliconnect')."</small></label>
<textarea id='".$idobject."[address]' name='".$idobject."[address]' class='form-control' rows='3' placeholder='".__( 'Address', 'doliconnect')."' required>".(isset($object->address) ? $object->address : null)."</textarea></div></div>";

print "<div class='form-row'>
    <div class='col-md-6'><label for='".$idobject."[town]'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Town', 'doliconnect')."</small></label>
      <input type='text' class='form-control' placeholder='".__( 'Town', 'doliconnect')."' name='".$idobject."[town]' value='".(isset($object->town) ? $object->town : null)."' autocomplete='off' required>
    </div>
    <div class='col'><label for='".$idobject."[zip]'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Zipcode', 'doliconnect')."</small></label>
      <input type='text' class='form-control' placeholder='".__( 'Zipcode', 'doliconnect')."' name='".$idobject."[zip]' value='".(isset($object->zip) ? $object->zip : null)."' autocomplete='off' required>
    </div>
    <div class='col'><label for='".$idobject."[country_id]'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Country', 'doliconnect')."</small></label>";

if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}

$pays = callDoliApi("GET", "/setup/dictionary/countries?sortfield=favorite%2Clabel&sortorder=DESC%2CASC&limit=400&lang=".$lang, null, $delay);

if ( isset($pays) ) { 
print "<select class='custom-select' id='".$idobject."[country_id]'  name='".$idobject."[country_id]' required>";
print "<option value='' disabled ";
if ( !isset($object->country_id) && ! $object->country_id > 0 || $pays == 0) {
print "selected ";}
print ">".__( '- Select -', 'doliconnect')."</option>";
foreach ( $pays as $postv ) { 
print "<option value='".$postv->id."' ";
if ( isset($object->country_id) && $object->country_id == $postv->id && $object->country_id != null && $postv->id != '0' ) {
print "selected ";
} elseif ( $postv->id == '0' ) { print "disabled "; }
print ">".$postv->label."</option>";
}
print "</select>";
} else {
print "<input type='text' class='form-control' id='inputcountry' placeholder='".__( 'Country', 'doliconnect')."' name='".$idobject."[country_id]' value='".$object->country_id."' autocomplete='off' required>";
}
print "</div></div>";

print "</li>";

if( has_filter('mydoliconnectuserform') && !in_array($mode, array('donation')) ) {
print "<li class='list-group-item'>";
print apply_filters('mydoliconnectuserform', $object);
print "</li>";
}

if ( in_array($mode, array('contact')) && doliversion('11.0.0') ) {

$contact_types = callDoliApi("GET", "/setup/dictionary/contact_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.source%3A%3D%3A'external')%20AND%20(t.element%3A%3D%3A'commande')", null, $delay);//%20OR%20(t.element%3A%3D%3A'propal')

print "<li class='list-group-item'>";
if ( !isset($contact_types->error ) && $contact_types != null ) {
$typecontact = array();

if ( isset($object->roles) && $object->roles != null ) {
foreach ( $object->roles as $role ) {
$typecontact[] .= $role->id; 
}}
foreach ( $contact_types as $contacttype ) {                                                           //name='".$idobject."[roles][id]'
print "<div class='custom-control custom-checkbox'><input type='checkbox' class='custom-control-input' value='".$contacttype->rowid."' id='".$idobject."[roles][".$contacttype->rowid."]' ";
if ( isset($object->roles) && $object->roles != null && in_array($contacttype->rowid, $typecontact)) { print " checked"; }
print " disabled><label class='custom-control-label' for='".$idobject."[roles][".$contacttype->rowid."]'>".$contacttype->label."</label></div>";
}
 
}

print "</li>";
}

if ( !in_array($mode, array('contact', 'donation', 'linkthirdparty')) ) {
print "<li class='list-group-item'>";

if ( !in_array($mode, array('contact', 'member', 'linkthirdparty')) ) {
print "<div class='form-row'><div class='col'><label for='description'><small><i class='fas fa-bullhorn fa-fw'></i> ".__( 'About Yourself', 'doliconnect')."</small></label>
<textarea type='text' class='form-control' name='description' id='description' rows='3' placeholder='".__( 'About Yourself', 'doliconnect')."'>".$current_user->description."</textarea></div></div>";

print "<div class='form-row'><div class='col'><label for='description'><small><i class='fas fa-link fa-fw'></i> ".__( 'Website', 'doliconnect')."</small></label>
<input type='url' class='form-control' name='".$idobject."[url]' id='website' placeholder='".__( 'Website', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->url) ? $object->url : null), ENT_QUOTES))."'></div></div>";
}

print "</li>";
}


if ( doliversion('11.0.0') ) { 
$socialnetworks = callDoliApi("GET", "/setup/dictionary/socialnetworks?sortfield=rowid&sortorder=ASC&limit=100&active=1", null, $delay);
if ( !isset($socialnetworks->error) && $socialnetworks != null ) { 
print "<li class='list-group-item'><div class='form-row'>";
foreach ( $socialnetworks as $social ) { 
$code = $social->code;
print "<div class='col-12 col-md-4'><label for='".$idobject."[socialnetworks][".$social->code."]'><small><i class='fab fa-".$social->code." fa-fw'></i> ".$social->label."</small></label>
<input type='text' name='".$idobject."[socialnetworks][".$social->code."]' class='form-control' id='".$idobject."[socialnetworks][".$social->code."]' placeholder='".__( 'Username', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->socialnetworks->$code) ? $object->socialnetworks->$code : null), ENT_QUOTES))."'></div>";
}
print "</div></li>";
}

} else { 
print "<li class='list-group-item'><div class='form-row'>";
if ( !empty(doliconst("SOCIALNETWORKS_FACEBOOK", $delay)) ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-facebook fa-fw'></i> Facebook</small></label>
<input type='text' name='".$idobject."[facebook]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->facebook) ? $object->facebook : null), ENT_QUOTES))."'></div>";
}
if ( !empty(doliconst("SOCIALNETWORKS_TWITTER", $delay)) ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-twitter fa-fw'></i> Twitter</small></label>
<input type='text' name='".$idobject."[twitter]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->twitter) ? $object->twitter : null), ENT_QUOTES))."'></div>";
}
if ( !empty(doliconst("SOCIALNETWORKS_SKYPE", $delay)) ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-skype fa-fw'></i> Skype</small></label>
<input type='text' name='".$idobject."[skype]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->skype) ? $object->skype : null), ENT_QUOTES))."'></div>";
}
if ( !empty(doliconst("SOCIALNETWORKS_LINKEDIN", $delay)) ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-linkedin-in fa-fw'></i> Linkedin</small></label>
<input type='text' name='".$idobject."[linkedin]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->linkedin) ? $object->linkedin : null), ENT_QUOTES))."'></div>";
}
print "</div></li>";
}

}

if ( function_exists('dolikiosk') && ! isset($object) && (! empty(dolikiosk()) && $mode == 'thirdparty') ) {
print "<li class='list-group-item'><div class='form-row'><div class='col'><label for='pwd1'><small><i class='fas fa-key fa-fw'></i> ".__( 'Password', 'doliconnect')."</small></label>
<input class='form-control' id='pwd1' type='password' name='pwd1' value ='' placeholder='".__( 'Choose your password', 'doliconnect')."' autocomplete='off' required>
<small id='pwd1' class='form-text text-justify text-muted'>".__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect')."</small></div></div>
<div class='form-row'><div class='col'><label for='pwd2'><small><i class='fas fa-key fa-fw'></i> ".__( 'Confirm your password', 'doliconnect')."</small></label>
<input class='form-control' id='pwd2' type='password' name='pwd2' value ='' placeholder='".__( 'Confirm your password', 'doliconnect')."' autocomplete='off' required></div>";
print "</div></li>";
}

if ( !is_user_logged_in() && in_array($mode, array('thirdparty','linkthirdparty')) ) {

if( has_action('register_form') ) {
if (!empty(do_action( 'register_form'))){
print "<li class='list-group-item'>";
print do_action( 'register_form');
print "</li>";
}
}

//print "<li class='list-group-item'>";
//print "<div class='form-row'><div class='custom-control custom-checkbox my-1 mr-sm-2'>
//<input type='checkbox' class='custom-control-input' value='1' id='optin1' name='optin1'>
//<label class='custom-control-label' for='optin1'> ".__( 'I would like to receive the newsletter', 'doliconnect')."</label></div></div>";
//print "<div class='form-row'><div class='custom-control custom-checkbox my-1 mr-sm-2'>
//<input type='checkbox' class='custom-control-input' value='forever' id='validation' name='validation' required>
//<label class='custom-control-label' for='validation'>".__( 'I read and accept the <a href="#" data-toggle="modal" data-target="#cgvumention">Terms & Conditions</a>.', 'doliconnect')."</label></div></div>";

//if ( get_option( 'wp_page_for_privacy_policy' ) ) {
//print "<div class='modal fade' id='cgvumention' tabindex='-1' role='dialog' aria-labelledby='cgvumention' aria-hidden='true'><div class='modal-dialog modal-lg modal-dialog-centered' role='document'><div class='modal-content'><div class='modal-header'><h5 class='modal-title' id='cgvumentionLabel'>".__( 'Terms & Conditions', 'doliconnect')."</h5><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>
//<div class='modal-body'>";
//$post = get_post(get_option( 'wp_page_for_privacy_policy' ));
//print $post->post_content;
//print apply_filters('the_content', get_post_field('post_content', get_option( 'wp_page_for_privacy_policy' )));
//print get_the_content( 'Read more', '', get_option( 'wp_page_for_privacy_policy' )); 
//print "</div></div></div>";}
//print "</li>";
}

print "</ul>";
}
//add_action( 'wp_loaded', 'doliconnectuserform', 10, 2);

function doliloading($id=loading) {
$loading = '<div id="doliloading-'.$id.'" style="display:none"><br><br><br><br><center><div class="align-middle">';
$loading .= '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>'; 
$loading .= '<h4>'.__( 'Loading', 'doliconnect').'</h4></div></center><br><br><br><br></div>';
return $loading;
}

function doliconnect_loading() {

doliconnect_enqueues();

print '<div id="DoliconnectLoadingModal" class="modal fade bd-example-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-show="true" data-backdrop="static" data-keyboard="false">
<div class="modal-dialog modal-dialog-centered modal">
<div class="text-center text-light w-100">
<div class="spinner-border" role="status"><span class="sr-only">loading...</span></div>
<h4>'.__( 'Processing', 'doliconnect').'</h4>
</div></div></div>';

}
add_action( 'wp_footer', 'doliconnect_loading');

function dolibug($msg = null) {
//header('Refresh: 180; URL='.esc_url(get_permalink()).'');
$bug = '<div id="dolibug" ><br><br><br><br><center><div class="align-middle"><i class="fas fa-bug fa-7x fa-fw"></i><h4>';
if ( ! empty($msg) ) {
$bug .= $msg;
} else { $bug .= __( 'Oops, our servers are unreachable.<br>Thank you for coming back in a few minutes.', 'doliconnect'); }
$bug .= '</h4>';
if ( defined("DOLIBUG") && ! empty(constant("DOLIBUG")) ) {
$bug .= '<h6>'.__( 'Error code', 'doliconnect').' #'.constant("DOLIBUG").'</h6>';
}
$bug .='</div></center><br><br><br><br></div>';
return $bug;
}

function Doliconnect_MailAlert( $user_login, $user) {

if ( $user->loginmailalert == 'on' ) { //&& $user->ID != ''.constant("DOLICONNECT_DEMO").''
$sitename = get_option('blogname');
$siteurl = get_option('siteurl');
$subject = "[$sitename] ".__( 'Connection notification', 'doliconnect');
$body = __( 'It appears that you have just logged on to our site the following IP address:', 'doliconnect')."<br /><br />".$_SERVER['REMOTE_ADDR']."<br /><br />".__( 'If you have not made this action, please change your password immediately.', 'doliconnect')."<br /><br />".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br />$siteurl";				
$headers = array('Content-Type: text/html; charset=UTF-8');
$mail =  wp_mail($user->user_email, $subject, $body, $headers);
}

}
add_action('wp_login', 'Doliconnect_MailAlert', 10, 2);

function dolidocdownload($type, $ref=null, $fichier=null, $name=null, $refresh = false, $style = 'btn-outline-dark btn-sm btn-block') {
global $wpdb;
 
if ( $name == null ) { $name=$fichier; } 

if ( doliversion('11.0.0') ) {
$doc = callDoliApi("GET", "/documents/download?modulepart=".$type."&original_file=".$ref."/".$fichier, null, 0);
} else {
$doc = callDoliApi("GET", "/documents/download?module_part=".$type."&original_file=".$ref."/".$fichier, null, 0);
}
//print var_dump($doc);

if ( isset($ref) && isset($fichier) && isset($doc->content) ) { 

$data = "data:application/pdf;".$doc->encoding.",".$doc->content;
$filename = explode(".", $doc->filename)[0];

if (!empty(get_option('doliconnectbeta'))) {
$document = '<button type="button" class="btn btn btn-outline-dark btn-sm btn-block" data-toggle="modal" data-target=".modal-'.$filename.'">'.$name.' <i class="fas fa-file-download"></i></button>';
$document .= '<div class="modal fade modal-'.$filename.'" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg" role="document"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title" id="exampleModalCenterTitle"><a href="'.$data.'" download="'.$doc->filename.'">'.__( 'Download', 'doliconnect').' '.$doc->filename.'</a></h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body">';
$document .= '<iframe class="pdfjs-viewer" style="width:100%;height:70vh" src="'.plugins_url("doliconnect/includes/pdfjs/web/viewer.html").'?file=" id="pdfjsframe-'.$filename.'"></iframe>
<script>
document.getElementById("pdfjsframe-'.$filename.'").onload = function() {
document.getElementById("pdfjsframe-'.$filename.'").contentWindow.PDFViewerApplication.open("'.$data.'");
};
</script>';
$document .= '</div></div></div></div>';
} else {
$document = '<a href="'.$data.'" role="button" class="btn '.$style.'" download="'.$doc->filename.'">'.$name.' <i class="fas fa-file-download"></i></a>';
}
} else {
$document = '<button class="btn '.$style.'" disabled>'.$name.' <i class="fas fa-file-download"></i></button>';
}

return $document;
}

function dolihelp($type) {

if ( is_user_logged_in() && !empty(doliconst('MAIN_MODULE_TICKET')) ) {
$arr_params = array( 'module' => 'tickets', 'type' => $type, 'create' => true); 
$link=esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount'))); 
} elseif ( !empty(get_option('dolicontact')) ) {
$arr_params = array( 'create' => true); //'type' => $postorder->id,  
$link=esc_url( add_query_arg( $arr_params, doliconnecturl('dolicontact')));
} else {
$link='#';
}

$help = "<a href='".$link."' role='button' title='".__( 'Help?', 'doliconnect')."'><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-question-circle'></i></div><div class='d-none d-md-block'><i class='fas fa-question-circle'></i> ".__( 'Need help?', 'doliconnect')."</div></a>";

return $help;
}

function dolidelay($delay = null, $refresh = false, $protect = false) {

if (! is_numeric($delay)) {

if (false ===  get_site_option('doliconnect_delay_'.$delay) ) {

if ($delay == 'constante' || $delay == 'constantes') { $delay = MONTH_IN_SECONDS; }
elseif ($delay == 'dolibarr') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'doliconnector') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'paymentmethods') { $delay = WEEK_IN_SECONDS; }
elseif ($delay == 'thirdparty' || $delay == 'customer' || $delay == 'supplier') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'contact') { $delay = WEEK_IN_SECONDS; }
elseif ($delay == 'proposal') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'order') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'contract') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'project') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'member') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'donation') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'ticket') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'product') { $delay = 8 * HOUR_IN_SECONDS; }
elseif ($delay == 'cart') { $delay = 20 * MINUTE_IN_SECONDS; }
elseif ($delay == 'document') { $delay = MONTH_IN_SECONDS; }
} else {
$delay = HOUR_IN_SECONDS;
//$delay = get_site_option('doliconnect_delay_'.$delay);
}
 
}

$array = get_option('doliconnect_ipkiosk');
if ( is_array($array) && in_array($_SERVER['REMOTE_ADDR'], $array) ) {
$delay=0;
}
if ( $refresh && is_user_logged_in() ) {
$delay=$delay*-1;
}

return $delay;
}

function dolirefresh( $origin, $url, $delay, $element = null) {

$refresh = "<script>";
$refresh .= 'function refreshloader(){
jQuery("#DoliconnectLoadingModal").modal("show");
jQuery(window).scrollTop(0); 
this.form.submit();
}';
$refresh .= "</script>";

if ( isset($element->date_modification) && !empty($element->date_modification) ) {
$refresh .= "<i class='fas fa-database'></i> ".wp_date( get_option( 'date_format' ).' - '.get_option('time_format'), $element->date_modification, false);
} elseif ( get_option("_transient_timeout_".$origin) > 0 ) {
$refresh .= "<i class='fas fa-database'></i> ".wp_date( get_option( 'date_format' ).' - '.get_option('time_format'), get_option("_transient_timeout_".$origin)-$delay, false);
} elseif (is_user_logged_in() ) {
$refresh .= __( 'Refresh', 'doliconnect');
}
 
if (is_user_logged_in() ) {
$refresh .= " <a onClick='refreshloader()' href='".esc_url( add_query_arg( 'refresh', true, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) )."' title='".__( 'Refresh datas', 'doliconnect')."'><i class='fas fa-sync-alt'></i></a>";
}

return $refresh;
}

function dolikiosk() {
$array = get_option('doliconnect_ipkiosk');
if ( is_array($array) && in_array($_SERVER['REMOTE_ADDR'], $array) ) {
return true;
} else {
return false;
}
}

function dolialert($type, $msg) { //__( 'Oops!', 'doliconnect')
$alert = '<div class="alert alert-'.$type.' alert-dismissible fade show" role="alert">';
if ($type == 'success') {
$alert .= '<strong>'.__( 'Congratulations!', 'doliconnect').'</strong>';
} elseif ($type == 'warning') {
$alert .= '<strong>'.__( 'Be carefull', 'doliconnect').'</strong>';
} else {
$alert .= '<strong>'.__( 'Oops', 'doliconnect').'</strong>';
}
$alert .= ' '.$msg;
$alert .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
return $alert;
}

function doliloaderscript($idform, $scrolltop = true) {
$loader = "<script>";
$loader .= 'window.setTimeout(function () {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function () {
        $(this).remove();
    });
}, 5000);';

$loader .= 'var form = document.getElementById("'.$idform.'");';
$loader .= 'form.addEventListener("submit", function(event) {
jQuery("#DoliconnectLoadingModal").modal("show");';
if (!empty($scrolltop)) $loader .= 'jQuery(window).scrollTop(0);'; 
$loader .= 'console.log("submit");
form.submit();
});';
$loader .= "</script>";
return $loader;
}

function dolimodalloaderscript($idform) {
print "<script>";
?>
var form = document.getElementById('<?php print $idform; ?>');
form.addEventListener('submit', function(event) { 
jQuery(window).scrollTop(0);
jQuery('#Close<?php print $idform; ?>').hide(); 
jQuery('#Footer<?php print $idform; ?>').hide();
jQuery('#<?php print $idform; ?>').hide(); 
jQuery('#doliloading-<?php print $idform; ?>').show(); 
console.log("submit");
form.submit();
});
<?php
print "</script>";
}

function doliaddress($object) {
if ( !empty($object->name) ) {
$address = "<b><i class='fas fa-building fa-fw'></i> ".$object->name;
} else {
$address = "<b><i class='fas fa-building fa-fw'></i> ".($object->civility ? $object->civility : $object->civility_code)." ".$object->firstname." ".$object->lastname;
}
if ( !empty($object->default) ) { $address .= " <i class='fas fa-star fa-1x fa-fw' style='color:Gold'></i>"; }
if ( !empty($object->poste) ) { $address .= ", ".$object->poste; }
if ( !empty($object->type) ) { $address .= "<br>".__( 'Type', 'doliconnect').": ".$object->type; }
$address .= "</b><br>";
$address .= "<small class='text-muted'>".$object->address.", ".$object->zip." ".$object->town." - ".$object->country."<br>".$object->email." - ".(isset($object->phone) ? $object->phone : $object->phone_pro)."</small>";
return $address;
}

function dolicontact($id, $refresh = false) {
$object = callDoliApi("GET", "/contacts/".$id, null, dolidelay('contact', esc_attr(isset($refresh) ? $refresh : null)));  
$address = "<b><i class='fas fa-address-book fa-fw'></i> ".($object->civility ? $object->civility : $object->civility_code)." ".$object->firstname." ".$object->lastname;
if ( !empty($object->default) ) { $address .= " <i class='fas fa-star fa-1x fa-fw' style='color:Gold'></i>"; }
if ( !empty($object->poste) ) { $address .= ", ".$object->poste; }
$address .= "</b><br>";
$address .= "<small class='text-muted'>".$object->address.", ".$object->zip." ".$object->town." - ".$object->country."<br>".$object->email." - ".$object->phone_pro."</small>";
return $address;
}

function dolitotal($object) { 
$total = "<li class='list-group-item bg-light'><b>".__( 'Total excl. tax', 'doliconnect').": ".doliprice($object, 'ht', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b></li>";
$total .= "<li class='list-group-item bg-light'><b>".__( 'Total VAT', 'doliconnect').": ".doliprice($object, 'tva', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b></li>";
$total .= "<li class='list-group-item list-group-item-primary'><b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b></li>";
//if ( ! empty($object->cond_reglement_id) ) { $total .= "<b>".__( 'Terms of the settlement', 'doliconnect').":</b> ".$object->cond_reglement; }
//$total .= "</li>";
return $total;
}

function doliline($object, $refresh = false) {
global $current_user;

$doliline=null;

if ( isset($object) && is_object($object) && isset($object->lines) && $object->lines != null && (doliconnector($current_user, 'fk_soc') == $object->socid) ) {  
foreach ( $object->lines as $line ) { 

if ( $line->fk_product > 0 ) {
$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}
$product = callDoliApi("GET", "/products/".$line->fk_product."?includestockdata=".$includestock."&includesubproducts=true", null, dolidelay('cart', $refresh));
}

$minstock = min(array($product->stock_theorique,$product->stock_reel));
$maxstock = max(array($product->stock_theorique,$product->stock_reel));

if (( $maxstock <= 0 || (isset($product->array_options->options_packaging) && $maxstock < $product->array_options->options_packaging ) ) && is_page(doliconnectid('dolicart')) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK')) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER')) ) {
$doliline .= "<li class='list-group-item list-group-item-danger'>";
define('dolilockcart', '1'); 
} elseif ($product->stock_reel < $line->qty && $product->stock_reel > 0 && is_page(doliconnectid('dolicart')) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK')) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER')) ) {
$doliline .= "<li class='list-group-item list-group-item-warning'>";
define('dolilockcart', '1'); 
} else {
$doliline .= "<li class='list-group-item list-group-item-light'>";
//define('dolilockcart', '0'); 
}    
if ( $line->date_start != '' && $line->date_end != '' )
{
$start = wp_date('d/m/Y', $line->date_start);
$end = wp_date('d/m/Y', $line->date_end);
$dates =" <i>(Du $start au $end)</i>";
}

$doliline .= '<div class="w-100 justify-content-between"><div class="row"><div class="d-none d-sm-block col-sm-2 col-lg-1"><center>';
if ( !empty(doliconst('MAIN_MODULE_FRAISDEPORT')) && doliconst('FRAIS_DE_PORT_ID_SERVICE_TO_USE') == $line->fk_product ) {
$doliline .= '<i class="fas fa-shipping-fast fa-2x fa-fw"></i>';
} else {
$doliline .= doliconnect_image('product', $line->fk_product, null, $refresh);
}

$doliline .= '</center></div><div class="col-8 col-sm-7 col-md-5 col-lg-6"><h6 class="mb-1">'.doliproduct($line, 'product_label').'</h6>';

if ( doliconst('FRAIS_DE_PORT_ID_SERVICE_TO_USE') != $line->fk_product ) {
$doliline .= "<p><small>";
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $doliline .= "<i class='fas fa-toolbox fa-fw'></i> ".(!empty($product->ref)?$product->ref:'-'); }
if ( !empty($product->barcode) ) { 
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $doliline .= " | "; }
$doliline .= "<i class='fas fa-barcode fa-fw'></i> ".$product->barcode; }
$doliline .= "</small></p>";
if(!empty(doliconst("PRODUIT_DESC_IN_FORM")) && !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_DESC') ) { $doliline .= '<p class="mb-1"><small>'.doliproduct($line, 'product_desc').'</small></p>'; }
$doliline .= '<p><small><i>'.(isset($dates) ? $dates : null).'</i></small></p>';
} else {
$doliline .= '<small><a href="'.doliconnecturl('dolishipping').'">'.esc_html__( 'Shipping informations', 'doliconnect').'</a></small>';
}

if (( $maxstock <= 0 || (isset($product->array_options->options_packaging) && $maxstock < $product->array_options->options_packaging ) ) && is_page(doliconnectid('dolicart')) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK')) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER')) ) {
$doliline .= "<b>".__( "Sorry, this product is no longer available", 'doliconnect')."</b>";
} elseif ($product->stock_reel < $line->qty && $product->stock_reel > 0 && is_page(doliconnectid('dolicart')) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK')) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER')) ) {
$doliline .= "<b>".__( "Sorry, this product is not available with this quantity", 'doliconnect')."</b>";
}

$doliline .= '</div><div class="col d-none d-md-block col-md-3 text-right">';
if ( $object->statut == 0 && !is_page(doliconnectid('doliaccount')) && doliconst('FRAIS_DE_PORT_ID_SERVICE_TO_USE') != $line->fk_product  ) {
$doliline .= '<center>'.doliproductstock($product).'</center>';
if ( !empty($product->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".$lang, null, dolidelay('constante', $refresh));
$doliline .= "<center><small><span class='flag-icon flag-icon-".strtolower($product->country_code)."'></span> ".$country->label."</small></center>"; }
}

$doliline .= '</div><div class="col-4 col-sm-3 col-md-2 text-right"><h6 class="mb-1">'.doliprice($line, (empty(get_option('dolibarr_b2bmode'))?'total_ttc':'total_ht'), isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h6>';
if ( !empty(doliconst('MAIN_MODULE_FRAISDEPORT')) && doliconst('FRAIS_DE_PORT_ID_SERVICE_TO_USE') == $line->fk_product ) {
$doliline .= '<h6 class="mb-1">x'.$line->qty.'</h6>';
} elseif ( $object->statut == 0 && !is_page(doliconnectid('doliaccount')) ) {
$doliline .= "<input type='hidden' name='updateorderproduct[".$line->fk_product."][product]' value='".$line->fk_product."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][price]' value='".$line->subprice."'>";
$doliline .= "<input type='hidden' name='updateorderproduct[".$line->fk_product."][remise_percent]' value='".$line->remise_percent."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][date_start]' value='".$line->date_start."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][date_end]' value='".$line->date_end."'>";
$doliline .= "<div class='input-group input-group-sm mb-3'>";
//if (( $maxstock <= 0 || (isset($product->array_options->options_packaging) && $maxstock < $product->array_options->options_packaging ) ) && is_page(doliconnectid('dolicart')) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK')) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER')) ) {
$doliline .= "<div class='input-group-prepend'><button type='button' class='btn btn-danger' id='deleteorderproduct-".$line->fk_product."' name='deleteorderproduct-".$line->fk_product."' value='0' title='".__( 'Delete', 'doliconnect')."'><i class='fas fa-trash fa-fw'></i></button></div>";
//} else {
$doliline .= "<select class='form-control btn-light btn-outline-secondary' id='updateorderproduct-".$line->fk_product."' name='updateorderproduct-".$line->fk_product."'>";
if ( $product->stock_reel-$line->qty >= 0 && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES')) ) ) {
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$m0 = 1*$product->array_options->options_packaging;
$m1 = get_option('dolicartlist')*$product->array_options->options_packaging;
} else {
$m0 = 1;
$m1 = get_option('dolicartlist');
}
if ( $product->stock_reel-$line->qty >= $m1 || empty(doliconst('MAIN_MODULE_STOCK')) ) {
$m2 = $m1;
} elseif ( $product->stock_reel > $line->qty ) {
$m2 = $product->stock_reel;
} else { $m2 = $line->qty; }
} else {
if ( isset($line) && $line->qty > 1 ) { $m2 = $line->qty; }
else { $m2 = 1; }
} 
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$step = $product->array_options->options_packaging;
} else {
$step = 1;
}              
if ((empty($product->stock_reel) && !empty(doliconst('MAIN_MODULE_STOCK')) && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES')) )) || $m2 < $step)  { $doliline .= "<OPTION value='0' selected>".__( 'Unavailable', 'doliconnect')."</OPTION>"; 
} elseif (!empty($m2) && $m2 >= $step) {
foreach (range($m0, $m2, $step) as $number) {
if ( ($number == $step && empty($line->qty) ) || $number == $line->qty || ($number == $m0 && empty($line->qty) )) {
$doliline .= "<option value='$number' selected='selected'";
if ($product->stock_reel < $number && is_page(doliconnectid('dolicart')) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK')) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER')) ) $doliline .= " disabled";
$doliline .= ">x ".$number."</option>";
} else {
$doliline .= "<option value='$number' >x ".$number."</option>";
}
	}
$doliline .= "</select>";
} else {
$doliline .= '<h6 class="mb-1">x'.$line->qty.'</h6>';
}
$doliline .= "</div>";
$doliline .= "<script>";
$doliline .= "(function ($) {
$(document).ready(function(){
$('#deleteorderproduct-".$line->fk_product."').on('click',function(event){
event.preventDefault();
$('#DoliconnectLoadingModal').modal('show');
var qty = $(this).val();
console.log('".$line->fk_product." ' + qty + '".$line->subprice."');
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'update_cart',
            'productid': '".$line->fk_product."',
            'qty': qty,
            'price': '".$line->subprice."' 
          }
        }).done(function(response) {
      if (response.success) {
$('#a-tab-info').addClass('disabled'); 
$('#a-tab-pay').addClass('disabled');
window.location.reload();  
//document.getElementById('doliline').innerHTML = response.data.lines;
//if (document.getElementById('DoliHeaderCarItems')) {
//document.getElementById('DoliHeaderCarItems').innerHTML = response.data.items;
//}
//if (document.getElementById('DoliFooterCarItems')) {  
//document.getElementById('DoliFooterCarItems').innerHTML = response.data.items;
//}
//if (document.getElementById('DoliWidgetCarItems')) {
//document.getElementById('DoliWidgetCarItems').innerHTML = response.data.items;
//} 
console.log(response.data.message);
}
//$('#DoliconnectLoadingModal').modal('hide');
        });
});
$('#updateorderproduct-".$line->fk_product."').on('change',function(event){
event.preventDefault();
$('#DoliconnectLoadingModal').modal('show');
var qty = $(this).val();
console.log('".$line->fk_product." ' + qty + '".$line->subprice."');
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'update_cart',
            'productid': '".$line->fk_product."',
            'qty': qty,
            'price': '".$line->subprice."' 
          }
        }).done(function(response) {
      if (response.success) {
$('#a-tab-info').addClass('disabled'); 
$('#a-tab-pay').addClass('disabled');
window.location.reload();  
//document.getElementById('doliline').innerHTML = response.data.lines;
//if (document.getElementById('DoliHeaderCarItems')) {
//document.getElementById('DoliHeaderCarItems').innerHTML = response.data.items;
//}
//if (document.getElementById('DoliFooterCarItems')) {  
//document.getElementById('DoliFooterCarItems').innerHTML = response.data.items;
//}
//if (document.getElementById('DoliWidgetCarItems')) {
//document.getElementById('DoliWidgetCarItems').innerHTML = response.data.items;
//} 
console.log(response.data.message);
}
//$('#DoliconnectLoadingModal').modal('hide');
        });
});
});
})(jQuery);";
$doliline .= "</script>";
//} 
} else {
$doliline .= '<h6 class="mb-1">x'.$line->qty.'</h6>';
}
$doliline .= "</div></div></li>";
}
} else {
$doliline .= "<li class='list-group-item list-group-item-light'><br><br><br><br><br><center><h5>".__( 'Your basket is empty.', 'doliconnect')."</h5></center>";
if ( !is_user_logged_in() ) {
$doliline .= '<center>'.__( 'If you already have an account,', 'doliconnect').' ';

if ( get_option('doliloginmodal') == '1' ) {
       
$doliline .= '<a href="#" data-toggle="modal" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('sign in', 'doliconnect').'" role="button">'.__( 'sign in', 'doliconnect').'</a> ';
} else {
$doliline .= "<a href='".wp_login_url( doliconnecturl('dolicart') )."?redirect_to=".doliconnecturl('dolicart')."' title='".__('sign in', 'doliconnect')."'>".__( 'sign in', 'doliconnect').'</a> ';
}
$doliline .= __( 'to see your basket.', 'doliconnect').'</center>';
}
$doliline .= "<br><br><br><br><br></li>";
} 
return $doliline;
}

function doliunit($scale, $type, $refresh = null) {
$unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.scale%3A%3D%3A'".$scale."')%20AND%20(t.unit_type%3A%3D%3A'".$type."')", null, dolidelay('constante', $refresh));
return $unit[0]->short_label;
}

function doliduration($object) {
if ( !is_null($object->duration_unit) && 0 < ($object->duration_value)) {
$duration = $object->duration_value.' ';
if ( $object->duration_value > 1 ) {
if ( $object->duration_unit == 'y' ) { $duration .=__( 'years', 'doliconnect'); }
elseif ( $object->duration_unit == 'm' )  { $duration .=__( 'months', 'doliconnect'); }
elseif ( $object->duration_unit == 'd' )  { $duration .=__( 'days', 'doliconnect'); }
elseif ( $object->duration_unit == 'h' )  { $duration .=__( 'hours', 'doliconnect'); }
elseif ( $object->duration_unit == 'i' )  { $duration .=__( 'minutes', 'doliconnect'); }
} else {
if ( $object->duration_unit == 'y' ) { $duration .=__( 'year', 'doliconnect');}
elseif ( $object->duration_unit == 'm' )  { $duration .=__( 'month', 'doliconnect'); }
elseif ( $object->duration_unit == 'd' )  { $duration .=__( 'day', 'doliconnect'); }
elseif ( $object->duration_unit == 'h' )  { $duration .=__( 'hour', 'doliconnect'); }
elseif ( $object->duration_unit == 'i' )  { $duration .=__( 'minute', 'doliconnect'); }
}

if ( $object->duration_unit == 'i' ) {
$altdurvalue=60/$object->duration_value; 
}

} else {
$duration = '';
}
return $duration;
}

function dolipaymentterm($id, $refresh = false) {
$paymenterm = callDoliApi("GET", "/setup/dictionary/payment_terms?sortfield=rowid&sortorder=ASC&limit=100&active=1&sqlfilters=(t.rowid%3A%3D%3A'".$id."')", null, dolidelay('constante', $refresh)); 
//print var_dump($paymenterm[0]);
if ($paymenterm[0]->type_cdr == 1) {
$term = sprintf( _n( '%s day', '%s days', $paymenterm[0]->nbjour, 'doliconnect'), $paymenterm[0]->nbjour);
$term .= ", ".__( 'end of month', 'doliconnect');
} elseif ($paymenterm[0]->type_cdr == 2) {
$term = sprintf( _n( '%s day', '%s days', $paymenterm[0]->nbjour, 'doliconnect'), $paymenterm[0]->nbjour);
$term .= ", ".sprintf( __( 'the %s of month', 'doliconnect'), $paymenterm[0]->decalage);
} else {
$term = sprintf( _n( '%s day', '%s days', $paymenterm[0]->nbjour, 'doliconnect'), $paymenterm[0]->nbjour);
}
return $term;
}

function doliconnect_langs($arg) {

if (function_exists('pll_the_languages')) {       

print '<div class="modal fade" id="DoliconnectSelectLang" tabindex="-1" role="dialog" aria-labelledby="DoliconnectSelectLangLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content border-0"><div class="modal-header border-0">
<h5 class="modal-title" id="DoliconnectSelectLangLabel">'.__('Choose your language', 'doliconnect').'</h5><button id="closemodalSelectLang" type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span></button></div>';
 
print '<script>';
?>
function loadingSelectLangModal() {
jQuery("#closemodalSelectLang").hide();
jQuery("#SelectLangmodal-form").hide();
jQuery("#loadingSelectLang").show();  
}
<?php
print '</script>';

print '<div class="modal-body"><div class="card" id="SelectLangmodal-form"><ul class="list-group list-group-flush">';
$translations = pll_the_languages( array( 'raw' => 1 ) );
foreach ($translations as $key => $value) {
print "<a href='".$value['url']."?".$_SERVER["QUERY_STRING"]."' onclick='loadingSelectLangModal()' class='list-group-item list-group-item-light list-group-item-action list-group-item-light'>
<span class='flag-icon flag-icon-".strtolower(substr($value['slug'], -2))."'></span> ".$value['name'];
if ( $value['current_lang'] == true ) { print " <i class='fas fa-language fa-fw'></i>"; }
print "</a>";
}      

print '</ul></div>
<div id="loadingSelectLang" style="display:none"><br><br><br><center><div class="align-middle"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div><h4>'.__('Loading', 'doliconnect').'</h4></div></center><br><br><br></div>
</div></div></div></div>';

}    

}
add_action( 'wp_footer', 'doliconnect_langs', 10, 1);

function dolipaymentmethods($object = null, $module = null, $url = null, $refresh = false) {
global $current_user;

$request = "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods";
 
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$request .= "?type=".$module."&rowid=".$object->id;
$currency=strtolower($object->multicurrency_code?$object->multicurrency_code:'eur');  
$stripeAmount=($object->multicurrency_total_ttc?$object->multicurrency_total_ttc:$object->total_ttc)*100;
}

$listpaymentmethods = callDoliApi("GET", $request, null, dolidelay('paymentmethods', $refresh));
//print $listpaymentmethods;
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', $refresh)); 
//print $thirdparty;

$paymentmethods = "";
$lock = dolipaymentmodes_lock(); 
if ( isset($listpaymentmethods->stripe) ) {
$paymentmethods .= "<script src='https://js.stripe.com/v3/'></script>";
}
 
$paymentmethods .= doliloaderscript('doliconnect-paymentmethodsform');

if ( isset($listpaymentmethods->stripe) && in_array('payment_request_api', $listpaymentmethods->stripe->types) && !empty($module) && is_object($object) && isset($object->id) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= "<div id='pra-error-message' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= "<div id='payment-request-button'><!-- A Stripe Element will be inserted here. --></div>
<div id='else' style='display: none' ><br><div style='display:inline-block;width:46%;float:left'><hr width='90%' /></div><div style='display:inline-block;width: 8%;text-align: center;vertical-align:90%'><small class='text-muted'>".__( 'or', 'doliconnect-pro' )."</small></div><div style='display:inline-block;width:46%;float:right' ><hr width='90%'/></div><br></div>";
}

$paymentmethods .= '<div class="card shadow-sm"><ul class="list-group list-group-flush panel-group" id="accordion">';
if ( isset($listpaymentmethods->stripe) && empty($listpaymentmethods->stripe->live) ) {
$paymentmethods .= "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( "Stripe's in sandbox mode", 'doliconnect')."</b> <small>(<a href='https://stripe.com/docs/testing#cards' target='_blank' rel='noopener'>".__( "Link to Test card numbers", 'doliconnect')."</a>)</small></li>";
}

$paymentmethods .= '<div class="card-body text-muted"><small>';
if (isset($object) && !empty(get_option('doliconnectbeta'))) {
$paymentmethods .= '<div class="custom-control custom-checkbox">
  <input type="checkbox" class="custom-control-input" id="checkBox1" ">
  <label class="custom-control-label" for="checkBox1">'.sprintf( __( 'I read and accept the %s', 'doliconnect'), dolidocdownload('', '', '', __( 'Terms & Conditions', 'doliconnect'), false, 'btn-link')).'</label>
</div>';
$paymentmethods .= "<script>";
$paymentmethods .= "jQuery(document).ready(function() { 
jQuery('#checkBox1').click(function() {
jQuery('input[name=paymentmode]').attr('disabled', !jQuery('input[name=paymentmode]').attr('disabled'));
}); 
});"; 
$paymentmethods .= "</script>"; 
} else {
$paymentmethods .= "".sprintf( __( 'Read the %s', 'doliconnect'), dolidocdownload('', '', '', __( 'Terms & Conditions', 'doliconnect'), false, 'btn-link btn-sm'))."";
$paymentmethods .= "<script>";
$paymentmethods .= "jQuery(document).ready(function() { 
jQuery('input[name=paymentmode]').attr('disabled', !jQuery('input[name=paymentmode]').attr('disabled'));
});"; 
$paymentmethods .= "</script>"; 
}
$paymentmethods .= '</small></div>';

if (empty($listpaymentmethods->payment_methods)) {
$countPM = 0;
} else {
$countPM = count(get_object_vars($listpaymentmethods->payment_methods));
}

$pm = array();
if ( $listpaymentmethods->payment_methods != null ) {
foreach ( $listpaymentmethods->payment_methods as $method ) {
$pm[] .= "".$method->id."";                                                                                                                      
$paymentmethods .= "<li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>";
$paymentmethods .= '<input onclick="ShowHideDivPM(\''.$method->id.'\')" type="radio" id="'.$method->id.'" name="paymentmode" value="'.$method->id.'" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#'.$method->id.'" ';
if ( (!empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id != $method->id && !empty($module) && is_object($object) && isset($object->id)) || (date('Y/n') >= $method->expiration && !empty($object) && !empty($method->expiration)) ) { $paymentmethods .=" disabled "; }
elseif ( !empty($method->default_source) ) { $paymentmethods .=" checked "; }
$paymentmethods .= " disabled><label class='custom-control-label w-100' for='".$method->id."'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethods .= '<center><i ';
if ( $method->type == 'sepa_debit' || $method->type == 'PRE' || $method->type == 'VIR' ) { $paymentmethods .= 'class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"'; } 
elseif ( $method->brand == 'visa' ) { $paymentmethods .= 'class="fab fa-cc-visa fa-3x fa-fw" style="color:#172274"'; }
else if ( $method->brand == 'mastercard' ) { $paymentmethods .= 'class="fab fa-cc-mastercard fa-3x fa-fw" style="color:#FF5F01"'; }
else if ( $method->brand == 'amex' ) { $paymentmethods .= 'class="fab fa-cc-amex fa-3x fa-fw" style="color:#2E78BF"'; }
else { $paymentmethods .= 'class="fab fa-credit-card fa-3x fa-fw"';}
$paymentmethods .= '></i></center>';
$paymentmethods .= '</div><div class="col-9 col-sm-7 col-md-8 col-xl-8 align-middle"><h6 class="my-0">';
if ( $method->type == 'sepa_debit' || $method->type == 'PRE' || $method->type == 'VIR' ) {
$paymentmethods .= __( 'Account', 'doliconnect').' '.$method->reference;
//$paymentmethods .= '<small> <a href="'.$method->mandate_url.'" title="'.__( 'Mandate', 'doliconnect').' '.$method->mandate_reference.'" target="_blank"><i class="fas fa-info-circle"></i></a></small>';
} else {
$paymentmethods .= __( 'Card', 'doliconnect').' '.$method->reference;
}
if ( !empty($method->expiration) ) { $paymentmethods .=" - ".date("m/Y", strtotime($method->expiration.'/1')); }
if ( $method->default_source ) { $paymentmethods .= " <i class='fas fa-star fa-fw' style='color:Gold' ></i>"; }
$paymentmethods .= "</h6><small class='text-muted'>".$method->holder."</small></div>";
$paymentmethods .= "<div class='d-none d-sm-block col-2 align-middle text-right'>";
$paymentmethods .= "<span class='flag-icon flag-icon-".strtolower($method->country)."'></span>";
$paymentmethods .= "</div></div></label></div></li>";
$paymentmethods .= '<li id="'.$method->id.'Panel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .= "<div id='".$method->id."-error-message' class='text-danger' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= '<div class="btn-group btn-block" role="group" aria-label="actions buttons">';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
if ( $method->type == 'card' ) {
$paymentmethods .= '<button type="button" onclick="PayCardPM(\''.$method->id.'\')" class="btn btn-danger">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
} elseif ( $method->type == 'sepa_debit' ) {
$paymentmethods .= '<button type="button" onclick="PaySepaDebitPM(\''.$method->id.'\')" class="btn btn-danger">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
} else {
$paymentmethods .= '<button type="button" onclick="PayPM(\''.$method->type.'\')" class="btn btn-danger btn-block">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
}
} else {
$paymentmethods .= '<button type="button" onclick="DefaultPM(\''.$method->id.'\')" class="btn btn-warning"';
if ( !empty($method->default_source) ) { $paymentmethods .= " disabled"; }
$paymentmethods .= '>'.__( "Favourite", 'doliconnect').'</button>
<button type="button" onclick="DeletePM(\''.$method->id.'\')" class="btn btn-danger"';
if ( !empty($method->default_source) && $countPM > 1 ) { $paymentmethods .= " disabled"; }
$paymentmethods .= '>'.__( 'Delete', 'doliconnect').'</button>';
}
$paymentmethods .= '</div>';
$paymentmethods .= '</div></li>';
}} else {
$paymentmethods .= '<li class="list-group-item list-group-item-light list-group-item-action flex-column align-items-start"><div class="custom-control custom-radio">
<input type="radio" id="none" name="paymentmode" value="none" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#none" checked disabled>
<label class="custom-control-label w-100" for="none"><div class="row"><div class="col-3 col-md-2 col-xl-2 align-middle">
<center><i class="fas fa-border-none fa-3x fa-fw"></i></center></div><div class="col-9 col-md-10 col-xl-10 align-middle"><h6 class="my-0">'.__( 'No registered payment method', 'doliconnect').'</h6><small class="text-muted"></small></div></div></label>
</div></li>';
}
if ( $countPM < 5 && isset($listpaymentmethods->stripe) && in_array('card', $listpaymentmethods->stripe->types) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= '<li class="list-group-item list-group-item-light list-group-item-action flex-column align-items-start"><div class="custom-control custom-radio">
<input type="radio" id="card" name="paymentmode" value="card" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#card"  disabled>
<label class="custom-control-label w-100" for="card"><div class="row"><div class="col-3 col-md-2 col-xl-2 align-middle">
<center><span class="fa-stack fa-3x fa-fw" style="font-size: 1.5em;"><i class="fas fa-credit-card fa-stack-2x"></i><i class="fas fa-plus fa-stack-1x" style="color:Tomato"></i></span></center></div><div class="col-9 col-md-10 col-xl-10 align-middle"><h6 class="my-0">'.__( 'Credit/debit card', 'doliconnect').'</h6><small class="text-muted">Visa, Mastercard, Amex...</small></div></div></label>
</div></li>';
$paymentmethods .= '<li id="cardPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .= '<input id="cardholder-name" name="cardholder-name" value="" type="text" class="form-control" placeholder="'.__( "Card's owner", 'doliconnect').'" autocomplete="off" required>
<label for="card-element"></label>
<div class="form-control" id="card-element"><!-- a Stripe Element will be inserted here. --></div>';
$paymentmethods .= "<div id='card-error-message' class='text-danger' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= '<small>'.sprintf( esc_html__( 'By providing your card and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to the financial institution that issued your card to take payments from your card account in accordance with those instructions. You are entitled to a refund from your financial institution under the terms and conditions of your agreement with it. A refund must be claimed within 90 days starting from the date on which your card was debited.', 'doliconnect'), get_bloginfo('name')).'</small>';
$paymentmethods .= "</p>";
$paymentmethods .= '<p>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="cardDefault0" name="cardDefault" value="0"  class="custom-control-input" checked>
  <label class="custom-control-label text-muted" for="cardDefault0">'.__( "Not save", 'doliconnect').'</label>
</div>
<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="cardDefault1" name="cardDefault" value="1"  class="custom-control-input">
  <label class="custom-control-label text-muted" for="cardDefault1">'.__( "Save", 'doliconnect').'</label>
</div>';} else {
$paymentmethods .= '<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="cardDefault0" name="cardDefault" value="0"  class="custom-control-input"';
if (empty($countPM)) {
$paymentmethods .= ' disabled'; 
} else {
$paymentmethods .= ' checked';
} 
$paymentmethods .= '><label class="custom-control-label text-muted" for="cardDefault0">'.__( "Save", 'doliconnect').'</label>
</div>';
}
$paymentmethods .= '<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="cardDefault2" name="cardDefault" value="2" class="custom-control-input"';
if (empty($countPM)) {
$paymentmethods .= ' checked'; 
} 
$paymentmethods .= '><label class="custom-control-label text-muted" for="cardDefault2">'.__( "Save as favourite", 'doliconnect').'</label>
</div></p>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<button id="cardPayButton" class="btn btn-danger btn-block" >'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
} else {
$paymentmethods .= "<button id='cardButton' class='btn btn-warning btn-block' title='".__( 'Add', 'doliconnect')."'>".__( 'Add', 'doliconnect')."</button>";
}
$paymentmethods .= '</div></li>';
}
if ( $countPM < 5 && isset($listpaymentmethods->stripe) && in_array('sepa_debit', $listpaymentmethods->stripe->types) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= '<li class="list-group-item list-group-item-light list-group-item-action flex-column align-items-start"><div class="custom-control custom-radio">
<input type="radio" id="iban" name="paymentmode" value="iban" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#iban" disabled>
<label class="custom-control-label w-100" for="iban"><div class="row"><div class="col-3 col-md-2 col-xl-2 align-middle">
<center><span class="fa-stack fa-3x fa-fw" style="font-size: 1.5em;"><i class="fas fa-university fa-stack-2x"></i><i class="fas fa-plus fa-stack-1x" style="color:Tomato"></i></span></center></div><div class="col-9 col-md-10 col-xl-10 align-middle"><h6 class="my-0">'.__( 'Bank account', 'doliconnect').'</h6><small class="text-muted">Via SEPA Direct Debit</small></div></div></label>
</div></li>';
$paymentmethods .= '<li id="ibanPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .= '<input id="ibanholder-name" name="ibanholder-name" value="" type="text" class="form-control" placeholder="'.__( "Bank's owner", 'doliconnect').'" autocomplete="off" required>
<label for="iban-element"></label>
<div class="form-control" id="iban-element"><!-- a Stripe Element will be inserted here. --></div>';
$paymentmethods .= "<div id='bank-name' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= "<div id='iban-error-message' class='text-danger' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= '<small>'.sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with it. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect'), get_bloginfo('name')).'</small>';
$paymentmethods .= "</p>";
$paymentmethods .= '<p>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="ibanDefault0" name="ibanDefault" value="0"  class="custom-control-input" checked>
  <label class="custom-control-label text-muted" for="ibanDefault0">'.__( "Not save", 'doliconnect').'</label>
</div>
<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="ibanDefault1" name="ibanDefault" value="1"  class="custom-control-input">
  <label class="custom-control-label text-muted" for="ibanDefault1">'.__( "Save", 'doliconnect').'</label>
</div>';} else {
$paymentmethods .= '<div class="custom-control custom-radio custom-control-inline ">
  <input type="radio" id="ibanDefault0" name="ibanDefault" value="0"  class="custom-control-input"';
if (empty($countPM)) {
$paymentmethods .= ' disabled'; 
} else {
$paymentmethods .= ' checked';
} 
$paymentmethods .= '><label class="custom-control-label text-muted" for="ibanDefault0">'.__( "Save", 'doliconnect').'</label>
</div>';
}
$paymentmethods .= '<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="ibanDefault1" name="ibanDefault" value="1" class="custom-control-input"';
if (empty($countPM)) {
$paymentmethods .= ' checked'; 
} 
$paymentmethods .= '><label class="custom-control-label text-muted" for="ibanDefault1">'.__( "Save as favourite", 'doliconnect').'</label>
</div></p>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<button id="ibanPayButton" class="btn btn-danger btn-block" >'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
} else {
$paymentmethods .= "<button id='ibanButton' class='btn btn-warning btn-block' title='".__( 'Add', 'doliconnect')."'>".__( 'Add', 'doliconnect')."</button>";
}
$paymentmethods .= '</div></li>';
}
if ( isset($listpaymentmethods->stripe) && in_array('ideal', $listpaymentmethods->stripe->types) && !empty($module) && is_object($object) && isset($object->id) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .='<li class="list-group-item list-group-item-light list-group-item-action flex-column align-items-start"><div class="custom-control custom-radio">
<input type="radio" id="ideal" name="paymentmode" value="ideal" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#ideal" disabled>
<label class="custom-control-label w-100" for="ideal"><div class="row"><div class="col-3 col-md-2 col-xl-2 align-middle">
<center><i class="fab fa-ideal fa-3x fa-fw" style="color:#CC0066"></i></center></div><div class="col-9 col-md-10 col-xl-10 align-middle"><h6 class="my-0">'.__( 'iDEAL', 'doliconnect').'</h6><small class="text-muted">iDEAL PAYMENT</small></div></div></label>
</div></li>';
$paymentmethods .= '<li id="idealPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .= '<input id="idealholder-name" name="idealholder-name" value="" type="text" class="form-control" placeholder="'.__( "Bank's owner", 'doliconnect').'" autocomplete="off" required>
<label for="ideal-element"></label>
<div class="form-control" id="ideal-element"><!-- a Stripe Element will be inserted here. --></div>';
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= "</p>";
$paymentmethods .= '<button id="idealPayButton" class="btn btn-danger btn-block" >'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
$paymentmethods .= '</div></li>';
}

//offline payment methods
if ( isset($listpaymentmethods->VIR) && !empty($listpaymentmethods->VIR) ) {
$paymentmethods .= "<li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input type='radio' id='vir' name='paymentmode' value='vir' class='custom-control-input' data-toggle='collapse' data-parent='#accordion' ";
//if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_code != 'VIR' ) { $paymentmethods .=" disabled "; }
//else
if ( (!empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_code != 'VIR') || ($listpaymentmethods->payment_methods == null && !empty($listpaymentmethods->stripe) && !in_array('card', $listpaymentmethods->stripe->types)) || (isset($object) && $object->mode_reglement_code == 'VIR') ) { $paymentmethods .= " checked"; }
$paymentmethods .= " href='#vir' disabled><label class='custom-control-label w-100' for='vir'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethods .= '<center><i class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"></i></center>';
$paymentmethods .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Transfer', 'doliconnect')."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect')."</small>";
$paymentmethods .= '</div></div></label></div></li>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<li id="virPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .= '<button type="button" onclick="PayPM(\'VIR\')" class="btn btn-danger btn-block">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
$paymentmethods .= '</div></li>';
}}
if ( isset($listpaymentmethods->CHQ) && !empty($listpaymentmethods->CHQ) ) {
$paymentmethods .= "<li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input type='radio' id='chq' name='paymentmode' value='chq' class='custom-control-input' data-toggle='collapse' data-parent='#accordion' ";
//if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_code != 'CHQ' ) { $paymentmethods .=" disabled "; }
//else
if ( (!empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_code == 'CHQ') || ($listpaymentmethods->payment_methods == null && !in_array('card', $listpaymentmethods->stripe->types) && $listpaymentmethods->RIB == null) || (isset($object) && $object->mode_reglement_code == 'CHQ') ) { $paymentmethods .= " checked"; }
$paymentmethods .= " href='#chq' disabled><label class='custom-control-label w-100' for='chq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethods .= '<center><i class="fas fa-money-check fa-3x fa-fw" style="color:Tan"></i></center>';
$paymentmethods .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Check', 'doliconnect')."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect')."</small>";
$paymentmethods .= '</div></div></label></div></li>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<li id="chqPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .= '<button type="button" onclick="PayPM(\'CHQ\')" class="btn btn-danger btn-block">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
$paymentmethods .= '</div></li>';
}}
if ( ! empty(dolikiosk()) ) {
$paymentmethods .= "<li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-startt'><div class='custom-control custom-radio'>
<input type='radio' id='liq' name='paymentmode' value='liq' class='custom-control-input' data-toggle='collapse' data-parent='#accordion' ";
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_code != 'LIQ' ) { $paymentmethods .=" disabled "; }
elseif ( (!empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_code == 'LIQ') || ($listpaymentmethods->payment_methods == null && !in_array('card', $listpaymentmethods->stripe->types) && $listpaymentmethods->CHQ == null && $listpaymentmethods->RIB == null) ) { $paymentmethods .= " checked"; }
$paymentmethods .= " href='#liq' disabled><label class='custom-control-label w-100' for='liq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethods .= '<center><i class="fas fa-money-bill-alt fa-3x fa-fw" style="color:#85bb65"></i></center>';
$paymentmethods .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Cash', 'doliconnect')."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect')."</small>";
$paymentmethods .= '</div></div></label></div></li>';
if (!empty($module) && is_object($object) && isset($object->id)) {
$paymentmethods .='<li id="liqPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .='<button type="button" onclick="PayPM(\'LIQ\')" class="btn btn-danger btn-block">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
$paymentmethods .='</div></li>';
}}

$paymentmethods .= "</ul><div class='card-body text-muted'><small>";

$paymentmethods .= "<b>".__( 'Payment term', 'doliconnect').":</b> ";
if (!empty($thirdparty->cond_reglement_id)) { 
$paymentmethods .= dolipaymentterm($thirdparty->cond_reglement_id);
} else {
$paymentmethods .= __( 'immediately', 'doliconnect');
}

$paymentmethods .= "</small></div><div class='card-footer text-muted'>";
$paymentmethods .= "<small><div class='float-left'>";
$paymentmethods .= dolirefresh($request, $url, dolidelay('paymentmethods'));
$paymentmethods .= "</div><div class='float-right'>";
$paymentmethods .= dolihelp('ISSUE');
$paymentmethods .= "</div></small>";
$paymentmethods .= "</div></div>";

$paymentmethods .= "<script>";

if ( !empty($listpaymentmethods->stripe->account) && isset($listpaymentmethods->stripe->publishable_key) ) {
$paymentmethods .= "var stripe = Stripe('".$listpaymentmethods->stripe->publishable_key."', {
  stripeAccount: '".$listpaymentmethods->stripe->account."'
});";
} elseif ( isset($listpaymentmethods->stripe->publishable_key) ) {
$paymentmethods .= "var stripe = Stripe('".$listpaymentmethods->stripe->publishable_key."');";
}

$paymentmethods .= 'var style = {
  base: {
    color: "#32325d",
    lineHeight: "18px",
    fontSmoothing: "antialiased",
    fontSize: "16px",
    "::placeholder": {
      color: "#aab7c4"
    }
  },
  invalid: {
    color: "#fa755a",
    iconColor: "#fa755a"
  }
};'; 

$paymentmethods .= 'var options = {
  style: style,
  supportedCountries: ["SEPA"],
  placeholderCountry: "'.$listpaymentmethods->thirdparty->countrycode.'",
};';

$paymentmethods .= "function HideDivPM(controle = null) {
var listpm = ".json_encode($pm).";
var mpx;
for (mpx of listpm) {
if (mpx != controle) jQuery('#' + mpx + 'Panel').collapse('hide');
}
}"; 

if (!empty($module) && is_object($object) && isset($object->id) && !empty($listpaymentmethods->stripe->client_secret) && preg_match('/pi_/', $listpaymentmethods->stripe->client_secret)) {
$paymentmethods .= "stripe.retrievePaymentIntent('".$listpaymentmethods->stripe->client_secret."').then(function(result) {
    if (result.error) { 
    } else {
    if (result.paymentIntent.status == 'succeeded' || result.paymentIntent.status == 'processing' || result.paymentIntent.status == 'canceled' ) {
    window.location = '".$url."?step=validation';
    }
    }
  });";
}

$paymentmethods .= "jQuery('#none,#card,#iban,#ideal,#vir,#chq,#liq').on('click', function (e) {
          e.stopPropagation();";
if ( isset($listpaymentmethods->stripe->publishable_key) ) {
$paymentmethods .= "var elements = stripe.elements();";
}
if (!empty($listpaymentmethods->stripe->client_secret)) { 
$paymentmethods .= "var clientSecret = '".$listpaymentmethods->stripe->client_secret."';";
}
$paymentmethods .= "HideDivPM(this.id);
          if(this.id == 'card'){
var cardElement = elements.create('card', options);
cardElement.mount('#card-element');
var cardholderName = document.getElementById('cardholder-name');
cardholderName.value = '';
var cardButton = document.getElementById('cardButton');
var cardPayButton = document.getElementById('cardPayButton');
var displayCardError = document.getElementById('card-error-message');
displayCardError.textContent = '';
cardElement.addEventListener('change', function(event) {
  // Handle real-time validation errors from the card Element.
    console.log('Reset error message');
    displayCardError.textContent = '';
  if (event.error) {
    displayCardError.textContent = event.error.message;
    displayCardError.classList.add('visible');
    //cardButton.disabled = true;
  } else {
    displayCardError.textContent = '';
    displayCardError.classList.remove('visible');
    //cardButton.disabled = false;
  }
});
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#cardPanel').collapse('show');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              jQuery('#liqPanel').collapse('hide'); 
cardholderName.addEventListener('change', function(event) {
    console.log('Reset error message');
    displayCardError.textContent = '';
    //cardButton.disabled = false; 
});
if (cardButton) {
cardButton.addEventListener('click', function(event) {
console.log('We click on cardButton');
cardButton.disabled = true; 
        if (cardholderName.value == '')
        	{        
				console.log('Field Card holder is empty');
				displayCardError.textContent = 'We need an owner as on your card';
        cardButton.disabled = false; 
        jQuery('#DoliconnectLoadingModal').modal('hide');   
        	}
        else
        	{
  stripe.confirmCardSetup(
    clientSecret,
    {
      payment_method: {
        card: cardElement,
        billing_details: {name: cardholderName.value}
      }
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
jQuery('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
displayCardError.textContent = result.error.message;    
    } else {
      // The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'add_paymentmethod');
inputvar.setAttribute('value', result.setupIntent.payment_method);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default');
inputvar.setAttribute('value', jQuery('input:radio[name=cardDefault]:checked').val());
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
    }
  }); 
          }
});
}
if (cardPayButton) {
cardPayButton.addEventListener('click', function(event) {
console.log('We click on cardButton');
cardPayButton.disabled = true; 
        if (cardholderName.value == '')
        	{        
				console.log('Field Card holder is empty');
				displayCardError.textContent = 'We need an owner as on your card';
        cardPayButton.disabled = false; 
        jQuery('#DoliconnectLoadingModal').modal('hide');   
        	}
        else
        	{
  stripe.confirmCardPayment(
    clientSecret,
    {
      payment_method: {
        card: cardElement,
        billing_details: {name: cardholderName.value}
      }
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
jQuery('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
displayCardError.textContent = result.error.message;    
    } else {
      // The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentintent');
inputvar.setAttribute('value', result.paymentIntent.id);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentmethod');
inputvar.setAttribute('value', result.paymentIntent.payment_method);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default');
inputvar.setAttribute('value', jQuery('input:radio[name=cardDefault]:checked').val());
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
    }
  }); 
          }
});
}
              //alert('card');
          }else if(this.id == 'iban'){
var ibanElement = elements.create('iban', options);
ibanElement.mount('#iban-element'); 
var ibanholderName = document.getElementById('ibanholder-name');
ibanholderName.value = '';
var ibanButton = document.getElementById('ibanButton'); 
var ibanPayButton = document.getElementById('ibanPayButton');
var displayIbanError = document.getElementById('iban-error-message');
displayIbanError.textContent = ''; 
var bankName = document.getElementById('bank-name');
bankName.textContent = '';
ibanElement.addEventListener('change', function(event) {
  // Handle real-time validation errors from the iban Element.
    console.log('Reset error message');
    displayIbanError.textContent = '';
    bankName.textContent = '';
  if (event.error) {
    displayIbanError.textContent = event.error.message;
    displayIbanError.classList.add('visible');
    ibanButton.disabled = true;
  } else {
    displayIbanError.textContent = '';
    displayIbanError.classList.remove('visible');
    ibanButton.disabled = false;
  }
  // Display bank name corresponding to IBAN, if available.
  if (event.bankName) {
    bankName.textContent = event.bankName;
    bankName.classList.add('visible');
  } else {
    bankName.textContent = '';
    bankName.classList.remove('visible');
  }
});
              jQuery('#cardPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('show');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              jQuery('#liqPanel').collapse('hide'); 
ibanholderName.addEventListener('change', function(event) {
    console.log('Reset error message');
    displayIbanError.textContent = '';
    ibanButton.disabled = false; 
});
if (ibanButton) {
ibanButton.addEventListener('click', function(event) {
console.log('We click on ibanButton');
ibanButton.disabled = true; 
        if (ibanholderName.value == '')
        	{        
				console.log('Field iban holder is empty');
				displayIbanError.textContent = 'We need an owner as on your account';
        ibanButton.disabled = false; 
        jQuery('#DoliconnectLoadingModal').modal('hide');   
        	}
        else
        	{
  stripe.confirmSepaDebitSetup(
    clientSecret,
    {
      payment_method: {
        sepa_debit: ibanElement,
        billing_details: {
          name: ibanholderName.value,
          email: '".$listpaymentmethods->thirdparty->email."'
        }
      }
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
jQuery('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
displayIbanError.textContent = result.error.message;    
    } else {
      // The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'add_paymentmethod');
inputvar.setAttribute('value', result.setupIntent.payment_method);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default');
inputvar.setAttribute('value', jQuery('input:radio[name=ibanDefault]:checked').val());
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
    }
  }); 
          }
});
}
if (ibanPayButton) {
ibanPayButton.addEventListener('click', function(event) {
console.log('We click on ibanButton');
ibanPayButton.disabled = true; 
        if (ibanholderName.value == '')
        	{        
				console.log('Field iban holder is empty');
				displayIbanError.textContent = 'We need an owner as on your account';
        ibanPayButton.disabled = false; 
        jQuery('#DoliconnectLoadingModal').modal('hide');   
        	}
        else
        	{
  stripe.confirmSepaDebitPayment(
    clientSecret,
    {
      payment_method: {
        sepa_debit: ibanElement,
        billing_details: {
          name: ibanholderName.value,
          email: '".$listpaymentmethods->thirdparty->email."'
        }
      }
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
jQuery('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
displayIbanError.textContent = result.error.message;    
    } else {
      // The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentintent');
inputvar.setAttribute('value', result.paymentIntent.id);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentmethod');
inputvar.setAttribute('value', result.paymentIntent.payment_method);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default');
inputvar.setAttribute('value', jQuery('input:radio[name=ibanDefault]:checked').val());
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
    }
  }); 
          }
});
}
              //alert('iban');
          }else if(this.id == 'ideal'){
var idealElement = elements.create('idealBank', options);
idealElement.mount('#ideal-element'); 
var idealholderName = document.getElementById('idealholder-name');
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              jQuery('#idealPanel').collapse('show');
              jQuery('#liqPanel').collapse('hide'); 
              //alert('ideal');
          }else if(this.id == 'vir'){               
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              jQuery('#virPanel').collapse('show');
              jQuery('#liqPanel').collapse('hide'); 
              //alert('vir');
          }else if(this.id == 'chq'){
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('show');
              jQuery('#liqPanel').collapse('hide'); 
              //alert('chq'); 
          }else if(this.id == 'liq'){
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide'); 
              jQuery('#liqPanel').collapse('show'); 
              //alert('chq');   
          }else {
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              jQuery('#liqPanel').collapse('hide'); 
              //alert('4');
          }
        })

function ShowHideDivPM(pm) {
              var displayPmError = document.getElementById( pm + '-error-message');
              displayPmError.textContent = '';
              HideDivPM(pm);
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              jQuery('#liqPanel').collapse('hide'); 
              jQuery('#' + pm + 'Panel').collapse('show');
        }
        
function DefaultPM(pm) {
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default_paymentmethod');
inputvar.setAttribute('value', pm);
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
        }

function DeletePM(pm) {
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'delete_paymentmethod');
inputvar.setAttribute('value', pm);
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
        }
        
function PayPM(pm) {
// The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentintent');
inputvar.setAttribute('value', null);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentmethod');
inputvar.setAttribute('value', pm);
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
}    
        
function PayCardPM(pm) {";
if (!empty($listpaymentmethods->stripe->client_secret)) { 
$paymentmethods .= "var clientSecret = '".$listpaymentmethods->stripe->client_secret."';";
}
$paymentmethods .= "var displayCardError = document.getElementById( pm + '-error-message');
displayCardError.textContent = '';
  stripe.confirmCardPayment(
    clientSecret,
    {
      payment_method: pm
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
jQuery('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
displayCardError.textContent = result.error.message;    
    } else {
      // The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentintent');
inputvar.setAttribute('value', result.paymentIntent.id);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentmethod');
inputvar.setAttribute('value', pm);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default');
inputvar.setAttribute('value', jQuery('input:radio[name=cardDefault]:checked').val());
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
    }
  }); 
}

function PaySepaDebitPM(pm) {";
if (!empty($listpaymentmethods->stripe->client_secret)) { 
$paymentmethods .= "var clientSecret = '".$listpaymentmethods->stripe->client_secret."';";
}
$paymentmethods .= "var displayIbanError = document.getElementById( pm + '-error-message');
displayIbanError.textContent = '';
  stripe.confirmSepaDebitPayment(
    clientSecret,
    {
      payment_method: pm
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
jQuery('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
displayIbanError.textContent = result.error.message;    
    } else {
      // The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentintent');
inputvar.setAttribute('value', result.paymentIntent.id);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentmethod');
inputvar.setAttribute('value', pm);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default');
inputvar.setAttribute('value', jQuery('input:radio[name=ibanDefault]:checked').val());
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
    }
  }); 
}";

if ( isset($listpaymentmethods->stripe) && in_array('payment_request_api', $listpaymentmethods->stripe->types) && !empty($module) && is_object($object) && isset($object->id) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= "
var clientSecret = '".$listpaymentmethods->stripe->client_secret."';
var displayError = document.getElementById('pra-error-message');
displayError.textContent = '';
stripe.retrievePaymentIntent(
  clientSecret
).then(function(result) {
if (result.error) { 
// Display error.message
displayError.textContent = result.error.message;
} else {
// The setup has succeeded. Display PRA button.
var paymentRequest = stripe.paymentRequest({
  country: '".$listpaymentmethods->thirdparty->countrycode."',
  currency: result.paymentIntent.currency,
  total: {
    label: 'Demo total',
    amount: result.paymentIntent.amount,
  },
  requestPayerName: false,
  requestPayerEmail: false,
});
var elements = stripe.elements();
var prButton = elements.create('paymentRequestButton', {
  paymentRequest: paymentRequest,
});

// Check the availability of the Payment Request API first.
paymentRequest.canMakePayment().then(function(result) {
  if (result) {
    jQuery('#else').show();
    prButton.mount('#payment-request-button');
  } else {
    document.getElementById('payment-request-button').style.display = 'none';
    jQuery('#else').hide();
  }
});

// Confirm payment
paymentRequest.on('paymentmethod', function(ev) {
  // Confirm the PaymentIntent without handling potential next actions (yet).
  stripe.confirmCardPayment(
    clientSecret,
    {payment_method: ev.paymentMethod.id},
    {handleActions: false}
  ).then(function(result) {
    if (result.error) {
      // Display error.message
displayError.textContent = result.error.message;  
    } else {
      // The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentintent');
inputvar.setAttribute('value', result.paymentIntent.id);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'paymentmethod');
inputvar.setAttribute('value', result.paymentIntent.payment_method);
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
    }
  }); 
});

}});
";   
}

                 
$paymentmethods .= "</script>";

return $paymentmethods;
}

function doliconnect_paymentmethods($object = null, $module = null, $url = null, $refresh = false) {
global $current_user;

$request = "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods";
 
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$request .= "?type=".$module."&rowid=".$object->id;
$currency=strtolower($object->multicurrency_code?$object->multicurrency_code:'eur');  
$stripeAmount=($object->multicurrency_total_ttc?$object->multicurrency_total_ttc:$object->total_ttc)*100;
}

$listpaymentmethods = callDoliApi("GET", $request, null, dolidelay('paymentmethods', $refresh));
//print $listpaymentmethods;
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', $refresh)); 
//print $thirdparty;

$paymentmethods = "";
 
if ( isset($listpaymentmethods->stripe) ) {
$paymentmethods .= "<script src='https://js.stripe.com/v3/'></script>";
$paymentmethods .= "<script>";
$paymentmethods .= 'var style = {
  base: {
    color: "#32325d",
    lineHeight: "18px",
    fontSmoothing: "antialiased",
    fontSize: "16px",
    "::placeholder": {
      color: "#aab7c4"
    }
  },
  invalid: {
    color: "#fa755a",
    iconColor: "#fa755a"
  }
};';
if ( !empty($listpaymentmethods->stripe->account) && isset($listpaymentmethods->stripe->publishable_key) ) {
$paymentmethods .= "var stripe = Stripe('".$listpaymentmethods->stripe->publishable_key."', {
  stripeAccount: '".$listpaymentmethods->stripe->account."'
});";
} elseif ( isset($listpaymentmethods->stripe->publishable_key) ) {
$paymentmethods .= "var stripe = Stripe('".$listpaymentmethods->stripe->publishable_key."');";
} 
if ( isset($listpaymentmethods->stripe->publishable_key) ) {
$paymentmethods .= "var elements = stripe.elements();";
}
if (!empty($listpaymentmethods->stripe->client_secret)) { 
$paymentmethods .= "var clientSecret = '".$listpaymentmethods->stripe->client_secret."';";
}
$paymentmethods .= "</script>";
}

//if ( isset($listpaymentmethods->stripe) && in_array('payment_request_api', $listpaymentmethods->stripe->types) && !empty($module) && is_object($object) && isset($object->id) && empty($thirdparty->mode_reglement_id) ) {
//$paymentmethods .= "<div id='pra-error-message' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
//$paymentmethods .= "<div id='payment-request-button'><!-- A Stripe Element will be inserted here. --></div>
//<div id='else' style='display: none' ><br><div style='display:inline-block;width:46%;float:left'><hr width='90%' /></div><div style='display:inline-block;width: 8%;text-align: center;vertical-align:90%'><small class='text-muted'>".__( 'or', 'doliconnect-pro' )."</small></div><div style='display:inline-block;width:46%;float:right' ><hr width='90%'/></div><br></div>";
//} 

$paymentmethods .= "<div id='DoliPaymentmethodAlert' class='text-danger font-weight-bolder'></div><div class='card shadow-sm'>";

if (empty($listpaymentmethods->payment_methods)) {
$countPM = 0;
} else {
$countPM = count(get_object_vars($listpaymentmethods->payment_methods));
}

$paymentmethods .= "<div class='card-body'>";

if ( isset($listpaymentmethods->stripe) && empty($listpaymentmethods->stripe->live) ) {
$paymentmethods .= "<i class='fas fa-info-circle'></i> <b>".__( "Stripe's in sandbox mode", 'doliconnect')."</b> <small>(<a href='https://stripe.com/docs/testing#cards' target='_blank' rel='noopener'>".__( "Link to Test card numbers", 'doliconnect')."</a>)</small>";
}

$paymentmethods .= "<ul class='nav bg-light nav-pills rounded nav-fill flex-column' role='tablist'>";

if ( isset($listpaymentmethods->payment_methods) && $listpaymentmethods->payment_methods != null ) {
foreach ( $listpaymentmethods->payment_methods as $method ) {
$paymentmethods .= '<li id="li-'.$method->id.'" class="nav-item"><a class="nav-link';
if ( (!empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id != $method->id && !empty($module) && is_object($object) && isset($object->id)) || (date('Y/n') >= $method->expiration && !empty($object) && !empty($method->expiration)) ) { $paymentmethods .=" disabled "; }
elseif ( (!empty($method->default_source) && empty($thirdparty->mode_reglement_id) && !in_array($method->type, array('PRE','VIR'))) || (!empty($method->default_source) && !empty($thirdparty->mode_reglement_id) && in_array($method->type, array('PRE'))) ) { $paymentmethods .=" active"; }
$paymentmethods .= '" data-toggle="pill" href="#nav-tab-'.$method->id.'"><i ';
if ( $method->type == 'sepa_debit' || $method->type == 'PRE' || $method->type == 'VIR' ) { $paymentmethods .= 'class="fas fa-university fa-fw float-left" style="color:DarkGrey"'; } 
elseif ( $method->brand == 'visa' ) { $paymentmethods .= 'class="fab fa-cc-visa fa-fw float-left" style="color:#172274"'; }
else if ( $method->brand == 'mastercard' ) { $paymentmethods .= 'class="fab fa-cc-mastercard fa-fw float-left" style="color:#FF5F01"'; }
else if ( $method->brand == 'amex' ) { $paymentmethods .= 'class="fab fa-cc-amex fa-fw float-left" style="color:#2E78BF"'; }
else { $paymentmethods .= 'class="fab fa-credit-card fa-fw float-left"';}
$paymentmethods .= "></i> ";
if ( $method->type == 'sepa_debit' || $method->type == 'PRE' || $method->type == 'VIR' ) {
$paymentmethods .= __( 'Account', 'doliconnect')." ".$method->reference;
} else {
$paymentmethods .= __( 'Card', 'doliconnect').' '.$method->reference;
}
if ( $method->default_source && empty($thirdparty->mode_reglement_id) && !in_array($method->type, array('PRE','VIR')) || (!empty($method->default_source) && !empty($thirdparty->mode_reglement_id) && in_array($method->type, array('PRE'))) ) { $paymentmethods .= " <i class='fas fa-star fa-fw' style='color:Gold'></i>"; }
$paymentmethods .= "<span class='flag-icon flag-icon-".strtolower($method->country)." float-right'></span></a></li>";
}}
if (isset($listpaymentmethods->stripe) && in_array('card', $listpaymentmethods->stripe->types) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= '<li class="nav-item"><a onclick="dolistripecard();" class="nav-link';
if ($countPM >= 5) { 
$paymentmethods .= " disabled";
} elseif (empty($countPM)) {
$paymentmethods .= " active";
}
$paymentmethods .= '" data-toggle="pill" href="#nav-tab-card">
<i class="fas fa-credit-card fa-fw float-left"></i> ';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= __( 'Pay by credit/debit card', 'doliconnect');
} else {
$paymentmethods .= __( 'Add a credit/debit card', 'doliconnect');
}
$paymentmethods .= "</a></li>";
}
//if (isset($listpaymentmethods->stripe) && in_array('sepa_debit', $listpaymentmethods->stripe->types) && empty($thirdparty->mode_reglement_id) ) {
//$paymentmethods .= '<li class="nav-item"><a onclick="my_code();" class="nav-link';
//if ($countPM >= 5) $paymentmethods .= " disabled"; 
//$paymentmethods .= '" data-toggle="pill" href="#nav-tab-sepa_debit">
//<i class="fas fa-university fa-fw float-left"></i></span> '.__( 'Pay by levy', 'doliconnect').'</a></li>';
//}
if ( isset($listpaymentmethods->PAYPAL) && !empty($listpaymentmethods->PAYPAL) ) {
$paymentmethods .= '<li class="nav-item"><a class="nav-link" data-toggle="pill" href="#nav-tab-paypal">
<i class="fab fa-paypal float-left"></i> Paypal</a></li>';
}
if ( isset($listpaymentmethods->VIR) && !empty($listpaymentmethods->VIR) ) {
$paymentmethods .= "<li id='li-VIR' class='nav-item'><a class='nav-link ";
$mode_reglement_code = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'VIR')", null, dolidelay('constante'));
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id != $mode_reglement_code[0]->id && !empty($module) && is_object($object) && isset($object->id) ) { $paymentmethods .=" disabled "; }
elseif ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .=" active"; }
$paymentmethods .= "' data-toggle='pill' href='#nav-tab-vir'>
<i class='fa fa-money-check fa-fw float-left' style='color:Tan'></i> ".__( 'Pay by bank transfert', 'doliconnect');
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= " <i class='fas fa-star fa-fw' style='color:Gold'></i>"; }
$paymentmethods .= "</a></li>";
}
if ( isset($listpaymentmethods->CHQ) && !empty($listpaymentmethods->CHQ) ) {
$paymentmethods .= "<li id='li-CHQ' class='nav-item'><a class='nav-link ";
$mode_reglement_code = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'CHQ')", null, dolidelay('constante'));
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id != $mode_reglement_code[0]->id && !empty($module) && is_object($object) && isset($object->id) ) { $paymentmethods .=" disabled "; }
elseif ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .=" active"; }
$paymentmethods .= "' data-toggle='pill' href='#nav-tab-chq'>
<i class='fa fa-money-check fa-fw float-left' style='color:Tan'></i> ".__( 'Pay by bank check', 'doliconnect');
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= " <i class='fas fa-star fa-fw' style='color:Gold'></i>"; }
$paymentmethods .= "</a></li>";
}    
if ( ! empty(dolikiosk()) ) {
$paymentmethods .= '<li class="nav-item"><a class="nav-link" data-toggle="pill" href="#nav-tab-kiosk">
<i class="fas fa-money-bill-alt fa-fw float-left" style="color:#85bb65"></i> '.__( 'Pay at front desk', 'doliconnect').'</a></li>';
}

$paymentmethods .= "</ul><br><div class='tab-content'>";

if ( isset($listpaymentmethods->payment_methods) && $listpaymentmethods->payment_methods != null ) {
foreach ( $listpaymentmethods->payment_methods as $method ) {
$paymentmethods .= "<div class='tab-pane fade";
if ( $method->default_source && empty($thirdparty->mode_reglement_id) && !in_array($method->type, array('PRE','VIR')) || (!empty($method->default_source) && !empty($thirdparty->mode_reglement_id) && in_array($method->type, array('PRE'))) ) {
$paymentmethods .= " show active"; 
}
$paymentmethods .= "' id='nav-tab-".$method->id."'><div class='card bg-light' style='border:0'><div class='card-body'>";
$paymentmethods .= "<script>";
$paymentmethods .= "(function ($) {
$(document).ready(function(){
$('#defaultbtn_".$method->id.", #deletebtn_".$method->id."').on('click',function(event){
event.preventDefault();
$('#DoliconnectLoadingModal').modal('show');
var actionvalue = $(this).val();
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolipaymentmethod_request',
            'dolipaymentmethod-nonce': '".wp_create_nonce( 'dolipaymentmethod-nonce')."',
            'payment_method': '".$method->id."',
            'action_payment_method': actionvalue
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(actionvalue);
      if (response.success) {
if (actionvalue == 'delete_payment_method')  {
document.getElementById('li-".$method->id."').remove();
document.getElementById('nav-tab-".$method->id."').remove();
} else {
document.location = '".$url."';
}
if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
}
console.log(response.data);
}
$('#DoliconnectLoadingModal').modal('hide');
        });
});
});
})(jQuery);";
$paymentmethods .= "</script>";
$paymentmethods .= "<div class='row'>";
$paymentmethods .= "<div class='col'>
  <dt>".__( 'Debtor', 'doliconnect')."</dt>
  <dd>".__( 'Holder:', 'doliconnect')." ".$method->holder;
if (isset($method->mandate->creation) && !empty($method->mandate->creation)) {
$paymentmethods .= "<br>".__( 'Creation:', 'doliconnect');
$paymentmethods .= " ".wp_date( 'j F Y', $method->mandate->creation, false); }
if (isset($method->expiration) && !empty($method->expiration)) {
$paymentmethods .= "<br>".__( 'Expiration:', 'doliconnect');
$expdate =  explode("/", $method->expiration);
$timestamp = mktime(0, 0, 0, (int) $expdate['1'], 0, (int) $expdate['0']);
$paymentmethods .= " ".wp_date( 'F Y', $timestamp, false); }
$paymentmethods .= "</dd>
</div>";
if (isset($method->mandate) && !empty($method->mandate)) { $paymentmethods .= "<div class='col'>
  <dt>".__( 'Mandate', 'doliconnect')."</dt>
  <dd>".__( 'Reference:', 'doliconnect')." <a href='".$method->mandate->url."' target='_blank'>".$method->mandate->reference."</a>";
$paymentmethods .= "<br>".__( 'Type:', 'doliconnect')." ";
if ($method->mandate->type == 'multi_use') {
$paymentmethods .= __( 'Recurring', 'doliconnect'); 
} elseif ($method->mandate->type == 'single_use') {
$paymentmethods .= __( 'Unique', 'doliconnect');
}
$paymentmethods .= "</dd>
</div>"; }
$paymentmethods .= "</div>";
$paymentmethods .= "<p class='text-justify'><small><strong>Note:</strong> Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua.</small></p></div></div>";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
if ( $method->type == 'card' ) {
$paymentmethods .= '<br><button type="button" onclick="PayCardPM(\''.$method->id.'\')" class="btn btn-danger btn-block">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
} elseif ( $method->type == 'sepa_debit' ) {
$paymentmethods .= '<br><button type="button" onclick="PaySepaDebitPM(\''.$method->id.'\')" class="btn btn-danger btn-clock">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
} else {
$paymentmethods .= '<br><button type="button" onclick="PayPM(\''.$method->type.'\')" class="btn btn-danger btn-block">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
}
} else {
$paymentmethods .= '<br><div class="btn-group btn-block" role="group" aria-label="actions buttons">';
if ( !isset($method->default_source) && !in_array($method->type, array('VIR')) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= "<button type='button' id='defaultbtn_".$method->id."' name='default_payment_method' value='default_payment_method' class='btn btn-light'";
$paymentmethods .= "title='".__( 'Favourite', 'doliconnect')."'><i class='fas fa-star fa-fw' style='color:Gold'></i> ".__( "Favourite", 'doliconnect')."</button>";
}
if ( (!isset($method->default_source) && $countPM > 1) || $countPM == 1  || in_array($method->type, array('VIR')) ) { 
$paymentmethods .= "<button type='button' id='deletebtn_".$method->id."' name='delete_payment_method' value='delete_payment_method' class='btn btn-light'";
$paymentmethods .= "title='".__( 'Delete', 'doliconnect')."'><i class='fas fa-trash fa-fw' style='color:Red'></i> ".__( 'Delete', 'doliconnect').'</button>';
}
$paymentmethods .= "</div>";
}
$paymentmethods .= "</div>";
}}

if ( $countPM < 5 && isset($listpaymentmethods->stripe) && in_array('card', $listpaymentmethods->stripe->types) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= "<div class='tab-pane fade";
if (empty($countPM) && empty($thirdparty->mode_reglement_id)) {
$paymentmethods .= " show active"; 
}
$paymentmethods .= "' id='nav-tab-card'><div class='card bg-white'><div class='card-body'>";
$paymentmethods .= "<input id='cardholder-name' name='cardholder-name' value='' type='text' class='form-control' placeholder='".__( "Full name on the card", 'doliconnect')."' autocomplete='off' required>
<label for='card-element'></label><div class='form-control' id='card-element'><!-- a Stripe Element will be inserted here. --></div>";
$paymentmethods .= "<p><div id='card-error-message' class='text-danger' role='alert'><!-- a Stripe Message will be inserted here. --></div></p>";
$paymentmethods .= '<p><div class="custom-control custom-radio custom-control-inline ">';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<input type="radio" id="cardDefault0" name="cardDefault" value="0"  class="custom-control-input" checked>
  <label class="custom-control-label text-muted" for="cardDefault0">'.__( "Not save", 'doliconnect').'</label>
</div>
<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="cardDefault1" name="cardDefault" value="1"  class="custom-control-input">
  <label class="custom-control-label text-muted" for="cardDefault1">'.__( "Save", 'doliconnect').'</label>
</div>';} else {
$paymentmethods .= '<input type="radio" id="cardDefault0" name="cardDefault" value="0"  class="custom-control-input"';
if (empty($countPM)) {
$paymentmethods .= ' disabled'; 
} else {
$paymentmethods .= ' checked';
} 
$paymentmethods .= '><label class="custom-control-label text-muted" for="cardDefault0">'.__( "Save", 'doliconnect').'</label>
</div>';
}
$paymentmethods .= '<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="cardDefault1" name="cardDefault" value="1" class="custom-control-input"';
if (empty($countPM)) {
$paymentmethods .= ' checked'; 
} 
$paymentmethods .= '><label class="custom-control-label text-muted" for="cardDefault1">'.__( "Save as favourite", 'doliconnect').'</label>
</div></p>';
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= "<small><strong>Note:</strong> ".sprintf( esc_html__( 'By providing your card and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to the financial institution that issued your card to take payments from your card account in accordance with those instructions. You are entitled to a refund from your financial institution under the terms and conditions of your agreement with it. A refund must be claimed within 90 days starting from the date on which your card was debited.', 'doliconnect'), get_bloginfo('name'))."</small>";
$paymentmethods .= "</p>";
$paymentmethods .= "<script>";
$paymentmethods .= "function dolistripecard(){
(function ($) {
$(document).ready(function(){";
$paymentmethods .= "var cardElement = elements.create('card', {style: style});
cardElement.mount('#card-element');
var cardholderName = document.getElementById('cardholder-name');
cardholderName.value = '';
var displayCardError = document.getElementById('card-error-message');
displayCardError.textContent = '';
cardElement.on('change', function(event) {
    console.log('Reset error message');
    displayCardError.textContent = '';
  if (event.error) {
    displayCardError.textContent = event.error.message;
    displayCardError.classList.add('visible');
  } else {
    displayCardError.textContent = '';
    displayCardError.classList.remove('visible');
  }
});";
// add a card
$paymentmethods .= "$('#cardButton').on('click',function(event){
event.preventDefault();
$('#cardButton').disabled = true;
$('#DoliconnectLoadingModal').modal('show');
console.log('Click on cardButton');
var cardholderName = document.getElementById('cardholder-name');
if (cardholderName.value == ''){               
console.log('Field Card holder is empty');
displayCardError.textContent = 'We need an owner as on your card';
$('#cardButton').disabled = false;
$('#DoliconnectLoadingModal').modal('hide');  
} else {
  stripe.confirmCardSetup(
    clientSecret,
    {
      payment_method: {
        card: cardElement,
        billing_details: {name: cardholderName.value}
      }
    }
  ).then(function(result) {
    if (result.error) {
$('#DoliconnectLoadingModal').modal('hide');
$('#cardButton').disabled = false;
console.log('Error occured when adding card');
displayCardError.textContent = result.error.message;    
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolipaymentmethod_request',
            'dolipaymentmethod-nonce': '".wp_create_nonce( 'dolipaymentmethod-nonce')."',
            'payment_method': result.setupIntent.payment_method,
            'action_payment_method': 'add_payment_method',
            'default': $('input:radio[name=cardDefault]:checked').val()
          }
        }).done(function(response) {
$(window).scrollTop(0);
console.log(response.data); 
      if (response.success) {
      if (document.getElementById('DoliPaymentmethodAlert')) {
      document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
      }
document.location = '".$url."';
      } else {
      if (document.getElementById('DoliPaymentmethodAlert')) {
      document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
      }
$('#DoliconnectLoadingModal').modal('hide');
      }
        });
    }
  }); 
          }     
});";
// pay with card script
$paymentmethods .= "});
})(jQuery);";
$paymentmethods .= "}";
$paymentmethods .= "window.onload=dolistripecard();";
$paymentmethods .= "</script></div></div><br>";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= "<button type='button' id='cardPayButton' class='btn btn-danger btn-block'>".__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</button>";
} else {
$paymentmethods .= "<button type='button' id='cardButton' class='btn btn-light btn-block' title='".__( 'Add', 'doliconnect')."'><i class='fas fa-plus-circle fa-fw'></i> ".__( 'Add', 'doliconnect')."</button>";
}
$paymentmethods .= "</div>";
}

if ( $countPM < 5 && isset($listpaymentmethods->stripe) && in_array('sepa_debit', $listpaymentmethods->stripe->types) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= "<div class='tab-pane fade";
//if (empty($countPM)) {
//$paymentmethods .= " show active";
//} else {
$paymentmethods .= "";
//}
$paymentmethods .= "' id='nav-tab-sepa_debit'>";
$paymentmethods .= "<input id='ibanholder-name' name='ibanholder-name' value='' type='text' class='form-control' placeholder='".__( "Full name of the owner", 'doliconnect')."' autocomplete='off' required>
<label for='iban-element'></label><div class='form-control' id='iban-element'><!-- a Stripe Element will be inserted here. --></div>";
$paymentmethods .= "<div id='bank-name' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= "<div id='iban-error-message' class='text-danger' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= "<small><strong>Note:</strong> ".sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with it. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect'), get_bloginfo('name'))."</small>";
$paymentmethods .= "</p>";
$paymentmethods .= "<script>";
$paymentmethods .= "</script>";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= "<button id='cardPayButton' class='btn btn-danger btn-block'>".__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</button>";
} else {
$paymentmethods .= "<button id='cardButton' class='btn btn-warning btn-block' title='".__( 'Add', 'doliconnect')."'>".__( 'Add', 'doliconnect')."</button>";
}
$paymentmethods .= "</div>";
}

if ( isset($listpaymentmethods->PAYPAL) && !empty($listpaymentmethods->PAYPAL) ) {
$paymentmethods .= '<div class="tab-pane fade" id="nav-tab-paypal"><div class="card bg-light" style="border:0"><div class="card-body">
<p>Paypal is easiest way to pay online</p>
<p>
<button type="button" class="btn btn-primary"> <i class="fab fa-paypal"></i> Log in my Paypal </button>
</p>
<p class="text-justify"><strong>Note:</strong> Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. </p>
</div></div></div>';
}

if ( isset($listpaymentmethods->VIR) && !empty($listpaymentmethods->VIR) ) {
$paymentmethods .= "<div class='tab-pane fade";
$mode_reglement_code = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'VIR')", null, dolidelay('constante'));
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .=" show active"; }
$paymentmethods .= "' id='nav-tab-vir'><div class='card bg-light' style='border:0'><div class='card-body'>";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= "<p class='text-justify'>".sprintf( __( 'Please send your bank transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account:', 'doliconnect'), doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null), $object->ref )."</p>";
} else {
$paymentmethods .= "<p class='text-justify'>".__( 'Please send your bank transfert at the following account:', 'doliconnect')."</p>";
}
$paymentmethods .= "<div class='row'>";
if (!empty($listpaymentmethods->VIR->bank)) { $paymentmethods .= "<div class='col'>
  <dt>".__( 'Bank', 'doliconnect')."</dt>
  <dd>".$listpaymentmethods->VIR->bank."</dd>
</div>"; }
if (!empty($listpaymentmethods->VIR->number)) { $paymentmethods .= "<div class='col'>
  <dt>".__( 'Account', 'doliconnect')."</dt>
  <dd>".$listpaymentmethods->VIR->number."</dd>
</div>"; }
if (!empty($listpaymentmethods->VIR->iban)) { $paymentmethods .= "<div class='col'>
  <dt>IBAN</dt>
  <dd>".$listpaymentmethods->VIR->iban."</dd>
</div>"; }
if (!empty($listpaymentmethods->VIR->bic)) { $paymentmethods .= "<div class='col'>
  <dt>BIC/SWIFT</dt>
  <dd>".$listpaymentmethods->VIR->bic."</dd>
</div>"; }
$paymentmethods .= "</div>";
$paymentmethods .= "<p class='text-justify'><small><strong>Note:</strong> Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua.</small></p></div></div>";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<br><button type="button" onclick="PayPM(\'VIR\')" class="btn btn-danger btn-block">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
}  
$paymentmethods .= "</div>";
}

if ( isset($listpaymentmethods->CHQ) && !empty($listpaymentmethods->CHQ) ) {
$paymentmethods .= "<div class='tab-pane fade";
$mode_reglement_code = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'CHQ')", null, dolidelay('constante'));
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .=" show active"; }
$paymentmethods .= "' id='nav-tab-chq'><div class='card bg-light' style='border:0'><div class='card-body'>";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= "<p class='text-justify'>".sprintf( __( 'Please send your money check in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address:', 'doliconnect'), doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null), $object->ref, $listpaymentmethods->CHQ->proprio)."</p>";
} else {
$paymentmethods .= "<p class='text-justify'>".sprintf( __( 'Please send your money check to <b>%s</b> at the following address:', 'doliconnect'), $listpaymentmethods->CHQ->proprio)."</p>";
}
$paymentmethods .= "<div class='row'>";
$paymentmethods .= "<div class='col'><dl class='param'>
  <dt>Address</dt>
  <dd>".$listpaymentmethods->CHQ->proprio." - ".$listpaymentmethods->CHQ->owner_address."</dd>
</dl></div>";
$paymentmethods .= "</div>";
$paymentmethods .= "<p class='text-justify'><small><strong>Note:</strong> Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua.</small></p></div></div>";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<br><button type="button" onclick="PayPM(\'CHQ\')" class="btn btn-danger btn-block">'.__( 'Pay', 'doliconnect')." ".doliprice($object, 'ttc', $currency).'</button>';
}
$paymentmethods .= "</div>";
}

if ( ! empty(dolikiosk()) ) {
$paymentmethods .= '<div class="tab-pane fade" id="nav-tab-kiosk">
<p>Pay at reception with the help of our guest</p>
<p><strong>Note:</strong> Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. </p>
</div>';
} 

$paymentmethods .= "</div><br><small><b>".__( 'Payment term', 'doliconnect').":</b> ";
if (!empty($thirdparty->cond_reglement_id)) { 
$paymentmethods .= dolipaymentterm($thirdparty->cond_reglement_id);
} else {
$paymentmethods .= __( 'immediately', 'doliconnect');
}
$paymentmethods .= "</small>"; 
//
//    $(document).ready(function(){
//       $("a[href='#nav-tab-chq']").hasClass('active') ) {
          //alert('tab:' + e.target.href);
          //document.getElementById('li-VIR').outerHTML = '';
//       });
//    });
//
if (isset($object)) {
$paymentmethods .= "<script>";
$paymentmethods .= "function PayPM(pm) {
(function ($) {
$(document).ready(function(){
$('#DoliconnectLoadingModal').modal('show');
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'pay_cart',
            'module': '".$module."',
            'id': '".$object->id."',
            'paymentintent': null,
            'paymentmethod': pm,        
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(response.data);
if (response.success) {

if (document.getElementById('nav-tab-pay')) {
document.getElementById('nav-tab-pay').innerHTML = response.data;      
}
$('#a-tab-cart').addClass('disabled');
if (document.getElementById('nav-tab-cart')) {
document.getElementById('nav-tab-cart').remove();    
}
$('#a-tab-info').addClass('disabled')
if (document.getElementById('nav-tab-info')) {
document.getElementById('nav-tab-info').remove();    
};

} else {

if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
}

}
console.log(response.data.message);
$('#DoliconnectLoadingModal').modal('hide');
});
});
})(jQuery);
}    
        
function PayCardPM(pm) {";
if (!empty($listpaymentmethods->stripe->client_secret)) { 
$paymentmethods .= "var clientSecret = '".$listpaymentmethods->stripe->client_secret."';";
}
$paymentmethods .= "var displayCardError = document.getElementById( pm + '-error-message');
displayCardError.textContent = '';
  stripe.confirmCardPayment(
    clientSecret,
    {
      payment_method: pm
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
jQuery('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
displayCardError.textContent = result.error.message;    
    } else {



    }
  }); 
}";
$paymentmethods .= "</script>";
}
$paymentmethods .= "</div><div class='card-footer text-muted'>";
$paymentmethods .= "<small><div class='float-left'>";
$paymentmethods .= dolirefresh($request, $url, dolidelay('paymentmethods'));
$paymentmethods .= "</div><div class='float-right'>";
$paymentmethods .= dolihelp('ISSUE');
$paymentmethods .= "</div></small>";
$paymentmethods .= "</div></div>";

return $paymentmethods;
}

function doli_gdrf_data_request_form( $args = array() ) {
global $current_user;

	wp_enqueue_script( 'gdrf-public-scripts');
 
	// Captcha
	$number_one = wp_rand( 1, 9 );
	$number_two = wp_rand( 1, 9 );

	// Default strings
	$defaults = array(
		'label_select_request' => esc_html__( 'Select your request:', 'doliconnect'),
		'label_select_export'  => esc_html__( 'Export Personal Data', 'doliconnect'),
		'label_select_remove'  => esc_html__( 'Remove Personal Data', 'doliconnect'),
		'label_input_email'    => esc_html__( 'Your email address (required)', 'doliconnect'),
		'label_input_captcha'  => esc_html__( 'Human verification (required)', 'doliconnect'),
		'value_submit'         => esc_html__( 'Send Request', 'doliconnect'),
		'request_type'         => 'both',
	);

	// Filter string array
	$args = wp_parse_args( $args, array_merge( $defaults, apply_filters( 'privacy_data_request_form_defaults', $defaults ) ) );

	// Check is 4.9.6 Core function wp_create_user_request() exists
	if ( function_exists( 'wp_create_user_request' ) ) {

		// Display the form
		ob_start();
		?>
		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" class="was-validated" id="gdrf-form">
			<input type="hidden" name="action" value="doli_gdrf_data_request" />
			<input type="hidden" name="gdrf_data_human_key" id="gdrf_data_human_key" value="<?php echo $number_one . '000' . $number_two; ?>" />
			<input type="hidden" name="gdrf_data_nonce" id="gdrf_data_nonce" value="<?php echo wp_create_nonce( 'gdrf_nonce'); ?>" />
    <div class="card shadow-sm"><ul class="list-group list-group-flush">
		<?php if ( 'export' === $args['request_type'] ) : ?>
			<input type="hidden" name="gdrf_data_type" value="export_personal_data" id="gdrf-data-type-export" />
		<?php elseif ( 'remove' === $args['request_type'] ) : ?>
			<input type="hidden" name="gdrf_data_type" value="remove_personal_data" id="gdrf-data-type-remove" />
		<?php else : ?>
<li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='gdrf-data-type-export' class='custom-control-input' type='radio' name='gdrf_data_type' value='export_personal_data' checked>
<label class='custom-control-label w-100' for='gdrf-data-type-export'><div class='row'>
		<?php if ( !isset($args['widget']) ) : ?>
<div class='d-none d-sm-block col-sm-3 col-md-2 align-middle'>
<center><i class='fas fa-download fa-3x fa-fw'></i></center>
</div>
		<?php endif; ?>
<div class='col-auto align-middle'><h6 class='my-0'><?php echo __( 'Export your data', 'doliconnect'); ?></h6><small class='text-muted'><?php echo __( 'You will receive an email with a secure link to your data', 'doliconnect'); ?></small>
</div></div></label></div></li>
<li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='gdrf-data-type-remove' class='custom-control-input' type='radio' name='gdrf_data_type' value='remove_personal_data'>
<label class='custom-control-label w-100' for='gdrf-data-type-remove'><div class='row'>
		<?php if ( !isset($args['widget']) ) : ?>
<div class='d-none d-sm-block col-sm-3 col-md-2 align-middle'>
<center><i class='fas fa-eraser fa-3x fa-fw'></i></center>
</div>
		<?php endif; ?>
<div class='col-auto align-middle'><h6 class='my-0'><?php echo __( 'Erase your data', 'doliconnect'); ?></h6><small class='text-muted'><?php echo __( 'You will receive an email with a secure link to confirm the deletion', 'doliconnect'); ?></small>
</div></div></label></div></li>
<?php if (!empty(get_option('doliconnectbeta'))) { ?>
<li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='gdrf-data-type-delete' class='custom-control-input' type='radio' name='gdrf_data_type' value='delete_personal_data' disabled>
<label class='custom-control-label w-100' for='gdrf-data-type-delete'><div class='row'>
		<?php if ( !isset($args['widget']) ) : ?>
<div class='d-none d-sm-block col-sm-3 col-md-2 align-middle'>
<center><i class='fas fa-trash fa-3x fa-fw'></i></center>
</div>
		<?php endif; ?>
<div class='col-auto align-middle'><h6 class='my-0'><?php echo __( 'Delete your account', 'doliconnect'); ?></h6><small class='text-muted'><?php echo __( 'Soon, you will be able to delete your account', 'doliconnect'); ?></small>
</div></div></label></div></li>
		<?php } endif; ?>
    
    <?php if ( empty($current_user->user_email) ) : ?>
<li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-start'>
		<label for="gdrf_data_email">
			<?php echo esc_html( $args['label_input_email'] ); ?>
		</label>
      <div class='input-group'>
        <div class='input-group-prepend'>
          <span class='input-group-text' id='gdrf_data_emailPrepend'><i class='fas fa-at fa-fw'></i></span>
        </div>
        <input class='form-control' type='email' id='gdrf_data_email' aria-describedby='gdrf_data_emailPrepend' name='gdrf_data_email' required>
      </div>
</li>
		<?php else : ?>
      <li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-start'>
      <label for='gdrf_data_email'><?php echo esc_html( $args['label_input_email'] ); ?></label>
      <div class='input-group'>
        <div class='input-group-prepend'>
          <span class='input-group-text' id='gdrf_data_emailPrepend'><i class='fas fa-at fa-fw'></i></span>
        </div>
        <input class='form-control' type='email' id='gdrf_data_email' aria-describedby='gdrf_data_emailPrepend' name='gdrf_data_email' value='<?php echo $current_user->user_email; ?>' readonly>
      </div>

      </li>
		<?php endif; ?>
       	<li class='list-group-item list-group-item-light list-group-item-action flex-column align-items-start'>
				<label for="gdrf_data_human">
					<?php echo esc_html( $args['label_input_captcha'] ); ?>   
				</label>
			<div class='input-group'>
      <div class='input-group-prepend'><span class='input-group-text' id='gdrf_data_emailPrepend'><?php echo $number_one . ' + ' . $number_two . ' = ?'; ?></div>
        	<input type="text" class="form-control" id="gdrf_data_human" name="gdrf_data_human" required />
			</div>
      </li>
      </ul>
			<div class="card-body">
        <input id="gdrf-submit-button" class="btn btn-danger btn-block" type="submit" value="<?php echo __( 'Validate the request', 'doliconnect'); ?>"/>
      </div>
<div class="card-footer text-muted">
<small><div class='float-left'>
</div><div class='float-right'>
<?php echo dolihelp('ISSUE'); ?>
</div></small>
</div></div>
      
		</form>
		<?php
		return ob_get_clean();
	} else {
		// Display error message
		return esc_html__( 'This plugin requires WordPress 4.9.6.', 'doliconnect');
	}

}

?>
