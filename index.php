<?php
header("Access-Control-Allow-Orgin: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json");


include("modules/SxGeo.php"); 
require_once('vendor/autoload.php');
require("parser/parser.php");
require("core/cache.php");
require("core/error.php");
require("core/bd.php");
require("parser/drom.php");
require("parser/auto_ru.php");
require("parser/avito.php");
require("itemparsers/CarItem.php");
require("itemparsers/phoneitem.php");

class App extends Parsing {

    public $data;
    public $method;
    public $q;
    public $base;
    

    function __construct($bd)
    {
        $this->base = $bd;
        $this->method = explode( '/', $_GET['q'] );
        $this->q = $_GET['q'];

        if($_POST) {
            $this->data = $_POST;
        } else {
            $this->data = json_decode( file_get_contents("php://input"), true );
        }
        parent::__construct($this->data , $this->method, $this->base);

    }


    public function StartApp() {
        switch ($this->method[0]) {
            case 'parse':
                if(!empty($this->method[1]) && !empty( $this->method[2]) ) {
                    parent::GetData();
                } else {
                    ErrorApp::Err();
                }
                break;
            case 'refresh':
                	parent::Refresh();
                break;
            case 'feed':
                if(!empty($this->method[1])) {
                    parent::Feed($this->method[1]);
                } else {
                    ErrorApp::Err();
                }
                break;
            case 'getCaritem':
                if(!empty($this->method[1]) && !empty($this->data['url'])) {
                    $url = $this->data['url'];
                    CarItem::ItemCreator( $url, $this->method[1] , $this->base);
                } else {
                    ErrorApp::Err();
                }
                break;
            case 'getphoneinfo':
                if(!empty($this->method[1])) {
                    PhoneItem::ItemCreator($this->method[1] , $this->base);
                } else {
                    ErrorApp::Err();
                }
                break;
            default:
                ErrorApp::Err();
                break;
        }
    }
}


$app = new App($conn);
$app->StartApp();