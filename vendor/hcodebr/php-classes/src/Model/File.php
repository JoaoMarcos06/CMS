<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;


class File extends Model { 
           
    public static function listAll($model, $id){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM files WHERE idvinculo = :idvinculo AND model = :model ", array(
        ":idvinculo" => $id,
        ":model" => $model    
        ));
        
        $files = array("files" => $results);
        
        return $files;
        
        
    }
    
    public function save(){
        
        $sql = new Sql();
        
        $sql->query("INSERT INTO files(idvinculo, model, name, size, type, url, title, description) VALUES (:idvinculo, :model, :name, :size, :type, :url, :title, :description)", array(
            ":idvinculo" => $this->getidvinculo(),
            ":model" => $this->getmodel(),
            ":name" => $this->getname(),
            ":size" => $this->getsize(),
            ":type" => $this->gettype(),
            ":url" => $this->geturl(),
            ":title" => $this->gettitle(),
            ":description" => $this->getdescription()
        ));
        
        
    }
    
    public function get($id){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM files  WHERE id = :id ", array(
        ":id" => $id
        ));
        
        $this->setData($results[0]);        
        
    }
    
    public function update(){
        
        $sql = new Sql();
        
        
        $sql->query("UPDATE files SET name = :name, url = :url, title = :title, description = :description WHERE id = :id", array(
            ":name" => $this->getlogin(),
            ":url" => $this->geturl(),
            ":title" => $this->gettitle(),
            ":descripition" => $this->getdescription()
        ));
        
    }
    
    public function delete(){
        
        $sql = new Sql();
        
        $sql->query("DELETE FROM files WHERE id = :id ",array(":id" => $this->getid()));
        
    }
    
    public static function getFile($model, $idvinculo){
        
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT thumbnailUrl FROM files WHERE model = :model AND idvinculo = :idvinculo LIMIT 1 ORDER BY id DESC", array(
            ":model" => $model,
            ":idvinculo" => $idvinculo,
        ));
        
        if(count($results) > 0){
            return $results[0];
        } else{
            return array("thumbnailUrl" => "/INOVACMS/assets/dist/img/user2-160x160.jpg");
        }
        
        
        
    }
    
    
    
    
}



?>
