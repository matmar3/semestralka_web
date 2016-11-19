<?php
/**
 * Created by PhpStorm.
 * User: nouzo
 * Date: 12.11.2016
 * Time: 15:00
 */

namespace Mini\Model;


use Mini\Core\Model;

/**
 * Class Recenze
 * @package Mini\Model
 *
 * Model zajišťuje komunikaci s databází, převážně s tabulkou recenze a příspěvky.
 */
class Recenze extends Model
{
    /**
     * Funkce vrací pole příspěvků, které je třeba ohodnotit
     *
     * @return array - pole příspěvků, které je třeba ohodnotit
     */
    public function getPrispevky() {
        $dotaz = $this->db->prepare('
            SELECT p.*,COUNT(r.uzivatele_nick) as recenzentu,AVG(r.mezisoucet) as celkem,COUNT(r.mezisoucet) as kontrola FROM prispevky p
            LEFT JOIN recenze r ON p.id = r.prispevky_id
            WHERE p.stav = ?');
        $dotaz->execute(["recenzován"]);

        return $dotaz->fetchAll();
    }

    /**
     * Funkce vrací pole volných recenzentů, kteří nemají přiřazený příspěvek s předaným id k hodnocení
     *
     * @param $id - id příspěvku, který má být hodnocen
     * @return array - pole volných recenzentů
     */
    public function getVolneRecenzenty($id) {
        $identifikator = htmlspecialchars($id);

        $dotaz = $this->db->prepare('
              SELECT * FROM uzivatele u
              WHERE u.role_id = 2 
              AND u.nick NOT IN (SELECT uzivatele_nick FROM recenze WHERE prispevky_id = :id)');
        $dotaz->bindParam('id',$identifikator,\PDO::PARAM_INT);
        $dotaz->execute();

        return $dotaz->fetchAll();
    }

    /**
     * Funkce vrací pole recenzí, které odpovídají příspěvku s předaným id
     *
     * @param $id - id příspěvku
     * @return array - pole recenzí, které patří k příspěvku s předaným id
     */
    public function getRecenzePodlePrispevku($id) {
        $identifikator = htmlspecialchars($id);

        $dotaz = $this->db->prepare('SELECT * FROM recenze WHERE prispevky_id = :id');
        $dotaz->bindParam('id',$identifikator,\PDO::PARAM_INT);
        $dotaz->execute();

        return $dotaz->fetchAll();
    }

    /**
     * Funkce přidělí recenze (recenzenty) k příspěvku s předaným id
     *
     * @param array $recenzenti - pole přezdívek recenzentů, kteří mají ohodnotit příspěvek s předaným id
     * @param $prispevek_id - id příspěvku
     */
    public function pridelitRecenze(array $recenzenti, $prispevek_id) {
        $id_prispevku = htmlspecialchars($prispevek_id);

        foreach ($recenzenti as $user) {
            $nick = htmlspecialchars($user);

            $dotaz = $this->db->prepare('INSERT INTO recenze (uzivatele_nick,prispevky_id) VALUES (:nick,:prispevky_id)');
            $dotaz->bindParam('nick',$nick,\PDO::PARAM_STR);
            $dotaz->bindParam('prispevky_id',$id_prispevku,\PDO::PARAM_INT);
            $dotaz->execute();
        }
    }

    /**
     * Funkce odebere od příspěvku recenzi (recenzenta)
     *
     * @param $id - id recenze
     */
    public function odebratRecenzi($id) {
        $identifikator = htmlspecialchars($id);

        $dotaz = $this->db->prepare('DELETE FROM recenze WHERE id = :id LIMIT 1');
        $dotaz->bindParam('id',$identifikator,\PDO::PARAM_INT);
        $dotaz->execute();
    }

    /**
     * Funkce vrací pole recenzí, které má momentálně přihlášený recenzent ohodnotit
     *
     * @param $nick - přezdívka recenzenta
     * @return array - pole recenzí
     */
    public function getMojeRecenze($nick) {
        $nickname = htmlspecialchars($nick);

        $dotaz = $this->db->prepare('
            SELECT p.nazev,p.stav,r.mezisoucet,r.id 
            FROM recenze r, prispevky p
            WHERE r.uzivatele_nick = :nick 
            AND p.id = r.prispevky_id
        ');
        $dotaz->bindParam('nick',$nickname,\PDO::PARAM_STR);
        $dotaz->execute();

        return $dotaz->fetchAll();
    }

    /**
     * Funkce vrátí konkrétní recenzi podle předaného id recenze
     *
     * @param $id - id recenze
     * @return mixed - detailní informace o recenzi
     */
    public function getKonkretniRecenzi($id) {
        $identifikator = htmlspecialchars($id);

        $dotaz = $this->db->prepare('SELECT r.*,p.nazev,p.stav,p.id as pid FROM recenze r, prispevky p WHERE r.id=:id AND p.id = r.prispevky_id');
        $dotaz->bindParam('id',$identifikator,\PDO::PARAM_INT);
        $dotaz->execute();

        return $dotaz->fetch();
    }

    /**
     * Funkce uloží recenzi, která odpovídá příspěvku s předaným id
     *
     * @param $id - id příspěvku
     * @param $recenze - hodnocení
     */
    public function ulozitRecenzi($id, $recenze) {
        $identifikator = htmlspecialchars($id);
        $vzhled = htmlspecialchars($recenze['vzhled']);
        $srozumitelnost = htmlspecialchars($recenze['srozumitelnost']);
        $pravopis = htmlspecialchars($recenze['pravopis']);
        $rozsah = htmlspecialchars($recenze['rozsah']);
        $pocetKriterii = 4;

        $dotaz = $this->db->prepare('
            UPDATE recenze 
            SET vzhled=:vzhled, srozumitelnost=:srozumitelnost, pravopis=:pravopis, rozsah=:rozsah,
            mezisoucet=(SELECT (vzhled + srozumitelnost + pravopis + rozsah) / :pocet WHERE id = :id),
            cas_hodnoceni=NOW()
            WHERE id = :id2
        ');
        $dotaz->bindParam('vzhled',$vzhled,\PDO::PARAM_STR);
        $dotaz->bindParam('srozumitelnost',$srozumitelnost,\PDO::PARAM_STR);
        $dotaz->bindParam('pravopis',$pravopis,\PDO::PARAM_STR);
        $dotaz->bindParam('rozsah',$rozsah,\PDO::PARAM_STR);
        $dotaz->bindParam('pocet',$pocetKriterii,\PDO::PARAM_INT);
        $dotaz->bindParam('id',$identifikator,\PDO::PARAM_INT);
        $dotaz->bindParam('id2',$identifikator,\PDO::PARAM_INT);
        $dotaz->execute();

    }
}