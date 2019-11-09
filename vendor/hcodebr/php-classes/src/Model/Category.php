<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model {
    
    public static function listAll(){
        
        $sql = new Sql();
        
        return $sql->select("SELECT * FROM categories");
        
    }
    
    public function save(){
        
        $sql = new Sql();
        
        
        
        $sql->query(" INSERT INTO categories (description) VALUES ( :description)",[
            ":description" => $this->getdescription()         
        ]);
        
        
    }
    
    public function get($id){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM categories WHERE id = :id", [
            ":id" => $id
        ]);
        
        $this->setData($results[0]);        
        
    }
    
    public function update(){
        
        $sql = new Sql();
        
        $sql->query("UPDATE categories SET description = :description WHERE id = :id",[
            ":description" => $this->getdescription(),
            ":id" => $this->getid()
        ]);
        
        
        
    }
    
    public function delete(){
        
        $sql = new Sql();
        
        $sql->query("DELETE FROM categories WHERE id = :id", [":id" => $this->getid()]);
        
    }
    
    
    public function getProducts($related = true){
        
        $Sql = new Sql();
         
        if($related){
            return $Sql->select("
                SELECT * FROM products WHERE id IN (
                    SELECT p.id 
                    FROM products p 
                    INNER JOIN categoriesproducts pc ON p.id = pc.idproduct 
                    WHERE pc.idcategory = :id
                );",
                [":id" => $this->getid()]);
        }else{
            return $Sql->select("
                SELECT * FROM products WHERE id NOT IN (
                    SELECT p.id 
                    FROM products p 
                    INNER JOIN categoriesproducts pc ON p.id = pc.idproduct 
                    WHERE pc.idcategory = :id
                );",
                [":id" => $this->getid()]);
        }
        
        
    }
    
    
    public function addProduct(Product $product){
        
        $Sql = new Sql();
        
        var_dump($Sql->query("INSERT INTO categoriesproducts(idcategory, idproduct) VALUES (:idcategory, :idproduct)",[
            ":idcategory" => $this->getid(),
            ":idproduct" => $product->getid()
        ]));
        
    }
    
    public function removeProduct(Product $product){
        
        $Sql = new Sql();
        
        $Sql->query("DELETE FROM categoriesproducts WHERE idcategory = :idcategory AND idproduct = :idproduct",[
            ":idcategory" => $this->getid(),
            ":idproduct" => $product->getid()
        ]);
        
    }
    
}



?>