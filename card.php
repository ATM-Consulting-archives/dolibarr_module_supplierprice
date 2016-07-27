<?php
/* Copyright (C) 2007-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       dev/skeletons/skeleton_card.php
 *		\ingroup    mymodule othermodule1 othermodule2
 *		\brief      This file is an example of a php page
 *					Put here some comments
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
require('config.php');
require('class/supplierprice.class.php');
require('lib/supplierprice.lib.php');

// Change this following line to use the correct relative path from htdocs
include_once DOL_DOCUMENT_ROOT . '/core/lib/product.lib.php';
include_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
include_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';


// TODO voir si on garde
dol_include_once('/categories/class/categorie.class.php');


// Load traductions files requiredby by page
$langs->Load("other");
$langs->Load("bank");
$langs->Load("supplierprice@supplierprice");

// Get parameters
$id			= GETPOST('id','int');
$ref		= GETPOST('ref','alpha');
$action		= GETPOST('action','alpha');
$fk_supplier_price = GETPOST('fk_supplier_price', 'int');

$PDOdb = new TPDOdb;

// Protection if external user
if ($user->societe_id > 0)
{
	//accessforbidden();
}

// Load object if id or ref is provided as parameter
$object=new Product($db);
if ($id > 0 || ! empty($ref))
{
	$result=$object->fetch($id,$ref);
	if ($result < 0) dol_print_error($db);
}

// Initialize technical object to manage hooks of modules. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('productcard','globalcard'));
$extrafields = new ExtraFields($db);



/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

//var_dump($_REQUEST);exit;
$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// Action to add record
	if (($action == 'add' || $action == 'update') && ! GETPOST('cancel'))
	{
		$error=0;
		
		$supplierprice = new TSupplierPrice;
		if ($fk_supplier_price > 0) $supplierprice->load($PDOdb, $fk_supplier_price);

		if (! GETPOST('ref_fourn'))
		{
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Ref")), null, 'errors');
		}
		
		if (1>GETPOST('fk_soc'))
		{
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("supplier")), null, 'errors');
		}

		if (! $error)
		{
			$supplierprice->set_values($_REQUEST);
			$result=$supplierprice->save($PDOdb);
			
			if ($result > 0)
			{
				// Creation OK
				$urltogo=dol_buildpath('/supplierprice/card.php',1).'?id='.$object->id;
				header("Location: ".$urltogo);
				exit;
			}
			else
			{
				// Creation KO
				setEventMessages('supplierprice_error_save', null, 'errors');
				if ($action == 'add') $action='create';
				else $action='edit';
			}
		}
		else
		{
			if ($action == 'add') $action='create';
			else $action='edit';
		}
	}

	// Cancel
	if (($action == 'add' || $action == 'update') && GETPOST('cancel')) $action='view';

	// Action to delete
	if ($action == 'confirm_delete')
	{
		$result=$object->delete($user);
		if ($result > 0)
		{
			// Delete OK
			setEventMessages("RecordDeleted", null, 'mesgs');
			header("Location: ".dol_buildpath('/mymodule/list.php',1));
			exit;
		}
		else
		{
			if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
			else setEventMessages($object->error, null, 'errors');
		}
	}
}




/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans('SupplierPrice'),'');

$form=new Form($db);
$formproduct = new FormProduct($db);

// Put here content of your page

// Part to create
if ($action == 'create' || $action == 'edit')
{
	
	$fk_product_fourn_price = get_next_product_supplier_price_id();
	
	$supplierprice = new TSupplierPrice;
	if (!empty($conf->global->SUPPLIERPRICE_DEFAULT_TYPE)) $supplierprice->type_price = $conf->global->SUPPLIERPRICE_DEFAULT_TYPE;
	if ($action == 'edit') $supplierprice->load($PDOdb, $fk_supplier_price);
	
	print load_fiche_titre($langs->trans("SupplierPrice"));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	print '<input type="hidden" name="fk_product" value="'.$object->id.'">';
	print '<input type="hidden" name="fk_user_author" value="'.$user->id.'">';
	print '<input type="hidden" name="fk_supplier_price" value="'.$supplierprice->getId().'">';

	$head=product_prepare_head($object, $user);
	$titre=$langs->trans("CardProduct".$object->type);
	$picto=($object->type==1?'service':'product');
	dol_fiche_head($head, 'tabSupplierPrice1', $titre, 0, $picto);

	print '<table class="border centpercent">'."\n";
	
	// Fourn ref
    print '<tr><td class="fieldrequired" width="30%">'.$langs->trans("RefFourn").'</td><td>';
    print '<input value="'.$supplierprice->ref_fourn.'" size="12" name="ref_fourn" class="flat">';
    print '</td></tr>';
	
	// VAT
    print '<tr><td width="30%">'.$langs->trans("VATRate").'</td><td>';
    print $form->load_tva("tva_tx", ($action=='edit') ? $supplierprice->tva_tx : $object->tva_tx,$mysoc,'',$object->id,$object->tva_npr);
    print '</td></tr>';

	// Price base
	print '<tr><td width="30%">'.$langs->trans('PriceBase').'</td>';
	print '<td>';
	//print $form->select_PriceBaseType($object->price_base_type, "price_base_type");
	print 'HT</td>';
	print '</tr>';
	
	print '<tr><td width="30%">'.$langs->trans('PriceType').'</td><td>';
    print $form->selectarray("type_price", $supplierprice->TType_price, $supplierprice->type_price);
    print '</td></tr>';
    
    if($conf->multidevise->enabled){
        //Devise
		print '<tr><td>'.$langs->trans('Devise').'</td><td colspan="3">';
		print $form->selectCurrency( ($action=='edit') ? $supplierprice->currency_code : $conf->currency,"currency");
		print '</td></tr>';
	}

    //Pays
	print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">';
	print $form->select_country( ($action=='edit') ? $supplierprice->fk_country : 0,"fk_country");
	print '</td></tr>';
	
     //fournisseur
    print '<tr><td class="fieldrequired">'.$langs->trans('Supplier').'</td><td colspan="3">';
    print $form->select_company($supplierprice->fk_soc, 'fk_soc','',1);
    print '</td></tr>';
    
    //categorie
	print '<tr><td>'.$langs->trans('CategoriesSupplier').'</td><td colspan="3">';
	print $form->select_all_categories(2, ($action=='edit') ? $supplierprice->fk_categorie_client : 'auto', 'fk_categorie_client');
	print '</td></tr>';

	//Projet
	if (! empty($conf->projet->enabled)) 
	{
        include_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
        $formproject = new FormProjets($db);
        print '<tr><td>'.$langs->trans('Project').'</td><td colspan="3">';
        print $formproject->select_projects(-1, $supplierprice->fk_project, 'fk_project');
        print '</td></tr>';
	}
	
    // dates
    print '<tr><td width="30%">';
    print $langs->trans('DateBeginTarif');
    print '</td><td>';
    
    // Par défaut les tarifs n'ont pas de date de fin
    $show_empty = 0;
    if($action === 'add' || ($action === 'edit' && $tarif->date_start === 0)) {
        $show_empty = 1;
        $supplierprice->date_start = '';
    }
    
    $form->select_date($supplierprice->date_start,'date_start','','',$show_empty,"add",1,1);
    print '</td></tr>';
    
    print '<tr><td width="30%">';
    print $langs->trans('DateEndTarif');
    print '</td><td>';
    
    // Par défaut les tarifs n'ont pas de date de fin
    $show_empty = 0;
    if($action === 'add' || ($action === 'edit' && $tarif->date_end === 0)) {
        $show_empty = 1;
        $supplierprice->date_end = '';
    }
    
    $form->select_date($supplierprice->date_end,'date_end','','',$show_empty,"add",1,1);
    print '</td></tr>';
	
	$price = ( ($action=='edit') ? $supplierprice->price : $object->price);
	// Price
	print '<tr><td width="30%">';
	print $langs->trans('SellingPrice');
	print '</td><td>
	<input type="hidden" name="product_price" id="product_price" value="'.$price.'">
	<input size="10" name="price" value="'.price($price).'"></td></tr>';
			
	// Remise
	print '<tr><td width="30%">';
	print $langs->trans('Remise(%)');
	print '</td><td><input id="remise_percent" size="10" name="remise_percent" value="'.$supplierprice->remise_percent.'" />%</td></tr>';
	
	//Link to fournisseur price
	print '<input type="hidden" name="fk_product_fourn_price" value="'.$fk_product_fourn_price.'"/>';
	
	?>
		<script type="text/javascript">
		
		//POUR DEFINIR PLUS PRECISEMENT LES TYPES DE PRIX (utilité?)  
		
//			$('input[name=remise_percent]').change(function() {
//				var n_percent = parseInt($(this).val());
//				if (isNaN(n_percent)) { 
//					n_percent = 0;
//					$(this).val(0);
//				}
				
//				var price = $('#product_price').val();
//				if(n_percent>100 || n_percent<0) {
//					alert('<?php echo $langs->transnoentities('tarif_percent_not_between_0_100'); ?>');
//					$(this).val(0);
//					return false;
//				}
//				if($('#type_prix').val() != 'PERCENT/PRICE') {
//					$('input[name=price]').val(((100 - n_percent) * price / 100).toFixed(2));
//				}
//			});
			
//			$('input[name=price]').change(function() {
//				if($('#type_prix').val() != 'PERCENT/PRICE') {
//					var n_price = parseFloat($(this).val());
//					if (isNaN(n_price)) { 
//						n_price = 0;
//						$(this).val(0);
//					}
					
//					var price = parseFloat($('#product_price').val());
//					var percent;
//					
//					if (price == 0) {
//						percent = 0;
//					} else {
//						percent = - (((n_price - price) / price) *100 );
//					}
//					$('#remise_percent').val(percent.toFixed(0));
					
//				}
//			});

		</script>
	<?php
	
	//Quantité
	print '<tr><td width="30%">';
	print $langs->trans('Quantity');
	print '</td><td><input size="10" name="qty" value="'.__val($supplierprice->qty,1,'double',true).'"></td></tr>';
	print '<tr><td width="30%">';
	print $langs->trans('Unit');
	print '</td><td>';
	if($object->array_options['options_unite_vente']=='unite') print 'U';
	else print $formproduct->select_measuring_units("weight_units", $object->array_options['options_unite_vente'], ($action=='edit') ? $supplierprice->unite_value : $object->{$object->array_options['options_unite_vente'].'_units'});
	print '</td></tr>';
	
	
	
	print '</table>'."\n";

	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';
	
	print '</form>';
}

// Part to show record
if ($id && (empty($action) || $action == 'view' || $action == 'delete'))
{
	print load_fiche_titre($langs->trans("SupplierPrice"));
    
	$head=product_prepare_head($object, $user);
	$titre=$langs->trans("CardProduct".$object->type);
	$picto=($object->type==1?'service':'product');
	dol_fiche_head($head, 'tabSupplierPrice1', $titre, 0, $picto);

	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&fk_supplier_price=' . $fk_supplier_price, $langs->trans('DeleteMyOjbect'), $langs->trans('ConfirmDeleteMyObject'), 'confirm_delete', '', 0, 1);
		print $formconfirm;
	}
	
	print '<table class="border" width="100%">';

	// Ref
	print '<tr>';
	print '<td width="15%">'.$langs->trans("Ref").'</td><td colspan="2">';
	print $form->showrefnav($object,'fk_product','',1,'fk_product');
	print '</td>';
	print '</tr>';
	
	// Label
	print '<tr><td>'.$langs->trans("Label").'</td><td>'.$object->libelle.'</td>';
	print '</tr>';
	// TVA
	print '<tr><td>'.$langs->trans("VATRate").'</td><td>'.vatrate($object->tva_tx.($object->tva_npr?'*':''),true).'</td></tr>';
	
	// Status (to sell)
	print '<tr><td>'.$langs->trans("Status").' ('.$langs->trans("Sell").')</td><td>';
	print $object->getLibStatut(2,0);
	print '</td></tr>';
	
	print "</table>\n";
	
	print "</div>\n";
	
	print '<div class="tabsAction">
				<a class="butAction" href="'.dol_buildpath('/supplierprice/card.php',1).'?id='.$object->id.'&action=create">'.$langs->trans('AddSupplierPrice').'</a>
			</div><br></a>';
	
	dol_fiche_end();


	// Buttons
	print '<div class="tabsAction">'."\n";
	$parameters=array();
	$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

	print '</div>'."\n";

	
	
	
	$sql = getSql($object->id);

	$r = new TSSRenderControler(new TSupplierPrice);
	
	$THide = array(
			'id'
			,'base_poids'
			,'unite_value'
			,'base'
		);
		
	if(empty($conf->multidevise->enabled)) $THide[] = 'currency';
	
	print $r->liste($PDOdb, $sql, array(
		'limit'=>array('nbLine'=>1000)
		,'title'=>array(
			'base' =>$langs->trans('PriceBase')
			,'ref_fourn'=>$langs->trans('RefFourn')
			,'fk_soc'=>$langs->trans('Supplier')
			,'date_start'=>$form->textwithpicto($langs->trans('StartDate'), $langs->trans('StartDateInfo'), 1, 'help', '', 0, 3)
			,'date_end'=>$form->textwithpicto($langs->trans('EndDate'), $langs->trans('EndDateInfo'), 1, 'help', '', 0, 3)
			,'qty'=>$langs->trans('Quantity')
			,'currency'=>$langs->trans('Devise')
			,'type_price' =>$langs->trans('PriceType')
			,'unite'=>$langs->trans('Unit')
			,'price'=>$langs->trans('Tarif')
			,'remise_percent' =>$langs->trans('Remise(%)')
			,'tva_tx'=>$langs->trans('TVA')
			,'Total' =>$langs->trans('Total')
			,'Supprimer' =>$langs->trans('Delete')
			,'Pays' =>$langs->trans('Country')
		)
		,'type'=>array(/*'date_debut'=>'date','date_fin'=>'date',*/'tva' => 'number', 'prix'=>'number', 'Total' => 'number' , 'quantite' => 'number')
		,'hide'=> $THide
		,'link'=>array(
			'Actions'=>'
					<a href="?id=@id@&action=delete&fk_product='.$object->id.'" onclick="return confirm(\''.$langs->trans('ConfirmDelete').'\');">'.img_delete().'</a>
					<a href="?id=@id@&action=edit&fk_product='.$object->id.'">'.img_edit().'</a>
			'
		)
		,'eval'=>array(
			'type_price'=>'_getTypePrice("@val@")'
			,'fk_soc'=>'_getNomURLSoc(@val@)'
		)
	));
	
	print '
		<style type="text/css">
			#list_llx_tarif_conditionnement td div {
				text-align:left !important;
			}
		</style>
	';

}

function _getTypePrice($idPriceCondi)
{
	global $langs;
	
	$supplierprice = new TSupplierPrice;
	return $langs->trans($supplierprice->TType_price[$idPriceCondi]);
}

function _getNomURLSoc($id_soc) 
{
	global $db;
	
	$s = new Societe($db);
	$s->fetch($id_soc);
	
	if($s->id > 0) return $s->getNomUrl(1);
	else return $id_soc;
}

// End of page
llxFooter();
$db->close();
