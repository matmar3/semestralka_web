<?php
/**
 * Created by PhpStorm.
 * User: nouzo
 * Date: 08.11.2016
 * Time: 10:22
 */

namespace Mini\Model;


/**
 * Class Uzivatel
 * @package Mini\Model
 *
 * Třída slouží k uchování údajů o přihlášeném uživateli
 */
class Uzivatel
{
    /**
     * @var mixed - přezdívka uživatele
     */
    private $nick;
    /**
     * @var mixed - jméno uživatele
     */
    private $jmeno;
    /**
     * @var mixed - příjmení uživatele
     */
    private $prijmeni;
    /**
     * @var mixed - email uživatele
     */
    private $email;
    /**
     * @var mixed - uživatelova oprávnění
     */
    private $role;

    /**
     * Uzivatel constructor.
     * @param array $udaje
     *
     * Uloží se předané údaje do atributů
     */
    public function __construct(array $udaje)
    {
        $this->nick = $udaje['nick'];
        $this->jmeno = $udaje['jmeno'];
        $this->prijmeni = $udaje['prijmeni'];
        $this->email = $udaje['email'];
        $this->role = $udaje['role_id'];
    }

    /**
     * Vrací přezdívku uživatele
     *
     * @return mixed - přezdívka uživatele
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * Vrací jméno uživatele
     *
     * @return mixed - jméno uživatele
     */
    public function getJmeno()
    {
        return $this->jmeno;
    }

    /**
     * Vrací příjmení uživatele
     *
     * @return mixed - příjmení uživatele
     */
    public function getPrijmeni()
    {
        return $this->prijmeni;
    }

    /**
     * Vrací email uživatele
     *
     * @return mixed - email uživatele
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Vrací oprávnění uživatele
     *
     * @return mixed - oprávnění uživatele
     */
    public function getRole()
    {
        return $this->role;
    }





}