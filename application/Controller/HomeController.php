<?php

/**
 * Class HomeController
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */

namespace Mini\Controller;

use Mini\Model\Prispevky;

/**
 * Class HomeController
 * @package Mini\Controller
 *
 * Třída zajišťuje vykreslování správných podstránek a komunikaci s modelem Prispevky.
 */
class HomeController extends Controller
{
    /**
     * @var Prispevky
     */
    private $prispevky;

    /**
     * RECENZOVÁNO = stav příspěvku, odpovídá stavu (enum) v tabulce příspěvky
     */
    const
        RECENZOVANO = 'recenzován';

    /**
     * HomeController constructor.
     *
     * vytvoří instanci modelu Prispevky pro práci s modelem
     */
    public function __construct()
    {
        parent::__construct();
        $this->prispevky = new Prispevky();
    }


    /**
     * Funkce zajišťje vykreslení úvodní stránnky
     *
     * twig params:
     *      prispevky - všechny viditelné příspěvky
     */
    public function index()
    {
        echo $this->twig->render('/home/index.twig',[
            'prispevky' => $this->prispevky->getPrispevky()
        ]);
    }

    /**
     * Funkce zajišťuje vykreslení stránky s příspěvkem podle předaného id příspěvku
     *
     * @param $id - id příspěvku
     *
     * twig params:
     *      prispevek - všechny údaje o příspěvku s předaným id
     */
    public function detail($id)
    {
        if(!isset($id))
            $this->redirect($this->makeURL('home:index'),'Příspěvek nenalezen');

        $prispevek = $this->prispevky->getKonkretniPrispevek($id);

        if(!$prispevek || ($prispevek->stav == self::RECENZOVANO && !isset($_SESSION['uzivatel'])))
            $this->redirect($this->makeURL('home:index'),'Příspěvek nenalezen');

        echo $this->twig->render('/home/detail.twig',[
            'prispevek' => $prispevek]
        );
    }

    /**
     *  Funkce zajišťuje vykreslení stránky
     */
    public function kontakt(){
        echo $this->twig->render('/home/kontakt.twig');
    }

    /**
     * Funkce zajišťuje vykreslení stránky
     */
    public function terminy(){
        echo $this->twig->render('/home/terminy.twig');
    }

    /**
     * Funkce zajišťuje vykreslení stránky
     */
    public function kriteria(){
        echo $this->twig->render('/home/kriteria.twig');
    }
}
