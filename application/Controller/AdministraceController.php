<?php
/**
 * Created by PhpStorm.
 * User: nouzo
 * Date: 06.11.2016
 * Time: 13:33
 */

namespace Mini\Controller;


use Mini\Model\Prispevky;
use Mini\Model\Recenze;
use Mini\Model\UserManager;
use Mini\Model\Uzivatel;

/**
 * Class AdministraceController
 * @package Mini\Controller
 *
 * Třída zajišťuje vykreslování správných podstránek, komunikaci mezi modelem Recenze a UserManager, kontrolu oprávnění
 */
class AdministraceController extends Controller
{
    /**
     * @var Uzivatel - přihlášený uživatel
     */
    private $uzivatel;

    /**
     * @var Prispevky - model Prispevky
     */
    private $prispevky;

    /**
     * @var Recenze - model Recenze
     */
    private $recenze;

    /**
     * @var UserManager - model UserManager
     */
    private $um;

    /**
     * AUTOR = oprávnění autor, odpovídá id v tabulce role
     */
    /**
     * RECENZENT = oprávnění recenzent, odpovídá id v tabulce role
     */
    /**
     * ADMIN = oprávnění administrátora, odpovídá id v tabulce role
     */
    /**
     * RECENZOVÁNO = stav příspěvku, odpovídá stavu (enum) v tabulce příspěvky
     */
    /**
     * MIN_POCET_RECENZENTU = minimální počet recenzentů pro přijetí/odmítnutí příspěvku
     */
    const
        AUTOR = 3,
        RECENZENT = 2,
        ADMIN = 1,
        RECENZOVANO = 'recenzován',
        MIN_POCET_RECENZENTU = 3;

    /**
     * AdministraceController constructor.
     *
     * zkontroluje zda je uživatel přihlášen, poté vytvoří instance k používaným modelům
     */
    public function __construct()
    {
        parent::__construct();
        $this->checkLogIn();
        $this->uzivatel = $_SESSION["uzivatel"];
        $this->prispevky = new Prispevky();
        $this->recenze = new Recenze();
        $this->um = new UserManager();
    }

    /**
     * Funkce kontroluje jestli je uživatel přihlášen podle globální proměnné 'uzivatel', která se nastavuje při
     * úspěšném přihlášení
     */
    private function checkLogIn() {
        if(!isset($_SESSION["uzivatel"]))
            $this->redirect($this->makeURL('sign:index'),'Nejste přihlášen','danger');
    }

    /**
     * Funkce kontroluje zda má uživatel oprávnění shodné s předaným parametrem
     * @param $role - předaná úroveň oprávnění
     */
    private function authorization($role) {
        if($this->uzivatel->getRole() != $role)
            $this->redirect($this->makeURL('administrace:index'),'Přístup odepřen','danger');
    }

    /**
     * Funkce zajistí vykreslení úvodní strany administrace
     *
     * twig params:
     *      admin = oprávnění admin
     *      recenzent = oprávnění recenzent
     *      autor = oprávnění autor
     */
    public function index() {
        echo $this->twig->render('/administrace/index.twig',[
            'admin' => self::ADMIN,
            'recenzent' => self::RECENZENT,
            'autor' => self::AUTOR
        ]);
    }

    /*----------------------------------------------------------------------------------------------
    ----------------------------------- AUTOR - PŘÍSPĚVKY ------------------------------------------
    ----------------------------------------------------------------------------------------------*/

    /**
     * Funkce zajišťuje vykreslení autorových příspěvků
     *
     * twig params:
     *      prispevky = všechny autorovy příspěvky
     */
    public function prispevky() {
        $this->authorization(self::AUTOR);
        echo $this->twig->render('/administrace/prispevky.twig',[
            'prispevky' => $this->prispevky->getMojePrispevky($this->uzivatel->getNick())
        ]);
    }

    /**
     * Funkce smaže příspěvek, kterému odpovídá id v tabulce prispevky
     *
     * @param $id - id příspěvku
     */
    public function smazatPrispevek($id) {
        $this->authorization(self::AUTOR);

        if(!isset($id))
            $this->redirect($this->makeURL('administrace:prispevky'),'Chyba v požadavku na smazání');

        $this->prispevky->smazatPrispevek($id);
        $this->redirect($this->makeURL('administrace:prispevky'),'Příspěvek byl smazán');
    }

    /**
     * Funkce vykreslí stránku s formulářen pro vytvoření nového příspěvku
     */
    public function novyPrispevek() {
        $this->authorization(self::AUTOR);
        echo $this->twig->render('/administrace/novy.twig');
    }

    /**
     * Funkce vytvoří příspěvek na základě předaných hodnot z formuláře ( $_POST['prispevek'] )     *
     */
    public function pridatPrispevek() {
        $this->authorization(self::AUTOR);
        if(isset($_POST['prispevek'])) {
            // povinne udaje
            $prispevek = $_POST['prispevek'];
            $prispevek['nick'] = $this->uzivatel->getNick();
            // nepovinne udaje
            $file = $_FILES["priloha"];
            // kontrola jestli je soubor nastaven
            if($file['size'] > 0) {
                $target_dir = ROOT . "public" . DIRECTORY_SEPARATOR . "prilohy" . DIRECTORY_SEPARATOR;
                $target_file = $target_dir . basename($file["name"]);
                $uploadOk = 1;
                $fileType = pathinfo($target_file,PATHINFO_EXTENSION);
                // Kontrola jestli soubor už existuje
                if (file_exists($target_file)) {
                    $this->redirect($this->makeURL('administrace:novyprispevek'),'Soubor již existuje');
                    $uploadOk = 0;
                }
                // KOntrola velikosti
                if ($file["size"] > 5000000) {
                    $this->redirect($this->makeURL('administrace:novyprispevek'),'Soubor je příliš velký');
                    $uploadOk = 0;
                }
                // Kontrola formátu
                if($fileType != "pdf") {
                    $this->redirect($this->makeURL('administrace:novyprispevek'),'Tento formát není podporován');
                    $uploadOk = 0;
                }
                // Kontrola kontrolní proměnné
                if ($uploadOk == 0) {
                    $this->redirect($this->makeURL('administrace:novyprispevek'),'Soubor nebyl nahrán');

                } else { // pokud je vše v pořádku, pokusí se nahrát soubor
                    if (move_uploaded_file($file["tmp_name"], $target_file)) {
                        $prispevek['priloha'] = $target_file;
                        $this->prispevky->pridatPrispevek($prispevek);
                        $this->redirect($this->makeURL('administrace:prispevky'),'Příspěvek byl vytvořen','success');
                    } else {
                        $this->redirect($this->makeURL('administrace:novyprispevek'),'Chyba při pokusu o nahrátí souboru');
                    }
                }
            } else { // soubor nebyl nastaven
                $prispevek['priloha'] = '';
                $this->prispevky->pridatPrispevek($prispevek);
                $this->redirect($this->makeURL('administrace:prispevky'),'Příspěvek byl vytvořen','success');
            }
        } else {
            $this->redirect($this->makeURL('administrace:novyprispevek'),'Došlo k chybě při přenosu dat');
        }
    }

    /**
     * Funkce zajistí vykreslení stránky s formulářem pro upravení příspěvku, kterému odpovídá id z tabulky prispevky
     *
     * @param $id - id příspěvku
     *
     * twig params:
     *      prispevek = údaje o uloženém příspěvku
     */
    public function upravitPrispevek($id) {
        $this->authorization(self::AUTOR);

        if(!isset($id))
            $this->redirect($this->makeURL('administrace:prispevky'),'Příspěvek nelze upravit');

        $prispevek = $this->prispevky->getKonkretniPrispevek($id);

        if(!$prispevek || $prispevek->stav != self::RECENZOVANO)
            $this->redirect($this->makeURL('administrace:prispevky'),'Příspěvek nelze upravit');

        echo $this->twig->render('/administrace/upravitprispevek.twig',[
            'prispevek' => $prispevek
        ]);
    }

    /**
     * Funkce provede úpravy v příspěvku podle předaných hodnot ( $_POST['prispevek'] )
     */
    public function zmenitPrispevek() {
        $this->authorization(self::AUTOR);
        if(isset($_POST['prispevek'])) {
            // povinne udaje
            $prispevek = $_POST['prispevek'];
            $prispevek['nick'] = $this->uzivatel->getNick();
            // nepovinne udaje
            $file = $_FILES["priloha"];
            $last_file = $prispevek['last_file'];
            // kontrola jestli je soubor nastaven
            if($file['size'] > 0) {
                $target_dir = ROOT . "public" . DIRECTORY_SEPARATOR . "prilohy" . DIRECTORY_SEPARATOR;
                $target_file = $target_dir . basename($file["name"]);
                $uploadOk = 1;
                $fileType = pathinfo($target_file,PATHINFO_EXTENSION);
                // smazání předešlého souboru
                if (file_exists($last_file)) {
                    unlink($last_file);
                }
                // Kontrola jestli soubor už existuje
                if (file_exists($target_file)) {
                    $this->redirect($this->makeURL('administrace:prispevky'),'Soubor již existuje');
                    $uploadOk = 0;
                }
                // KOntrola velikosti
                if ($file["size"] > 5000000) {
                    $this->redirect($this->makeURL('administrace:prispevky'),'Soubor je příliš velký');
                    $uploadOk = 0;
                }
                // Kontrola formátu
                if($fileType != "pdf") {
                    $this->redirect($this->makeURL('administrace:prispevky'),'Tento formát není podporován');
                    $uploadOk = 0;
                }
                // Kontrola kontrolní proměnné
                if ($uploadOk == 0) {
                    $this->redirect($this->makeURL('administrace:prispevky'),'Soubor nebyl nahrán');

                } else { // pokud je vše v pořádku, pokusí se nahrát soubor
                    if (move_uploaded_file($file["tmp_name"], $target_file)) {
                        $prispevek['priloha'] = $target_file;
                        $this->prispevky->upravitPrispevek($prispevek);
                        $this->redirect($this->makeURL('administrace:prispevky'),'Příspěvek byl upraven','success');
                    } else {
                        $this->redirect($this->makeURL('administrace:prispevky'),'Chyba při pokusu o nahrátí souboru');
                    }
                }
            } else { // soubor nebyl nastaven
                $prispevek['priloha'] = $last_file;
                $this->prispevky->upravitPrispevek($prispevek);
                $this->redirect($this->makeURL('administrace:prispevky'),'Příspěvek byl upraven','success');
            }
        } else {
            $this->redirect($this->makeURL('administrace:novyprispevek'),'Došlo k chybě při přenosu dat');
        }
    }

    /*----------------------------------------------------------------------------------------------
    ----------------------------------- RECENZE ----------------------------------------------------
    ----------------------------------------------------------------------------------------------*/

    /**
     * Funkce zobrazí stránku obsahující všechny recenze, které jsou přihlášenému recenzentovi přidělené
     *
     * twig params:
     *      recenze - všechny recenze, které jsou přihlášenému recenzentovi přidělené
     */
    public function kRecenzi() {
        $this->authorization(self::RECENZENT);
        echo $this->twig->render('/administrace/krecenzi.twig',[
            'recenze' => $this->recenze->getMojeRecenze($this->uzivatel->getNick())
        ]);
    }

    /**
     * Funkce otevře stránku s formulářem pro ohodnocení příspěvku
     *
     * @param $id - id odpovídající id v tabulce recenze
     *
     * twig params:
     *      recenze - v případě, že byl příspěvek už hodnocen, tato proměná obsahuje předešlé vyplněné hodnoty
     */
    public function hodnotit($id) {
        $this->authorization(self::RECENZENT);
        if(!isset($id))
            $this->redirect($this->makeURL('administrace:krecenzi'),'Chyba v požadavku');

        $recenze = $this->recenze->getKonkretniRecenzi($id);

        if(!$recenze || $recenze->stav != self::RECENZOVANO)
            $this->redirect($this->makeURL('administrace:krecenzi'),'Nelze recenzovat');

        echo $this->twig->render('/administrace/hodnotit.twig',[
            'recenze' => $recenze
        ]);
    }

    /**
     * Funkce zajišťuje uložení výsledků hodnocení
     *
     * @param $id - odpovídá id v tabulce recenze
     */
    public function ohodnotitPrispevek($id){
        $this->authorization(self::RECENZENT);
        if(!isset($id))
            $this->redirect($this->makeURL('administrace:krecenzi'),'Chyba v požadavku');

        if(isset($_POST['hodnoceni'])) {
            $this->recenze->ulozitRecenzi($id,$_POST['hodnoceni']);
            $this->redirect($this->makeURL('administrace:krecenzi'),'Recenze byla uložena','success');
        } else {
            $this->redirect($this->makeURL('administrace:krecenzi'),'Došlo k chybě při přenosu dat');
        }
    }

    /*----------------------------------------------------------------------------------------------
   ----------------------------------- ADMIN ----------------------------------------------------
   ----------------------------------------------------------------------------------------------*/

    /**
     * Funkce zobrazí stránku obsahující příspěvky, které jsou recenzovány
     *
     * twig params:
     *      prispevky - všechny právě recenzované příspěvky
     *      min_recenzentu - minimální počet recenzí pro přijetí/zamítnutí příspěvku
     */
    public function spravaRecenzi() {
        $this->authorization(self::ADMIN);
        echo $this->twig->render('/administrace/spravarecenzi.twig',[
            'prispevky' => $this->recenze->getPrispevky(),
            'min_recenzentu' => self::MIN_POCET_RECENZENTU
        ]);
    }

    /**
     * Funkce zobrazí stránku s formuláře pro přidělení recenzentů k příspěvku
     *
     * @param $id - odpovídá id v tabulce prispevky
     *
     * twig params:
     *      recenzenti - recenzenti, kteří tento příspěvek nehodnotí
     *      recenze - všechny recenze, které patří k tomuto příspěvku
     *      clanek_id - id příspěvku
     */
    public function spravaRecenzentu($id) {
        $this->authorization(self::ADMIN);
        if(!isset($id))
            $this->redirect($this->makeURL('administrace:spravarecenzi'),'Chyba v požadavku');

        $recenzePrispevku = $this->recenze->getRecenzePodlePrispevku($id);

        echo $this->twig->render('/administrace/prideleni.twig',[
            'recenzenti' => $this->recenze->getVolneRecenzenty($id),
            'recenze' => $recenzePrispevku,
            'clanek_id' => htmlspecialchars($id)
        ]);
    }

    /**
     * Funkce zajistí přidělení recenzentů podle hodnot předaných formulářem
     */
    public function priraditRecenzenty() {
        $this->authorization(self::ADMIN);
        if(isset($_POST['recenzenti']) && isset($_POST['id'])) {
            $this->recenze->pridelitRecenze($_POST['recenzenti'],$_POST['id']);
            $this->redirect($this->makeURL('administrace:spravarecenzi'),'Recenzent/i přidělen/i','success');
        } else {
            $this->redirect($this->makeURL('administrace:spravarecenzi'),'Došlo k chybě při přenosu dat');
        }
    }

    /**
     * Funkce smaže z tabulky recenze recenzi odpovídající předanému id
     *
     * @param $id - odpovídá recenzi, která bude smazána
     */
    public function odebratRecenzenta($id) {
        $this->authorization(self::ADMIN);
        if(!isset($id))
            $this->redirect($this->makeURL('administrace:spravarecenzi'),'Chyba v požadavku');

        $this->recenze->odebratRecenzi($id);
        $this->redirect($this->makeURL('administrace:spravarecenzi'),'Recenzent odebrán');
    }

    /**
     * Funkce označí příspěvek s předaným id jako přijatý
     *
     * @param $id - odpovídá id v tabulce příspěvky
     */
    public function prijmoutPrispevek($id) {
        $this->authorization(self::ADMIN);
        if(!isset($id))
            $this->redirect($this->makeURL('administrace:spravarecenzi'),'Chyba v požadavku');

        $this->prispevky->zmenitStav($id,'přijat');
        $this->redirect($this->makeURL('administrace:spravarecenzi'),'Příspěvek byl přijat','success');
    }

    /**
     * Funkce označí příspěvek s předaným id jako odmítnutý
     *
     * @param $id - odpovídá id v tabulce příspěvky
     */
    public function zamitnoutPrispevek($id) {
        $this->authorization(self::ADMIN);
        if(!isset($id))
            $this->redirect($this->makeURL('administrace:spravarecenzi'),'Chyba v požadavku');

        $this->prispevky->zmenitStav($id,'odmítnut');
        $this->redirect($this->makeURL('administrace:spravarecenzi'),'Příspěvek byl zamítnut');
    }

        // ------------------------------- UZIVATELE --------------------------------------------

    /**
     * Funkce zobrazí stránku s přehledem všech uživatelů
     *
     * twig params:
     *      uzivatele - všichni registrovaní uživatelé
     */
    public function uzivatele() {
        $this->authorization(self::ADMIN);

        echo $this->twig->render('/administrace/uzivatele.twig',[
            'uzivatele' => $this->um->getUzivatele()
        ]);
    }

    /**
     * Funkce zobrazí stránku obsahující všechny detaily uživatele s předaným nickem
     *
     * @param $nick - nick uživatele
     *
     * twig params:
     *      uzivatel - údaje o uživatel s předaným nickem
     *      roles - všechny role z tabulky role
     */
    public function detailUzivatele($nick) {
        $this->authorization(self::ADMIN);

        if(!isset($nick))
            $this->redirect($this->makeURL('administrace:uzivatele'),'Chyba v požadavku');

        $uzivatel = $this->um->getKonkretnihoUzivatele($nick);

        if(!$uzivatel)
            $this->redirect($this->makeURL('administrace:uzivatele'),'Požadovaný uživatel neexistuje');

        echo $this->twig->render('/administrace/detailuzivatele.twig',[
            'uzivatel' => $uzivatel,
            'roles' => $this->um->getRole()
        ]);
    }

    /**
     * Funkce změní heslo uživateli podle předaných údajů z formuláře
     */
    public function zmenitHeslo() {
        $this->authorization(self::ADMIN);
        if(isset($_POST['uzivatel'])) {
            $this->um->changePassword($_POST['uzivatel']);
            $this->redirect($this->makeURL('administrace:uzivatele'),'Heslo bylo změněno','success');
        } else {
            $this->redirect($this->makeURL('administrace:uzivatele'),'Došlo k chybě při přenosu dat');
        }
    }

    /**
     * Funkce změní údaje uživateli podle předaných údajů z formuláře
     */
    public function zmenitUdaje() {
        $this->authorization(self::ADMIN);
        if(isset($_POST['uzivatel'])) {
            $this->um->zmenitUdaje($_POST['uzivatel']);
            $this->redirect($this->makeURL('administrace:uzivatele'),'Změny uloženy','success');
        } else {
            $this->redirect($this->makeURL('administrace:uzivatele'),'Došlo k chybě při přenosu dat');
        }
    }

    /**
     * Funkce odstraní uživatele z tabulky uzivatele podle předané přezdívky
     *
     * @param $nick - přezdívka uživatele
     */
    public function smazatUzivatele($nick) {
        $this->authorization(1);
        if(!isset($nick))
            $this->redirect($this->makeURL('administrace:uzivatele'),'Chyba v požadavku');

        $this->um->smazatUzivatele($nick);
        $this->redirect($this->makeURL('administrace:uzivatele'),'Uživatel smazán');
    }
}