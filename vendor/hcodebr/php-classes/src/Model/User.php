<?php

namespace Hcode\Model;
use Hcode\DB\Sql;
use Hcode\Model;

class User extends Model {

    const SESSION = "User";

    public static function login($login, $password) {

        $sql = new Sql();

        $results = $sql->select("select * from tb_users where deslogin = :LOGIN", array(
            ":LOGIN" => $login,
        ));

        if (count($results) === 0) {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }

        $data = $results[0];

       
        if (password_verify($password, $data["despassword"]) === 'true') {

              $user = new User();

              $user->setData($data);

              $_SESSION[User::SESSION] = $user->getValues();

            return $user;

        } else {

            throw new \Exception("Usuário inexistente ou senha inválidas.");
        }   

    } 

    public static function verifyLogin($inadmin = true) {

        if (!isset($_SESSION[User::SESSION]) ||
            !$_SESSION[User::SESSION] ||
            !(int) $_SESSION[User::SESSION]["iduser"] > 0 ||
            (bool) $_SESSION[User::SESSION]["inadmin"] !== $inadmin) {
            header("Location: /admin/login");
            exit;
        }
    }

    public static function logout() {

        $_SESSION[User::SESSION] = null;

    }

    public static function listAll() {

        $sql = new Sql();
        return $sql->select("select * from tb_users a inner join tb_persons b using(idperson) order by b.desperson");

    }

    public function save() {

        $sql = new Sql();

        $results = $sql->select(
            "CALL sp_users_save(:desperson, :deslogin, :despassword,:desemail,:nrphone, :inadmin)",
            array(
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

    public function get($iduser) {

        $sql = new Sql();

        $results = $sql->select("
             SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
            ":iduser" => $iduser,
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

        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser" => $this->getiduser(),
        ));
    }

   /* public static function getForgot($email) {

        $sql = new Sql();

        $results = $sql->select(" select * from tb_persons a inner join tb_users b using (idperson) where a.desemail = :email",
            array(":email" => $email,
            ));

        if (count($results) === 0) {
            throw new \Exception("Error Processing Request", 1);
        } else {

            $data = $results[0];

            $results2 = $sql->select("
                CALL sp_userspasswordsrecoveries_create (:iduser, :desip)", array(
                ":iduser" => $data["iduser"],
                ":desip" => $_SERVER["REMOTE_ADDR"],
            ));

            if (count($results2) === 0) {
                throw new \Exception("Error Processing Request", 1);
            } else {
                $dataRecovery = $results2[0];

                $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_EBC));

                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da Hcode Store", "forgot", array(
                    "name" => $data["desperson"],
                    "link" => $link,
                ));

                $mailer->send();

                return $data;
            }

        }
    }

    public static function validForgotDecrypt($code) {

        $idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_EBC);

        $sql = new Sql();

        $results = $sql->select("select * from tb_userspasswordsrecoveries a
                                inner join tb_users b using(iduser)
                                inner join tb_persons c using(idperson)
                                where
                                a.idrecovery = :idrecovery
                                and
                                a.dtrecovery is null
                                and
                                date_add(a.dtregister, interval 1 hour)
                                >= current_date()", array(
                                ":idrecovery" => $idrecovery,
        ));
        if (count($results) === 0) {
            throw new \Exception("Erro de Processamento", 1);
        } else {
            return $results[0];
        }

    } */

}