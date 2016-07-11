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
		dol_include_once('custom/supplierprice/lib/supplierprice.lib.php');
		dol_include_once('custom/supplierprice/class/supplierprice.class.php');
		dol_include_once('product/class/product.class.php');
			
		$TPDOdb = new TPDOdb;
		
		if (in_array('ordersuppliercard', explode(':',$parameters['context'])) || in_array('invoicesuppliercard', explode(':',$parameters['context']))){
				
			//TODO pour chaque produit le fetcher. Comparer l'id produit avec le fk_soc de tarif. Si un tarif existe, rentre la case cochable.
			
			$TIdProducts = get_all_products();
			$TIdSupplierPrices = select_all_supplierprices();
			var_dump('toto');
			foreach ($TIdProducts as $idproduct) {
				$product = new Product($db);
				$product->fetch($idproduct);
				
				foreach ($TIdSupplierPrices as $idSupplierprice) {
					$supplierprice = new TSupplierPrice();
					$supplierprice->load($TPDOdb, $idSupplierprice);
					
				
					
				}
				
			}
			//TODO traiter l'information et renvoyer 
		}
		
	}


}