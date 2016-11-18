<?php
/**
 * Created by PhpStorm.
 * User: nouzo
 * Date: 06.11.2016
 * Time: 11:20
 */

namespace Mini\Controller;

use Mini\Model\User;
use Mini\Model\UserManager;
use Mini\Model\Uzivatel;

/**
 * Class SignController
 * @package Mini\Controller
 *
 * Třída zajišťuje vykreslování správných podstránek, komunikaci mezi modelem UserManager a User, přihlašování
 * a registraci uživatelů
 */
class SignController extends Controller
{
    /**
     * @var UserManager
     */
    private $um;

    /**
     * SignController constructor.
     *
     * vytvoří instanci UserManager pro práci s uživateli
     */
    public function __construct()
    {
        parent::__construct();
        $this->um = new UserManager();
    }


    /**
     * Funkce zajišťuje zobrazení přihlašovací stránky
     */
    public function index() {
        echo $this->twig->render('/sign/index.twig');
    }

    /**
     * Funkce se pokusí přihlásit uživatele podle zadaných údajů,
     * pokud se údaje shodují, vytvoří se globální proměnná 'uzivatel' a dojde k přesměrování do administrace
     */
    public function prihlasovani() {

        if(!isset($_POST['uzivatel'])) {
            $this->redirect($this->makeURL('sign:index'),'Přihlášení se nezdařilo');
            die;
        }

        $udaje = $this->um->autorizace($_POST['uzivatel']);

        if($udaje == null) {
            $this->redirect($this->makeURL('sign:index'),'Špatné jméno nebo heslo','danger');
            die;
        }

        if($udaje['role_id'] == 4) {
            $this->redirect($this->makeURL('sign:index'),'Byl jste zablokován','danger');
            die;
        }

        $_SESSION['uzivatel'] = new Uzivatel($udaje);
        $this->redirect($this->makeURL('administrace:index'));

    }

    /**
     * Funkce zobrazí stránku s formulářem pro registraci
     */
    public function registrace() {
        echo $this->twig->render('/sign/registrace.twig');
    }

    /**
     * Funkce zajišťuje registraci uživatele do systému podle předaných údajů
     */
    public function registrovani() {

        if(!isset($_POST['uzivatel'])) {
            $this->redirect($this->makeURL('sign:registrace'),'Registrace se nezdařila');
            die;
        }

        $um = new UserManager();

        if(!$um->add($_POST['uzivatel'])) {
            $this->redirect($this->makeURL('sign:registrace'),'Uživatel již existuje','danger');
            die;
        }

        $this->redirect($this->makeURL('home:index'),'Registrace proběhla úspěšně, nyní se můžete přihlásit.','success');

    }

    /**
     *  Funkce zajišťuje odhlášení uživatele z aplikace
     */
    public function out() {

        unset($_SESSION["uzivatel"]);
        $this->redirect($this->makeURL('home:index'),'Byl jste odhlášen');

    }
}