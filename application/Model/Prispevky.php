<?php
/**
 * Created by PhpStorm.
 * User: nouzo
 * Date: 12.11.2016
 * Time: 9:59
 */

namespace Mini\Model;


use HTMLPurifier_Config;
use Mini\Core\Model;

/**
 * Class Prispevky
 * @package Mini\Model
 *
 * Model slouží ke komunikaci převážně s tabulkou příspěvky
 */
class Prispevky extends Model
{
    /**
     * @var \HTMLPurifier - slouží k escapování řetězců
     */
    private $purifier;

    /**
     * Prispevky constructor.
     *
     * vytvoří instanci třídy HTMLPurifier
     */
    public function __construct()
    {
        parent::__construct();
        $config = HTMLPurifier_Config::createDefault();
        $this->purifier = new \HTMLPurifier($config);
    }


    /**
     * Funkce vytvoří ošetří vstupy a vytvoří v tabulce příspěvky nový příspěvek, podle předaných parametrů
     *
     * @param array $prispevek - detaily příspěvku
     */
    public function pridatPrispevek(array $prispevek) {
        $nazev = htmlspecialchars($prispevek['nazev']);
        $obsah = $this->purifier->purify($prispevek['obsah']);
        $priloha = htmlspecialchars($prispevek['priloha']);
        $nick = htmlspecialchars($prispevek['nick']);

        $dotaz = $this->db->prepare('
                    INSERT INTO prispevky (nazev,obsah,priloha_url,uzivatele_nick)
                    VALUES (:nazev,:obsah,:priloha,:nick)');
        $dotaz->bindParam('nazev',$nazev,\PDO::PARAM_STR);
        $dotaz->bindParam('obsah',$obsah,\PDO::PARAM_STR);
        $dotaz->bindParam('priloha',$priloha,\PDO::PARAM_STR);
        $dotaz->bindParam('nick',$nick,\PDO::PARAM_STR);
        $dotaz->execute();
    }

    /**
     * Funkce vytvoří ošetří vstupy a upraví v tabulce příspěvky příspěvek, podle předaných parametrů
     *
     * @param array $prispevek - detaily příspěvku
     */
    public function upravitPrispevek(array $prispevek) {
        $nazev = htmlspecialchars($prispevek['nazev']);
        $obsah = $this->purifier->purify($prispevek['obsah']);
        $priloha = htmlspecialchars($prispevek['priloha']);
        $id = htmlspecialchars($prispevek['id']);

        $dotaz = $this->db->prepare('
                    UPDATE prispevky SET nazev=:nazev, obsah=:obsah, priloha_url=:priloha
                    WHERE id=:id');
        $dotaz->bindParam('nazev',$nazev,\PDO::PARAM_STR);
        $dotaz->bindParam('obsah',$obsah,\PDO::PARAM_STR);
        $dotaz->bindParam('priloha',$priloha,\PDO::PARAM_STR);
        $dotaz->bindParam('id',$id,\PDO::PARAM_INT);
        $dotaz->execute();
    }

    /**
     * Funkce ušetří vstup a po provedení dotazu vrátí všechny příspěvky momntálně přihlášeného autora
     *
     * @param $nick - nickname autora
     * @return array - všechny příspěvky momntálně přihlášeného autora
     */
    public function getMojePrispevky($nick) {
        $prezdivka = htmlspecialchars($nick);

        $dotaz = $this->db->prepare('SELECT * FROM prispevky WHERE uzivatele_nick LIKE :nick');
        $dotaz->bindParam('nick',$prezdivka,\PDO::PARAM_STR);
        $dotaz->execute();

        return $dotaz->fetchAll();
    }

    /**
     * Funkce vrací pole všech přijatých příspěvků
     *
     * @return array - pole všech přijatých příspěvků
     */
    public function getPrispevky() {
        $dotaz = $this->db->prepare('
          SELECT p.*, u.jmeno, u.prijmeni
          FROM prispevky p, uzivatele u 
          WHERE stav LIKE \'přijat\' AND p.uzivatele_nick = u.nick
          ORDER BY p.cas_vytvoreni DESC
        ');
        $dotaz->execute();

        return $dotaz->fetchAll();
    }

    /**
     * Funkce vrátí příspěvek, který odpovídá předanému id
     *
     * @param $id - id příspěvku
     * @return mixed - detail příspěvku
     */
    public function getKonkretniPrispevek($id) {
        $identifikator = htmlspecialchars($id);

        $dotaz = $this->db->prepare('
            SELECT p.*, u.jmeno, u.prijmeni
            FROM prispevky p, uzivatele u 
            WHERE id = :id AND p.uzivatele_nick = u.nick
        ');
        $dotaz->bindParam('id',$identifikator,\PDO::PARAM_INT);
        $dotaz->execute();

        return $dotaz->fetch();
    }

    /**
     * Funkce smaže příspěvek na základě předaného id
     *
     * @param $id
     */
    public function smazatPrispevek($id) {
        $identifikator = htmlspecialchars($id);

        $cesta = $this->db->prepare('SELECT priloha_url FROM prispevky WHERE id = :id');
        $cesta->bindParam('id',$identifikator,\PDO::PARAM_INT);
        $cesta->execute();
        $url = $cesta->fetch();

        if (file_exists($url->priloha_url)) {
            unlink($url->priloha_url);
        }

        $dotaz = $this->db->prepare('DELETE FROM prispevky WHERE id = :id LIMIT 1');
        $dotaz->bindParam('id',$identifikator,\PDO::PARAM_INT);
        $dotaz->execute();
    }

    /**
     * Funkce změní u příspěvku stav na přijatý/zamítnutý a současně vypočte průměrné hodnocení ze všech odpovídajících recenzí
     *
     * @param $id - id příspěvku
     * @param $stav - nový stav
     */
    public function zmenitStav($id, $stav) {
        $novyStav = htmlspecialchars($stav);
        $identifikator = htmlspecialchars($id);

        $dotaz = $this->db->prepare('
                UPDATE prispevky p
                SET p.stav=:stav, p.hodnoceni = (SELECT AVG(r.mezisoucet) FROM recenze r WHERE r.prispevky_id = p.id)
                WHERE p.id=:id');
        $dotaz->bindParam('stav',$novyStav,\PDO::PARAM_STR);
        $dotaz->bindParam('id',$identifikator,\PDO::PARAM_INT);
        $dotaz->execute();
    }

}