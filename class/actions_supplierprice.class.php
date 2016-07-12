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
		$form = new Form($db);
		$formcore = new TFormCore;
		
		if (in_array('ordersuppliercard', explode(':',$parameters['context'])) || in_array('invoicesuppliercard', explode(':',$parameters['context']))){
				
			
			//Création d'une ligne permettant d'ajouter un Tarif appartenant à un produit
			$TIdProducts = get_all_products();
			$TIdSupplierPrices = select_all_supplierprices();
			?>
			idprod_supplierprice
			<tr class="liste_titre nodrag nodrop">
                <td >Ajout d'une ligne à partir d'un tarif défini pour un produit</td>
                <td>Tarif à appliquer</td>
                <td align="right">TVA</td>
                <td align="right">Qté</td>
                <td align="right">Total HT</td>
                <td align="right">Réf.</td>
                <td colspan="<?php echo 3 ?>">&nbsp;</td> <!-- TODO rendre le colspan dynamique -->
            </tr>
            <tr class="impair">
                <td><?php 
                    $form->select_produits('', 'idprod_supplierprice', '', 20);
                    ?></td>
                <td width="20%">
                	<select style="width: 50%; text-align: left">
                		<option>
                			
                		</option>
                	</select>
                </td>
                <td align="right"><?php
                    ;
                ?></td>
                <td align="right"><input type="text" value="1" class="flat" id="qty_qsp" name="qty_qsp" size="2"></td>
                <td align="right"><input type="text" value="" class="flat" id="price_ht_qsp" name="price_ht_qsp" size="5"></td>
                <td align="right"><input type="text" value="" class="flat" id="ref_qsp" name="ref_qsp" size="5"></td>
                <td align="right">&nbsp;</td>
                <td colspan="<?php echo $colspan ?>"><input type="button" name="bt_add_supplierprice" id="bt_add_supplierprice" value="Ajouter" class="button"/></td>
            </tr>
			
			<script type="text/javascript">
				$(document).ready(function() {
					
					$("#idprod_supplierprice").change(function(){
						$("#idprod_supplierprice option:selected").each(function(){
							console.log($(this).val());
							var idproduct = $(this).val();
							$.ajax({
							type: 'POST', // On spécifie la méthode
							dataType : 'json',
							url: '<?php echo dol_buildpath('/supplierprice/script/interface.php', 2)?>',
							data: { 'idprod_supplierprice': idproduct,
									'json' : 1 }, 
							success: function (response) {
								console.log(response);
								$("#socid").append(new Option(nom,response));
								}
							} )
						});
					});
					
				});
				
			</script>
			
			<?php
			
			 
		}
		
	}


}