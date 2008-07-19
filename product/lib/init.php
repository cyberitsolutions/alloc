<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

include(ALLOC_MOD_DIR."product/lib/product.inc.php");
include(ALLOC_MOD_DIR."product/lib/productCost.inc.php");
include(ALLOC_MOD_DIR."product/lib/productSale.inc.php");
include(ALLOC_MOD_DIR."product/lib/productSaleItem.inc.php");
include(ALLOC_MOD_DIR."product/lib/productSaleTransaction.inc.php");

class product_module extends module
{
  var $db_entities = array("product"
                         , "productCost"
                         , "productSale"
                         , "productSaleItem"
                         , "productSaleTransaction"
                         );
}

?>
