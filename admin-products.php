<?php

use \Hcode\Model\Product;
use \Hcode\Model\User;
use \Hcode\PageAdmin;

$app->get("/admin/products", function () {

    User::Verifylogin();

    $products = Product::listAll();

    $page = new PageAdmin();

    $page->setTpl("products", [

        "products" => $products,

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
