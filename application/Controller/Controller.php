<?php
/**
 * Created by PhpStorm.
 * User: nouzo
 * Date: 05.11.2016
 * Time: 8:47
 */

namespace Mini\Controller;


/**
 * Class Controller
 * @package Mini\Controller
 *
 * Třída slouží jako rodič pro jednotlivé kontrolery obsahující společné vlastnosti a atributy.
 */
class Controller
{
    /** @var null Twig - šablonovací systém*/
    protected $twig = null;

    /**
     * Controller constructor.
     *
     * provede základní nastavení šablonovacího systému
     */
    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(APP . 'view');
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addGlobal('session', $_SESSION);
        $this->twig->addExtension(new \Twig_Extensions_Extension_Text());
        $makeUrl = new \Twig_SimpleFunction('makeURL', [$this, 'makeURL']);
        $this->twig->addFunction($makeUrl);

        /**
         * globální proměná, která slouží k předávání zpráv uživateli
         */
        $_SESSION['flash'] = null;
    }

    /**
     * Funkce slouží k vytváření URL podle nastaveného parametru
     *
     * @param $page - název stránky ve formátu kontroler:metoda:parametr
     * @return string - vytvořené URL
     */
    public function makeURL($page) {
        $page = explode(':',htmlspecialchars($page));
        if(URL_TYPE) {
            $param = isset($page[2])? '/' . $page[2] : '';
            return URL . $page[0] . '/' . $page[1] . $param;
        } else {
            $param = isset($page[2])? '&param=' . $page[2] : '';
            return URL . 'index.php?page=' . $page[0] . '&action=' . $page[1] . $param;
        }
    }

    /**
     * Funkce slouží k přesměrování na požadovanou stránku s možností nastavit zprávu, která se po
     * přesměrování zobrazí uživateli
     *
     * @param $url - vytvořené URL, ne které se stránka přesměruje
     * @param null $message - zpráva pro uživatele
     * @param string $type - obarvení zprávy podle alertu z Bootstrap 3
     */
    public function redirect($url, $message = null, $type = 'warning') {
        $_SESSION['flash']['message'] = $message;
        $_SESSION['flash']['type'] = $type;
        header('location: ' . $url);
    }
}