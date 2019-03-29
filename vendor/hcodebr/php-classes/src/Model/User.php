<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Mailer;
use Hcode\Model;

class User extends Model
{

    const SESSION = "User";
    const SESSION_ADMIN = "User_Admin";
    const SECRET = "HcodePhp7_Secret";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSuccess";

    public static function getFromSession()
    {

        $user = new User();

        if (isset($_SESSION[User::SESSION]) && (int) $_SESSION[User::SESSION]['iduser'] > 0) {

            $user->setData($_SESSION[User::SESSION]);

        }

        return $user;
    }

    public static function getFromSessionAdmin()
    {

        $user = new User();

        if (isset($_SESSION[User::SESSION_ADMIN]) && (int) $_SESSION[User::SESSION_ADMIN]['iduser'] > 0) {

            $user->setData($_SESSION[User::SESSION_ADMIN]);

        }

        return $user;
    }

    public static function checkLogin($inadmin = true)
    {

        if ($inadmin == true) {

            if (!isset($_SESSION[User::SESSION_ADMIN]) || !$_SESSION[User::SESSION_ADMIN] ||
                !(int) $_SESSION[User::SESSION_ADMIN]["iduser"] > 0) {

                //Nao está logado
                return false;

            } else {

                if ($inadmin === true && (bool) $_SESSION[User::SESSION_ADMIN]["inadmin"] === true) {

                    return true;

                } else {

                    return false;
                }

            }

        } else {

            if (!isset($_SESSION[User::SESSION]) || !$_SESSION[User::SESSION] ||
                !(int) $_SESSION[User::SESSION]["iduser"] > 0) {

                //Nao está logado
                return false;

            } else {

                if ($inadmin === false && (bool) $_SESSION[User::SESSION]["inadmin"] === false) {

                    return true;

                } else if ($inadmin === true) {

                    return false;

                }

            }
        }

    }

    public static function login($login, $password)
    {

        $sql = new Sql();
        $verifica_tipo = '';

        if (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $login)) {

            $verifica_tipo = 'b.desemail';

        } else {

            $verifica_tipo = 'a.deslogin';

        }

        $results = $sql->select("select * from tb_users a inner join tb_persons b on
                                    a.idperson = b.idperson where $verifica_tipo = :LOGIN", array(
            ":LOGIN" => $login,
        ));

        if (count($results) === 0) {

            throw new \Exception("Usuário inexistente ou senha inválida.");
        }

        $data = $results[0];

        if (password_verify($password, $data["despassword"]) === true) {

            $user = new User();

            $data['desperson'] = $data['desperson'];

            $user->setData($data);

            if ($data['inadmin'] == 1) {

                $_SESSION[User::SESSION_ADMIN] = $user->getValues();

            } elseif ($data['inadmin'] == 0) {

                //die($data['inadmin']);

                $_SESSION[User::SESSION] = $user->getValues();

            }
            return $user;

        } else {

            throw new \Exception("Usuário inexistente ou senha inválidas.");
        }
    }

    public static function verifyLogin($inadmin = true)
    {

        if (!User::checkLogin($inadmin)) {

            if ($inadmin) {

                header("Location: /admin/login");

            } else {

                header("Location: login");
            }
            exit;
        }
    }

    public static function logout()
    {

        $_SESSION[User::SESSION] = null;

    }

    public static function logoutAdmin()
    {

        $_SESSION[User::SESSION_ADMIN] = null;

    }

    public static function listAll()
    {

        $sql = new Sql();
        return $sql->select("select * from tb_users a inner join tb_persons b using(idperson) order by b.desperson");

    }

    public function save()
    {

        $sql = new Sql();

        $results = $sql->select(
            "CALL sp_users_save(:desperson, :deslogin, :despassword,:desemail,:nrphone, :inadmin)",
            array(
                ":desperson" => \utf8_decode($this->getdesperson()),
                ":deslogin" => $this->getdeslogin(),
                ":despassword" => User::getPasswordHash($this->getdespassword()),
                ":desemail" => $this->getdesemail(),
                ":nrphone" => $this->getnrphone(),
                ":inadmin" => $this->getinadmin(),
            )
        );
        $this->setData($results[0]);
    }

    public function get($iduser)
    {

        $sql = new Sql();

        $results = $sql->select("
             SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
            ":iduser" => $iduser,
        ));

        $data = $results[0];

        $this->setData($data);

    }

    public function update()
    {

        $sql = new Sql();

        $results = $sql->select(
            "CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword,:desemail,:nrphone, :inadmin)",
            array(
                ":iduser" => $this->getiduser(),
                ":desperson" => utf8_decode($this->getdesperson()),
                ":deslogin" => $this->getdeslogin(),
                ":despassword" => User::getPasswordHash($this->getdespassword()),
                ":desemail" => $this->getdesemail(),
                ":nrphone" => $this->getnrphone(),
                ":inadmin" => $this->getinadmin(),
            )
        );
        $this->setData($results[0]);

    }
    public function delete()
    {

        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser" => $this->getiduser(),
        ));
    }

    public static function getForgot($email, $inadmin = true)
    {
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

    public static function setError($msg)
    {

        $_SESSION[User::ERROR] = $msg;
    }

    public static function getError()
    {

        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ?
        $_SESSION[User::ERROR] : '';

        User::clearError();
        return $msg;
    }

    public static function clearError()
    {

        $_SESSION[User::ERROR] = null;
    }

    public static function setErrorRegister($msg)
    {

        $_SESSION[User::ERROR_REGISTER] = $msg;
    }

    public static function getErrorRegister()
    {

        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ?
        $_SESSION[User::ERROR_REGISTER] : '';

        User::clearErrorRegister();
        return $msg;
    }

    public static function clearErrorRegister()
    {

        $_SESSION[User::ERROR_REGISTER] = null;
    }

    public static function checkLoginExist($login)
    {

        $sql = new Sql();

        $results = $sql->select("select * from tb_users where deslogin = :deslogin", [
            ':deslogin' => $login,
        ]);

        return (count($results) > 0);

    }

    public static function validForgotDecrypt($result)
    {
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

    public static function setForgotUsed($idrecovery)
    {

        $sql = new Sql();

        $sql->query("
        update tb_userspasswordsrecoveries  set dt_recovery = now() where idrecovery = :idrecovery", array(
            ":idrecovery" => $idrecovery,
        ));
    }

    public function setPassword($password)
    {

        $sql = new Sql();

        $sql->query("update tb_users set despassword = :password where iduser = :iduser", array(
            ":password" => $password,
            ":iduser" => $this->getiduser(),
        ));

    }

    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost' => 12,
        ]);
    }

    public static function clearSuccess()
    {

        $_SESSION[User::SUCCESS] = null;
    }

    public static function setSuccess($msg)
    {

        $_SESSION[User::SUCCESS] = $msg;
    }

    public static function getSuccess()
    {

        $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ?
        $_SESSION[User::SUCCESS] : '';

        User::clearSuccess();
        return $msg;
    }

    public function getOrders()
    {

        $sql = new Sql();

        $results = $sql->select("select * from  tb_orders a
        inner join tb_ordersstatus b using(idstatus)
        inner join tb_carts c using(idcart)
        inner join tb_users d on d.iduser = a.iduser
        inner join tb_addresses e using(idaddress)
        inner join tb_persons f on f.idperson = d.idperson
        where a.iduser = :iduser", [":iduser" => $this->getiduser()]);

        return $results;

    }

    public static function getPage($page = 1, $itemsPerPage = 10)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
        select SQL_CALC_FOUND_ROWS *
        from tb_users a
        inner join tb_persons b using(idperson)
        order by b.desperson
        limit $start, $itemsPerPage;
        ");

        $resultTotal = $sql->select("select FOUND_ROWS() as nrtotal");

        return [
            'data' => $results,
            'total' => (int) $resultTotal[0]["nrtotal"],
            'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage),
        ];
    }

    public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
        select SQL_CALC_FOUND_ROWS *
        from tb_users a
        inner join tb_persons b using(idperson)
        where b.desperson like :search or b.desemail = :search
        or a.deslogin like  :search
        order by b.desperson
        limit $start, $itemsPerPage;
        ", [
            ':search' => '%' . $search . '%',
        ]);

        $resultTotal = $sql->select("select FOUND_ROWS() as nrtotal");

        return [
            'data' => $results,
            'total' => (int) $resultTotal[0]["nrtotal"],
            'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage),
        ];
    }
}
