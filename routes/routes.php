<?php

use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Menu;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Portfolio;
use \Hcode\Model\Deposition;
use \Hcode\Model\Contact;
use \Hcode\Model\File;
use \Hcode\File\CustomUploadHandler;
use \Hcode\File\UploadHandler;

$app->get('/', function() {
      
    User::verifyLogin();
    
	$page = new Page(array());
    
    $page->setTpl('index');
});



$app->get('/login', function() {
    
    $_SESSION[User::SESSION] = NULL;
    
    $error = isset($_GET["error"]) ? $_GET["error"] : "";
    
	$page = new Page([
        "header" => false,
        "footer" =>false
    ]);
    
    $page->setTpl('login', array(
    "error" => $error
    ));
});

$app->post('/login',function(){
    
    $access = User::login($_POST['user'],$_POST['pass']);
    
    if($access == "success")
       header("Location: /WWW/CMS/");   
    else
        header("Location: /WWW/CMS/login?error=".$access);
    
    
    exit;
});

$app->get('/logout',function(){
    User::logout();
    
    header("Location: /WWW/CMS/login");
    exit;
});

$app->get('/users', function(){
   
    User::verifyLogin();
    
    $users = User::listALL();
    
    $page = new Page(array(),"");
    
    $page->setTpl('/users/users', array(
        "users" => $users
    ));
    
});

$app->get('/users/create', function(){
   
    User::verifyLogin();
    
    $page = new Page(array());
    
    $page->setTpl('/users/users-create');
    
});

$app->get('/users/:id/delete', function($id){
    
    User::verifyLogin();
    
    $user = new User();
    
    $user->get((int)$id);
    
    $user->delete();
    
    header("Location: /WWW/CMS/users");
    exit;
    
});

$app->get('/users/:id', function($id){
   
    User::verifyLogin();
    
    $user = new User();
    
    $user->get((int) $id);
    
    $page = new Page(array());
    
    $page->setTpl('/users/users-update', array(
    "user" => $user->getData()
    ));
    
});

$app->post('/users/create', function(){
    
    User::verifyLogin();
    
    $user = new User();
    
    $user->setData($_POST);
    
    //$user->setData(array("pass" => ""));
    
    $password = password_hash($_POST["pass"],PASSWORD_DEFAULT, ["cost" => 12]);
        
    $user->setpass($password);
    
    
    $user->save();
    
    header("Location: /WWW/CMS/users");
    exit;
    
    
});

$app->post('/users/:id', function($id){
    
    User::verifyLogin();
    
    $user = new User();
    
    $user->get((int)$id);
    
    $user->setData($_POST);
    
    $user->update();
    
    header("Location: /WWW/CMS/users");
    exit;
    
});


$app->get("/users/:id/permissions",function($id){
    
    User::verifyLogin();
    
    $user = new User();
    
    $user->get((int) $id);
    
    $page = new Page();
    
    $page->setTpl("/users/users-menus",[
            "user" => $user->getData(),
            "menusRelated" => $user->getMenus(),
            "menusNotRelated" => $user->getMenus(false)
    ]);    
    
});

$app->get("/users/:id/permissions/:idmenu/add",function($id,$idmenu){
    
    User::verifyLogin();
    
    $user = new User();
    
    $user->get((int)$id);
    
    $menu = new Menu();
    
    $menu->get((int)$idmenu);
    
    $user->addMenu($menu);
    
    header("Location: /WWW/CMS/users/".$id."/permissions");
    exit;
    
    
});

$app->get("/users/:id/permissions/:idmenu/remove",function($id,$idmenu){
    
    User::verifyLogin();
    
    $user = new User();
    
    $user->get((int)$id);
    
    $menu = new Menu();
    
    $menu->get((int)$idmenu);
    
    $user->removeMenu($menu);
    
    header("Location: /WWW/CMS/users/".$id."/permissions");
    exit;
    
      
    
});




$app->get("/forgot", function(){
   
    $page = new Page([
        "header" => false,
        "footer" =>false
    ]);
    
    $page->setTpl('/forgot/forgot');
    
    
});

$app->post("/forgot", function(){
    
    $data = User::getForgot($_POST["email"]);
    
    $page = new Page();
    
    $htmlRequest = $page->setTpl("/email/forgot",array(
        "name" => $data["name"],
        "link" => $data["link"]
    ),true);
    
    
    $transport = (new Swift_SmtpTransport('mail.inovarymktdigital.com.br', 587))
    ->setUsername('contato@inovarymktdigital.com.br')
    ->setPassword('inovary2019#');
    
    $mail = new Swift_Mailer($transport);
        
    $message = (new Swift_Message("Redefinir Senha"))
                            ->setFrom(["no-reply@inovarymktdigital.com.br" => "Inovary Marketing Digital"])
                            ->setTo(array($data["email"] => $data["name"]))
                            ->setContentType("text/html")
                            ->setBody($htmlRequest);
    
    $mail->send($message);
    
   header("Location: /WWW/CMS/forgot/sent");
   exit;
    
});

$app->get("/forgot/sent", function(){
   
    $page = new Page([
        "header" => false,
        "footer" =>false
    ]);
    
    $page->setTpl('/forgot/forgot-sent');
    
    
});

$app->get("/forgot/reset", function(){
    
    $_SESSION[User::SESSION] = NULL;
    
    $user = User::validCodeReset($_GET["code"], $_GET["k"]);
    
    $page = new Page([
        "header" => false,
        "footer" =>false
    ]);
    
    $page->setTpl('/forgot/forgot-reset',array( "name" => $user["person"], "code" => $_GET["code"], "k" => $_GET["k"]));
    
});

$app->post("/forgot/reset", function(){
   
    $forgot = User::validCodeReset($_POST["code"], $_POST["k"]);
   
    
    User::setForgotUser($forgot["idrecovery"]);
    
    $user = new User();
    
    $user->get((int)$forgot["iduser"]);
    
    $password = password_hash($_POST["password"],PASSWORD_DEFAULT, ["cost" => 12]);
    
    $user->setPasswordRecoverie($password);
    
    $page = new Page([
        "header" => false,
        "footer" =>false
    ]);
    
    $page->setTpl('/forgot/forgot-reset-success');
    
});


$app->get('/products', function(){
   
    User::verifyLogin();
    
    $products = Product::listALL();
    
    $page = new Page(array(),"");
    
    $page->setTpl('/products/products', array(
        "products" => $products
    ));
    
});

$app->get("/products/create", function(){
    
    User::verifyLogin();
    
    $page = new Page(array(),"");
    
    $page->setTpl("/products/products-create");
});

$app->post("/products/create",function(){
    
    User::verifyLogin();
    
    $product = new Product();
    
    $product->setData($_POST);
    
    $product->save();
    
    header("Location: /WWW/CMS/products");
    exit;
});

$app->get("/products/:id/delete", function($id){
    
    User::verifyLogin();
    
    $produto = new Product();
    
    $produto->get((int) $id);
    
    $produto->delete();
    
    header("Location: /WWW/CMS/products");
    exit;
});

$app->get("/products/:id",function($id){
    
    User::verifyLogin();
    
    $product = new Product();
    
    $product->get((int) $id);
    
    $page = new Page();
    
    $page->setTpl("/products/products-update",array(
        "product" => $product->getData()
    ));
    
});

$app->post("/products/:id", function($id){
    
    User::verifyLogin();
    
    $product = new Product();
    
    $product->get((int) $id);
    
    $product->setData($_POST);
    
    $product->update();
    
    header("Location: /WWW/CMS/products");
    exit;
    
});

$app->get("/categories", function(){
    
    User::verifyLogin();
    
    $categories = Category::listAll();
    
    $page = new Page();
    
    $page->setTpl("/categories/categories", array(
        "categories" => $categories
    ));
    
});

$app->get("/categories/create", function(){
   
    User::verifyLogin();
    
    $page = new Page();
    
    $page->setTpl("/categories/categories-create");
    
});

$app->post("/categories/create", function(){
    
    User::verifyLogin();
    
    $category = new Category();
    
    
    $category->setData($_POST);
    
    $category->save();
    
    header("Location: /WWW/CMS/categories");
    exit;
    
});

$app->get("/categories/:id/delete", function($id){
    
    User::verifyLogin();
    
    $category = new Category();
    
    $category->get((int) $id);
    
    $category->delete();
    
    header("Location: /WWW/CMS/categories");
    exit;
    
});

$app->get("/categories/:id", function($id){
    
    User::verifyLogin();
    
    $category = new Category();
    
    $category->get((int) $id);
    
    $page = new Page();
    
    $page->setTpl("/categories/categories-update", array(
        "category" => $category->getData()
    ));
    
});

$app->post("/categories/:id", function($id){
    
    User::verifyLogin();
    
    $category = new Category();
    
    $category->get((int) $id);
    
    $category->setData($_POST);
    
    $category->update();
    
    header("Location: /WWW/CMS/categories");
    exit;
    
});

$app->get("/categories/:id/products",function($id){
    
    User::verifyLogin();
    
    $category = new Category();
    
    $category->get((int) $id);
    
    $page = new Page();
    
    $page->setTpl("/categories/categories-products",[
            "category" => $category->getData(),
            "productsRelated" => $category->getProducts(),
            "productsNotRelated" => $category->getProducts(false)
    ]);    
    
});

$app->get("/categories/:id/products/:idproduct/add",function($id,$idproduct){
    
    User::verifyLogin();
    
    $category = new Category();
    
    $category->get((int)$id);
    
    $product = new Product();
    
    $product->get((int)$idproduct);
    
    $category->addProduct($product);
    
    header("Location: /WWW/CMS/categories/".$id."/products");
    exit;
    
    
});

$app->get("/categories/:id/products/:idproduct/remove",function($id,$idproduct){
    
    User::verifyLogin();
    
    $category = new Category();
    
    $category->get((int)$id);
    
    $product = new Product();
    
    $product->get((int)$idproduct);
    
    $category->removeProduct($product);
    
    header("Location: /WWW/CMS/categories/".$id."/products");
    exit;
    
      
    
});

$app->get("/contact", function(){
    
    User::verifyLogin();
    
    $contacts = Contact::listAll();
    
    $page = new Page();
    
    $page->setTpl("/contact/index", array(
    "contacts" => $contacts));
    
});

$app->get("/contact/:id", function($id){
   
    User::verifyLogin();
    
    $contact = new Contact();
    
    $contact->get((int) $id);
    
    $page = new Page();
    
    $page->setTpl("/contact/read-mail", array(
        "contact" => $contact->getData()
    ));
});

$app->get("/files/:model/:id",function($model, $id){
    
    User::verifyLogin();
    
    $page = new Page();
    
    $page->setTpl("/files/files",array(
        "model" => $model,
        "id" => $id
    ));
});

$app->post("/files/:id/delete", function($id){
    
     User::verifyLogin();
    
    $file = new File();
    
    $file->get((int) $id);
    
   // unlink($file->getthumbnailurl());
    
    $file->delete();
    
    
    echo json_encode(array());
    
});

$app->post("/files/:model/:id", function($model, $id){
    
    User::verifyLogin();
    
    
    $files = File::listAll($model, $id);
    
    echo json_encode($files);
    
});

$app->post("/files/create/:model/:id", function($model, $id){
    
    User::verifyLogin();
    
   $uploadHandler = new CustomUploadHandler($model, $id);
    
    
});


$app->get("/menus", function(){
    
    User::verifyLogin();
    
    $menus = Menu::listAll();
    
    $page = new Page();
    
    $page->setTpl("/menus/menus", array(
    "menus" => $menus
    ));
    
});

$app->get("/menus/create", function(){
    
    User::verifyLogin();
    
    $page = new Page();
    
    $page->setTpl("/menus/menus-create");
    
});

$app->post("/menus/create", function(){
    
    User::verifyLogin();
    
   $menu = new Menu();
    
   $menu->setData($_POST);

   $menu->save();
    
    header("Location: /WWW/CMS/menus");
    exit;
    
});

$app->get("/menus/:id/delete",function($id){
   
    User::verifyLogin();
    
    $menu = new Menu();
    
    $menu->get((int) $id);
    
    $menu->delete();
    
    header("Location: /WWW/CMS/menus");
    exit;
    
});

$app->get("/menus/:id", function($id){
    
    User::verifyLogin();
    
    $menu = new Menu();
    
    
    $menu->get((int) $id);
    
    $page = new Page();
    
    
    $page->setTpl("/menus/menus-update",array(
        "menu" => $menu->getData()
    ));
    
    
});

$app->post("/menus/:id", function($id){
    
    User::verifyLogin();
    
    $menu = new Menu();
    
    $menu->get((int) $id);
    
    $menu->setData($_POST);
    
    $menu->update();
    
    header("Location: /WWW/CMS/menus");
    exit;
    
    
});



?>
