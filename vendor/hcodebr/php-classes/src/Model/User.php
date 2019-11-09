<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\File;
use \Hcode\Model\Menu;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class User extends Model {
    
    const SESSION = "user";  
    
        
    public static function login($user, $pass){
        
        $sql = new Sql();
        
        
        $results = $sql->select("SELECT * FROM users u INNER JOIN persons p ON p.id = u.idperson  WHERE login = :LOGIN", array(
            ':LOGIN' => $user
        ));
        
        if(count($results) === 0){
            return "access";
        }
        
        $data = $results[0];
        
        
        
        
        if(password_verify($pass, $data["password"]) === true){
            
            $user = new User();
            
            $user->setData($data);
            
            $user->setData(File::getFile('users', $user->getid()));
            
            $_SESSION[$user::SESSION] = $user->getData();
            
            
            
           return "success";
            
        }else{
            return "auth";
        }
        
        
    }
    
    
    public static function getFromSession(){
        
         $user = new User();
        
        if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["iduser"] > 0){
            
            $user->setData($_SESSION[User::SESSION]);
        }
           
        return $user;
        
    }
    
    public static function verifyLogin($inadmin = true){
        
        if(!User::checkLogin($inadmin)){
            
            User::logout();
            
            header("Location: /INOVACMS/login");
            exit;
        }
        
    }
    
    public static function logout(){
        $_SESSION[User::SESSION] = NULL;
    }

    public static function checkLogin($inadmin = true){
        
        if(!isset($_SESSION[User::SESSION]) ||
            !$_SESSION[User::SESSION] ||
            !(int)$_SESSION[User::SESSION]["id"] > 0 ){
           return false; 
        }else{
            
            return true;
            
        }
        
    }   
           
    public static function listAll(){
        
        $sql = new Sql();
        
        return $sql->select("SELECT * FROM users u INNER JOIN persons p ON p.id = u.idperson ORDER BY p.person ASC ");
        
    }
    
    public function save(){
        
        $sql = new Sql();
        
        
        
        $sql->query("INSERT INTO persons(person, office, email, nrphone) VALUES (:person,  :office, :email, :nrphone)", array(
            ":person" => $this->getperson(),
            ":office" => $this->getoffice(),
            ":email" => $this->getemail(),
            ":nrphone" => $this->getnrphone()
        ));
        
        $last_id = $sql->select("SELECT LAST_INSERT_ID() as idperson");
        $last_id = $last_id[0];
        
        
        $sql->query("INSERT INTO users (idperson, login, password, inadmin) VALUES (:idperson, :login, :password, :inadmin)", array(
            ":idperson" => $last_id["idperson"],
            ":login" => $this->getlogin(),
            ":password" =>$this->getpass(),
            ":inadmin" => $this->getinadmin()
        ));
        
        
    }
    
    public function get($id){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM users u INNER JOIN persons p ON p.id = u.idperson WHERE u.id = :id ", array(
        ":id" => $id
        ));
        
        $this->setData($results[0]);        
        
    }
    
    public function update(){
        
        $sql = new Sql();
        
        $sql->query("UPDATE persons SET person = :person, office = :office, email = :email, nrphone = :nrphone WHERE id = :idperson", array(
            ":person" =>$this->getperson(),
            ":office" =>$this->getoffice(),
            ":email" => $this->getemail(),
            ":nrphone" => $this->getnrphone(),
            ":idperson" => $this->getidperson()
        ));
        
        
        $sql->query("UPDATE users SET login = :login, inadmin = :inadmin WHERE id = :iduser", array(
            ":login" => $this->getlogin(),
            ":inadmin" => $this->getinadmin(),
            ":iduser" => $this->getid()
        ));
        
    }
    
    public function delete(){
        
        $sql = new Sql();
        
        $sql->query("DELETE FROM persons WHERE id = :idperson ",array(":idperson" => $this->getidperson()));
        $sql->query("DELETE FROM users WHERE id = :iduser ",array(":iduser" => $this->getid()));
        
    }
    
    
    public static function getForgot($email){
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT *,u.id as iduser FROM persons p INNER JOIN users u ON p.id = u.idperson WHERE p.email = :email", array(
            ":email" => $email));
        
        
        if(count($results) === 0 ){
            throw new \Exception("Não foi possível recuperar a senha ");
        }else{
            
            $data = $results[0];
            $sql->query("INSERT INTO users_passwords_recoveries(iduser, ip) values (:iduser, :ip )",array(
                ":iduser" => $data["iduser"],
                ":ip" => $_SERVER["REMOTE_ADDR"]
            ));
            
            $recovery = $sql->select("SELECT * FROM users_passwords_recoveries WHERE id = LAST_INSERT_ID()");
            
            if(count($recovery) === 0){
                throw new \Exception("Não foi possível recuperar a senha");
            }else{
                $dataRecovery = $recovery[0];
                
                $key = Key::createNewRandomKey();
                $keyCipher = $key->saveToAsciiSafeString();
                
                
                $encrypt = Crypto::encrypt($dataRecovery["id"], $key);                
                
                $link = "http://www.inovarymktdigital.com.br/INOVACMS/forgot/reset?code=".$encrypt."&k=".$keyCipher;
                
                
                return array(
                    "name" => $data["person"],
                    "link" => $link,
                    "email" => $data["email"]
                );
                
            }
        }
    }
    
    
    public static function validCodeReset($code, $keyCipher){
        
        $key = Key::loadFromAsciiSafeString($keyCipher);
        
        
        $idrecovery = Crypto::decrypt($code,$key);
        
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT 
                                *,pr.id as idrecovery
                                FROM
                                    users_passwords_recoveries pr
                                        INNER JOIN
                                    users u ON u.id = pr.iduser
                                        INNER JOIN
                                    persons p ON p.id = u.idperson
                                WHERE 
                                 pr.id = :idrecovery
                                 AND date_add(pr.created_at, INTERVAL 1 HOUR) >= NOW()
                        ", array(":idrecovery" => $idrecovery));
        
        if(count($results) === 0){
            throw new \Exception("Não foi possível reucperar a senha");
        }else{
            return $results[0];
        }
        
        
    }
    
    
    public static function setForgotUser($idrecovery){
        
       
        $sql = new Sql();
        
        $sql->query("UPDATE user_passwords_recoveries SET dtrecovery = NOW() WHERE id = :id ", array(":id" => $idrecovery));
        
        
    }
    
    public function setPasswordRecoverie($password){
        
        $sql = new Sql();
        
        $sql->query("UPDATE users SET password = :password WHERE id = :id", array(":id" => $this->getid() , ":password" => $password));
        
    }
    
    
    public function getMenus($related = true){
        
            $Sql = new Sql();

            if($related){
                return $Sql->select("
                    SELECT * FROM menus WHERE id IN (
                        SELECT m.id 
                        FROM menus m 
                        INNER JOIN usersmenus um ON m.id = um.idmenu 
                        WHERE um.iduser = :id
                    );",
                    [":id" => $this->getid()]);
            }else{
                return $Sql->select("
                    SELECT * FROM menus WHERE id NOT IN (
                        SELECT m.id 
                        FROM menus m 
                        INNER JOIN usersmenus um ON m.id = um.idmenu 
                        WHERE um.iduser = :id
                    );",
                    [":id" => $this->getid()]);
            }
        
        
    }
    
    
    public function addMenu(Menu $menu){
        
        $Sql = new Sql();
        
        $Sql->query("INSERT INTO usersmenus(iduser, idmenu) VALUES (:iduser, :idmenu)",[
            ":iduser" => $this->getid(),
            ":idmenu" => $menu->getid()
        ]);
        
    }
    
    public function removeMenu(Menu $menu){
        
        $Sql = new Sql();
        
        $Sql->query("DELETE FROM usersmenus WHERE iduser = :iduser AND idmenu = :idmenu",[
            ":iduser" => $this->getid(),
            ":idmenu" => $menu->getid()
        ]);
        
    }
    
    
    public static function getMenusHeader($type){
        
            $Sql = new Sql();
            
            return $Sql->select("
                SELECT * FROM menus WHERE id IN (
                    SELECT m.id 
                    FROM menus m 
                    INNER JOIN usersmenus um ON m.id = um.idmenu 
                    WHERE (um.iduser = :id) AND (m.type = :type)
                );",
                [
                    ":id" => $_SESSION[User::SESSION]["id"],
                    ":type" => $type
                ]);
            
        
        
    }
    
}



?>
