<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Mailer;
use Hcode\Model;

class Category extends Model {
  
    public static function listAll() {

        $sql = new Sql();
        return $sql->select("select * from tb_categories order by descategory");
    }

    public function save() {

        $sql = new Sql();

        $results = $sql->select(
            "CALL sp_categories_save(:idcategory, :descategory)",
            array(
                ":idcategory" => $this->getidcategory(),
                ":descategory" => $this->getdescategory()
            )
        );
        $this->setData($results[0]);
    }

    public function get($idcategory) {

        $sql = new Sql();

        $results = $sql->select("
             SELECT * FROM tb_categories  WHERE idcategory = :idcategory;", array(
            ":idcategory" => $idcategory,
        ));

        $data = $results[0];

        $this->setData($data);
    }

    public function update() {

        $sql = new Sql();

        $results = $sql->select(
            "CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword,:desemail,:nrphone, :inadmin)",
            array(
                ":iduser" => $this->getiduser(),
                ":desperson" => $this->getdesperson(),
                ":deslogin" => $this->getdeslogin(),
                ":despassword" => $this->getdespassword(),
                ":desemail" => $this->getdesemail(),
                ":nrphone" => $this->getnrphone(),
                ":inadmin" => $this->getinadmin(),
            )
        );
        $this->setData($results[0]);
    }
    public function delete() {

        $sql = new Sql();

        $sql->query(" delete from tb_categories WHERE idcategory = :idcategory; ", array(
            ":idcategory" => $this->getidcategory(),
        ));
    }

    public static function getForgot($email, $inadmin = true) {
        $sql = new Sql();
        $results = $sql->select("
         SELECT *
         FROM tb_persons a
         INNER JOIN tb_users b USING(idperson)
         WHERE a.desemail = :email;
     ", array(
            ":email" => $email,
        ));
        if (count($results) === 0) {
            throw new \Exception("Não foi possível recuperar a senha.");
        } else {
            $data = $results[0];
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser" => $data['iduser'],
                ":desip" => $_SERVER['REMOTE_ADDR'],
            ));
            if (count($results2) === 0) {
                throw new \Exception("Não foi possível recuperar a senha.");
            } else {
                $dataRecovery = $results2[0];
                $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
                $result = base64_encode($iv . $code);
                if ($inadmin === true) {
                    $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result";
                } else {
                    $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$result";
                }
                $mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
                    "name" => $data['desperson'],
                    "link" => $link,
                ));
                $mailer->send();
                return $link;
            }
        }
    }
    public static function validForgotDecrypt($result) {
        $result = base64_decode($result);
        $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
        $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');
        $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
        $sql = new Sql();
        $results = $sql->select("
         SELECT *
         FROM tb_userspasswordsrecoveries a
         INNER JOIN tb_users b USING(iduser)
         INNER JOIN tb_persons c USING(idperson)
         WHERE
         a.idrecovery = :idrecovery
         AND
         a.dtrecovery IS NULL
         AND
         DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
     ", array(
            ":idrecovery" => $idrecovery,
        ));
        if (count($results) === 0) {
            throw new \Exception("Não foi possível recuperar a senha.");
        } else {
            return $results[0];
        }
    }

    public static function setForgotUsed($idrecovery) {

        $sql = new Sql();

        $sql->query("
        update tb_userspasswordsrecoveries  set dt_recovery = now() where idrecovery = :idrecovery", array(
            ":idrecovery" => $idrecovery,
        ));
    }

    public function setPassword($password) {

        $sql = new Sql();

        $sql->query("update tb_users set despassword = :password where iduser = :iduser", array(
            ":password" => $password,
            ":iduser" => $this->getiduser(),
        ));
    }
}
