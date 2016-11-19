<?php
/**
 * Created by PhpStorm.
 * User: nouzo
 * Date: 06.11.2016
 * Time: 12:04
 */

namespace Mini\Model;


use Mini\Core\Model;

/**
 * Class UserManager
 * @package Mini\Model
 *
 * Model zajišťuje komunikaci s databází hlavně s tabulkou uzivatele a role.
 */
class UserManager extends Model
{
    /**
     * Funkce se stará o kontrolu přihlašovacích údajů, vrací informace o uživateli, pokud přihlášneí proběhlo
     * úspěšně, v opačném případě vrací null
     *
     * @param array $pristupy - přihlašovací údaje do administrace
     * @return mixed - informace o přihlášeném uživateli
     */
    public function autorizace(array $pristupy) {
        $nick = htmlspecialchars($pristupy['nick']);
        $heslo = htmlspecialchars($pristupy['heslo']);

        $query = $this->db->prepare('SELECT * FROM uzivatele WHERE nick=:nick AND heslo=:heslo');
        $query->bindParam(':nick', $nick, \PDO::PARAM_STR, 45);
        $query->bindParam(':heslo', $heslo, \PDO::PARAM_STR, 20);
        $query->execute();

        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Funkce slouží k přidávání uživatelů do tabulky uzivatele
     *
     * @param array $udaje - údaje o novém uživateli
     * @return bool - true = užiatel přidán, false = nastala chyba
     */
    public function add(array $udaje) {
        try {
            $nick = htmlspecialchars($udaje['nick']);
            $jmeno = htmlspecialchars($udaje['jmeno']);
            $prijmeni = htmlspecialchars($udaje['prijmeni']);
            $email = htmlspecialchars($udaje['email']);
            $heslo = htmlspecialchars($udaje['heslo']);

            $query = $this->db->prepare('INSERT INTO uzivatele (nick,jmeno,prijmeni,email,heslo) VALUES (:nick,:jmeno,:prijmeni,:email,:heslo)');
            $query->bindParam(':nick',$nick,\PDO::PARAM_STR, 45);
            $query->bindParam(':jmeno',$jmeno,\PDO::PARAM_STR, 45);
            $query->bindParam(':prijmeni',$prijmeni,\PDO::PARAM_STR, 45);
            $query->bindParam(':email',$email,\PDO::PARAM_STR, 90);
            $query->bindParam(':heslo',$heslo,\PDO::PARAM_STR, 90);
            $query->execute();
        } catch (\PDOException $e) {
            return false;
        }

        return true;
    }

    /**
     * Funkce vrací všechny registrované uživatele v poli
     *
     * @return array - všichni uživatelé
     */
    public function getUzivatele() {
        $dotaz = $this->db->prepare('
            SELECT u.*,r.nazev 
            FROM uzivatele u, role r 
            WHERE r.id = u.role_id
            ORDER BY u.nick ASC
        ');
        $dotaz->execute();

        return $dotaz->fetchAll();
    }

    /**
     * Funkce vrací informace o uživateli s předaným nickem
     *
     * @param $nick - pžezdívka uživatele, kterou používá k přihlášení
     * @return mixed - informace o uživateli
     */
    public function getKonkretnihoUzivatele($nick) {
        $nickname = htmlspecialchars($nick);

        $dotaz = $this->db->prepare('
            SELECT u.*,r.nazev 
            FROM uzivatele u, role r 
            WHERE r.id = u.role_id AND u.nick = :nick
        ');
        $dotaz->bindParam('nick',$nickname,\PDO::PARAM_STR);
        $dotaz->execute();

        return $dotaz->fetch();
    }

    /**
     * Funkce vrací veškerá dostupná oprávnění
     *
     * @return array - všechna dostupná oprávnění
     */
    public function getRole() {
        $dotaz = $this->db->prepare('
            SELECT * 
            FROM role
            ORDER BY nazev ASC
        ');
        $dotaz->execute();

        return $dotaz->fetchAll();
    }

    /**
     * Funkce změní heslo konkrétnímu uživateli
     *
     * @param array $uzivatel - pole údajů - přezdívka, nové heslo
     */
    public function changePassword(array $uzivatel) {
        $nick = htmlspecialchars($uzivatel['nick']);
        $heslo = htmlspecialchars($uzivatel['heslo']);

        $dotaz = $this->db->prepare('
            UPDATE uzivatele SET heslo=:heslo WHERE nick=:nick LIMIT 1
        ');
        $dotaz->bindParam('heslo',$heslo,\PDO::PARAM_STR);
        $dotaz->bindParam('nick',$nick,\PDO::PARAM_STR);
        $dotaz->execute();
    }

    /**
     * Funkce změní všechny údaje o uživateli až na heslo
     *
     * @param array $uzivatel - pole nových údajů o uživateli
     */
    public function zmenitUdaje(array $uzivatel) {
        $jmeno = htmlspecialchars($uzivatel['jmeno']);
        $prijmeni = htmlspecialchars($uzivatel['prijmeni']);
        $email = htmlspecialchars($uzivatel['email']);
        $role = htmlspecialchars($uzivatel['role']);
        $nick = htmlspecialchars($uzivatel['nick']);

        $dotaz = $this->db->prepare('
            UPDATE uzivatele 
            SET jmeno=:jmeno, prijmeni=:prijmeni, email=:email, role_id=:role
            WHERE nick=:nick LIMIT 1
        ');
        $dotaz->bindParam('jmeno',$jmeno,\PDO::PARAM_STR);
        $dotaz->bindParam('prijmeni',$prijmeni,\PDO::PARAM_STR);
        $dotaz->bindParam('email',$email,\PDO::PARAM_STR);
        $dotaz->bindParam('role',$role,\PDO::PARAM_INT);
        $dotaz->bindParam('nick',$nick,\PDO::PARAM_STR);
        $dotaz->execute();
    }

    /**
     * Funkce smaže uživatele s předaným nickem
     *
     * @param $nick - přezdívka uživatele
     */
    public function smazatUzivatele($nick) {
        $nickname = htmlspecialchars($nick);

        $dotaz = $this->db->prepare('DELETE FROM uzivatele WHERE nick=:nick LIMIT 1');
        $dotaz->bindParam('nick',$nickname,\PDO::PARAM_STR);
        $dotaz->execute();
    }

}