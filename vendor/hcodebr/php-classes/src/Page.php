<?php

namespace Hcode;

use Rain\Tpl;
use \Hcode\Model\User;

class Page{
    
    private $tpl;
    private $options = [];
    private $defaults = [
        "data" => [],
        "header" => true,
        "footer" => true
    ];
    
    public function __construct($opts = array(), $tpl_dir = "/"){
        
        $this->options = array_merge($this->defaults, $opts);
        
        $config = array(
            "tpl_dir" => $_SERVER["DOCUMENT_ROOT"]."/INOVACMS/views/".$tpl_dir,
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"]."/INOVACMS/views-cache/",
            "debug" => false
        );
        
        
        Tpl::configure($config);
        
        $this->tpl = new Tpl;
        
        $this->setData($this->options["data"]);
        $this->setData(array("user" => $_SESSION[User::SESSION]));
        if(!is_null($_SESSION[User::SESSION])){
              $this->setData(array("menusSite" => User::getMenusHeader("S")));
              $this->setData(array("menusForms" => User::getMenusHeader("F")));
      
        }  
            
        
        if($this->options["header"]=== true) $this->tpl->draw("header");
    }
    
    public function setTPL($name,$data = array(), $returnHtml = false){
        
        $this->setData($data);
        
        return $this->tpl->draw($name,$returnHtml);        
    }
    
    private function setData($data = array()){
        
        foreach($data as $key => $value){
            $this->tpl->assign($key, $value);
        }
        
    }
    
    public function __destruct(){
        if($this->options["footer"]=== true) $this->tpl->draw("footer");
        
    }
    
    public function getTpl(){
        return $this->tpl;
    }
    
}


?>