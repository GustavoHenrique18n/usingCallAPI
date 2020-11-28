<?php

    //Aqui fazemos a importação dos controllers que serão consumidos
    //note que aqui é o nome da classe de cada arquivo .php
    use src\Controller\ClienteController;
        
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    //Aqui nesse trecho falamos para o código que esses controllers são requeridos
    //note que aqui é o endereço físico do arquivo .php
    require('./src/Controller/ClienteController.php');
    

    //Nesse trecho pego a URL que está vindo e quebro ela em array
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode( '/', $uri );

    //verifico se a posição 3 da URL é um dos end-points que estou esperando, se não for 
    //devolvo erro 404
    if ($uri[3] !== 'cliente') {
        header("HTTP/1.1 404 Not Found");
        exit();
    }

    //Nesse trecho pego o método que está vindo da URL
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    $requestEndPoint = $uri[4];

    //aqui valido qual é o endpoint para chamar a classe específica
    //note que em ambas as condições eu chamo o método processRequest esse método é responsável
    //por direcionar para o método correto.
    if($uri[3] === 'cliente')
    {
         $controller = new ClienteController($requestMethod,$requestEndPoint);
         $controller->processRequest();
    }
   
?>