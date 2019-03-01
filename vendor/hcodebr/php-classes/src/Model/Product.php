<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Product extends Model {

    public static function listAll() {

        $sql = new Sql();
        return $sql->select("select * from tb_products order by desproduct");
    }

    public function save() {

        $sql = new Sql();

        $results = $sql->select(
            "CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)",

            array(
                ":idproduct" => $this->getidproduct(),
                ":desproduct" => $this->getdesproduct(),
                ":vlprice" => $this->getvlprice(),
                ":vlwidth" => $this->getvlwidth(),
                ":vlheight" => $this->getvlheight(),
                ":vllength" => $this->getvllength(),
                ":vlweight" => $this->getvlweight(),
                ":desurl" => $this->getdesurl(),
            )
        );
        $this->setData($results[0]);

    }

    public function get($idproduct) {

        $sql = new Sql();

        $results = $sql->select("
             SELECT * FROM tb_products  WHERE idproduct= :idproduct;", array(
            ":idproduct" => $idproduct,
        ));

        $data = $results[0];

        $this->setData($data);
    }

    public function delete() {

        $sql = new Sql();

        $sql->query(" delete from tb_products  WHERE idproduct= :idproduct; ", array(
            ":idproduct" => $this->getidproduct(),
        ));
    }

    public function checkFoto(){

        if(file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."res"
        .DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".
        DIRECTORY_SEPARATOR.$this->getidproduct()."jpg")){
          
            $url = "/res/site/img/products/". $this->getidproduct().".jpg";
        } else {
            $url =  "/res/site/img/product.jpg";
        }

      return $this->setdesphoto($url);
    }


    public function getValues() {

        $this->checkPhoto();

        $values = parent::getValues();

        return $values;
    }
}
