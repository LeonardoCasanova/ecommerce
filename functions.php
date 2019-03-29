<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;
use \Hcode\Model\Order;

function formatDate($date){

 
  return date('d/m/Y',strtotime($date));


}

function formatPrice($vlprice){

  if(!$vlprice > 0 ) $vlprice = 0;
  return number_format($vlprice, 2, '.', '');


}

function checkLogin($inadmin = true){

  return User::checkLogin($inadmin);


}

function getUserName(){

  $user = User::getFromSession();

  return  $user->getdesperson();

}

function getUserNameAdmin(){

  $user = User::getFromSessionAdmin();

  return  $user->getdesperson();

}



function countOrders(){

  $order = new Order();

  $count = $order->notifyOrders();
  
  
 return $count['orders'];
}


function getCartNrQtd() {

  $cart =  Cart::getFromSession();

  $totals = $cart->getProductsTotals();

  return $totals['nrqtd'];
}


function getCartVlSubTotal() {

  $cart =  Cart::getFromSession();

  $totals = $cart->getProductsTotals();

  return formatPrice($totals['vlprice']);
}


?>