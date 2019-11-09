<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Product extends Model {
    
    public static function listAll(){
        
        $sql = new Sql();
        
        return $sql->select("SELECT * FROM products");
        
    }
    
    public function save(){
        
        $sql = new Sql();
        
        $sql->query(" INSERT INTO products(description, observations, active) VALUES (:description, :observations, :active)", array(
            ":description" => $this->getdescription(),            
            ":observations" => $this->getobservations(),            
            ":active" => $this->getactive()            
        ));
        
        
    }
    
    public function get($id){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM products WHERE id = :id", [
            ":id" => $id
        ]);
        
        $this->setData($results[0]);        
        
    }
    
    public function update(){
        
        $sql = new Sql();
        
        $sql->query("UPDATE products SET  description = :description, observations = :observations, active = :active WHERE id = :id",[
            ":description" => $this->getdescription(),
            ":observations" => $this->getobservations(),
            ":active" => $this->getactive(),
            ":id" => $this->getid()
        ]);
        
        
        
    }
    
    public function delete(){
        
        $sql = new Sql();
        
        $sql->query("DELETE FROM products WHERE id = :id", [":id" => $this->getid()]);
        
    }
}



?>