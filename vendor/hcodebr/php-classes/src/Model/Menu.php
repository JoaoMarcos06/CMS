<?php
namespace Hcode\Model;

use Hcode\Model;
use Hcode\DB\Sql;

class Menu extends Model {
    
     public static function listAll(){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM menus ");
        
        return $results;
        
        
    }
    
    public function save(){
        
        $sql = new Sql();            
        
        $sql->query("INSERT INTO menus (description,link, icon, type) VALUES (:description, :link, :icon, :type)",[
            ":description"=> $this->getdescription(),
            ":link" => $this->getlink(),
            ":icon" => $this->geticon(),
            ":type" => $this->gettp()
            
        ]);
        
        
    }
    
    public function get($id){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM menus  WHERE id = :id ", array(
        ":id" => $id
        ));
        
        $this->setData($results[0]);        
        
    }
    
    public function update(){
        
        $sql = new Sql();
        
        $sql->query("UPDATE menus SET description = :description, link = :link, icon = :icon, type = :type WHERE id = :id", [
            ":description" => $this->getdescription(),
            ":link" => $this->getlink(),
            ":icon" => $this->geticon(),
            ":type" => $this->gettp(),
            ":id" => $this->getid()
        ]);
        
    }
    
    public function delete(){
        
        $sql = new Sql();
        
        $sql->query("DELETE FROM menus WHERE id = :id ",array(":id" => $this->getid()));
       
    
}
}

?>