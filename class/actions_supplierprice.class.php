<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_supplierprice.class.php
 * \ingroup supplierprice
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */


 
/**
 * Class Actionssupplierprice
 */
class Actionssupplierprice
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Overloading the formAddObjectLine function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function formAddObjectLine($parameters, $object, $action, $hookmanager){
		global $db, $langs, $conf;
		
		
		define('INC_FROM_DOLIBARR', true);
		dol_include_once('/supplierprice/config.php');
		dol_include_once('/supplierprice/lib/supplierprice.lib.php');
		dol_include_once('/supplierprice/class/supplierprice.class.php');
		dol_include_once('/product/class/product.class.php');
		dol_include_once('/societe/class/societe.class.php');
			
		$TPDOdb = new TPDOdb;
		$form = new Form($db);
		$formcore = new TFormCore;
		
		if (in_array('ordersuppliercard', explode(':',$parameters['context'])) || in_array('invoicesuppliercard', explode(':',$parameters['context']))){
			
			//var_dump($object);
			$fournisseur = new Societe($db);
			if (!empty($object->socid)){
				$fournisseur->fetch($object->socid);
			 }
			
			
			?>
			
			<script type="text/javascript">
				$(document).ready(function() {
					
					var selectprice = $("#idprodfournprice");
					
					if(selectprice.length>0){
						
						$.ajax({
								type: 'POST', // On spécifie la méthode
								dataType : 'json',
								url: '<?php echo dol_buildpath('/supplierprice/script/interface.php', 2)?>',
								data: { 'action' : 'get_produits',
									'json':1}
							}).done(function(response){
								selectprice.empty();
								var i=0;
								
								$.each(response, function(){
									console.log(response[i]);
									
									if (response[i].remise != 0){
										var percentage = (response[i].price * response[i].remise)/100;
										var puHT = (response[i].price/response[i].qty)-percentage;
									}else{
										var puHT = response[i].price/response[i].qty;
									}
									selectprice.append
									if((!response[i].date_deb) && (!response[i].date_fin )){
										selectprice.append('<option value="'+response[i].rowid+'">'+response[i].ref+' - '+response[i].libelle+' - '+puHT.toFixed(2)+'€/U - '+response[i].qty+'U</option>')
									}else if((response[i].date_deb) && (!response[i].date_fin )){
										selectprice.append('<option value="'+response[i].rowid+'">'+response[i].ref+' - '+response[i].libelle+' - '+puHT.toFixed(2)+'€/U - '+response[i].qty+'U - à partir du '+response[i].date_deb+'</option>')
									}else{
										selectprice.append('<option value="'+response[i].rowid+'">'+response[i].ref+' - '+response[i].libelle+' - '+puHT.toFixed(2)+'€/U - '+response[i].qty+'U - du '+response[i].date_deb+' au '+response[i].date_fin+'</option>')
									}
									
									i++;
								});
								
								
							});
					}
					
					
					
				});
				
			</script>
				
			<?php
		}
		
	}


}