<?php



use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function(){

  User::Verifylogin();

  $products = Product::listAll();

  $page = new PageAdmin();

  $page->setTpl("products", [

    "products"=>$products

  ]);
});

$app->get("/admin/products/create", function(){

  User::Verifylogin();

  $page = new PageAdmin();

  $page->setTpl("products-create");

  
});


$app->post("/admin/products/create", function(){

  User::Verifylogin();

  $product = new Product();

  $product->setData($_POST);

  $product->save();

  header("Location: /admin/products");
  exit;
  
}); 


$app->get("/admin/products/:idproduct", function($idproduct){

  User::Verifylogin();


  $product = new Product();

  $product->get((int)$idproduct);

  $page = new PageAdmin();

  $page->setTpl("products-update",[

    'product'=>$product->getValues()
  ]);

});




