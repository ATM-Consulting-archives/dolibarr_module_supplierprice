<?php 

require('../config.php');
dol_include_once('/supplierprice/config.php');
dol_include_once('custom/supplierprice/lib/supplierprice.lib.php');
dol_include_once('custom/supplierprice/class/supplierprice.class.php');
dol_include_once('product/class/product.class.php');
$id = GETPOST('idprod_supplierprice');
$action = GETPOST('action');


switch ($action) {
	case 'appliquer_tarif':
		
		$TproductSupplierPrices = select_all_supplierprices($id);
		$PDOdb = new TPDOdb;
		$TData = array();
		
		
		foreach ($TproductSupplierPrices as $idsupplierprice) {
			$supplierprice = new TSupplierPrice;
			$supplierprice->load($PDOdb, $idsupplierprice);
			$TData[] = array(
					'id'        => $supplierprice->rowid,
					'ref_fourn' => $supplierprice->ref_fourn,
					'qty'       => $supplierprice->qty,
					'total'     => !empty($supplierprice->remise_percent) ? (($supplierprice->price*$supplierprice->remise_percent)/100 * $supplierprice->qty) : ($supplierprice->price * $supplierprice->qty)
			);
		}
		__out($TData);
		
		break;
	
	default:
		
		break;
}	
	
