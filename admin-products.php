<?php

use \Hcode\Model\Product;
use \Hcode\Model\User;
use \Hcode\PageAdmin;

$app->get("/admin/products", function () {

    User::Verifylogin();

  $search = (isset($_GET['search']) ? $_GET['search'] : "");
  $page = (isset($_GET['page'])) ? (INT)$_GET['page'] : 1;


  if($search != '') {

    $pagination = Product::getPageSearch($search, $page);
    
  } else {

    $pagination = Product::getPage($page);
  }
  $pages = [];

  for ($i=0; $i < $pagination['pages']; $i++) { 
   
    array_push($pages,[
      'href'=>'/admin/products?'.http_build_query([
      'page'=>$i+1,
      'search'=>$search
    ]),
    'text'=>$i+1
    ]);
  }
   
    $page = new PageAdmin();

    $page->setTpl("products", [
        "products" => $pagination['data'],
        'search'=>$search,
        'pages'=>$pages

    ]);
});

$app->get("/admin/products/create", function () {

    User::Verifylogin();

    $page = new PageAdmin();

    $page->setTpl("products-create");

});

/*
$app->get("/admin/products/:idproduct", function($idproduct){

User::Verifylogin();

$product = new Product();

$product->get((int)$idproduct);

$page = new PageAdmin();

$page->setTpl("products-update",[

'product'=>$product->getValues()
]);

});
 */
$app->get("/admin/products/:idproduct", function ($idproduct) {
    User::verifyLogin();
    $product = new Product();

    $product->get((int) $idproduct);
    $page = new PageAdmin();
    $valores = $product->getValues();
    $page->setTpl("products-update", [
        "product" => $valores,
    ]);
});

$app->post("/admin/products/:idproduct", function ($idproduct) {

    User::verifyLogin();

    $product = new Product();

    $product->get((int) $idproduct);

    $product->setData($_POST);

    $product->save();

    $product->setPhoto($_FILES["file"]);

    header('Location: /admin/products');

    exit;

});

$app->get("/admin/products/:idproduct/delete", function ($idproduct) {
  User::verifyLogin();
  $product = new Product();

  $product->get((int) $idproduct);

  $product->delete();

  header('Location: /admin/products');

  exit;
});
