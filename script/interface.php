<?php 

require('../config.php');
dol_include_once('/supplierprice/config.php');
dol_include_once('custom/supplierprice/lib/supplierprice.lib.php');
dol_include_once('custom/supplierprice/class/supplierprice.class.php');
dol_include_once('fourn/class/fournisseur.commande.class.php');
dol_include_once('fourn/class/fournisseur.facture.class.php');
dol_include_once('fourn/class/fournisseur.product.class.php');
dol_include_once('product/class/product.class.php');

$action = GETPOST('action');

$PDOdb = new TPDOdb;


switch ($action) {
	case 'get_produits':
		global $db;
			
		$TData = array();
			
		$allsupplierprices = get_all_prices_fourn();
		foreach($allsupplierprices as $supplierpriceid){
			
			$pricefourn = new ProductFournisseur($db);
			$product = new Product($db);
			
			$pricefourn->fetch_product_fournisseur_price($supplierpriceid);
			$TSupplierpriceIds = get_all_supplier_prices($pricefourn->product_fourn_price_id);
			$product->fetch($pricefourn->id);
			
			if (!empty($TSupplierpriceIds)){
				$supplierprice = new TSupplierPrice;
				
				$supplierprice->load($PDOdb, $id_supplierprice);
				$TData[] = array(
							'rowid' => $pricefourn->product_fourn_price_id,
							'ref' => $product->ref,
							'libelle' => $product->label,
							'price' => $pricefourn->fourn_price,
							'remise' => $pricefourn->fourn_remise_percent,
							'qty' => $pricefourn->fourn_qty,
							'date_deb' => date('d/m/Y',$supplierprice->date_start),
							'date_fin' => date('d/m/Y',$supplierprice->date_end)
													
				);
			}else{
				
				$TData[] = array(
							'rowid' => $pricefourn->product_fourn_price_id,
							'ref' => $product->ref,
							'libelle' => $product->label,
							'price' => $pricefourn->fourn_price,
							'remise' => $pricefourn->fourn_remise_percent,
							'qty' => $pricefourn->fourn_qty
				);
			}	
		}
		__out($TData);
		
		break;
	default:
		
		break;
}	
	
