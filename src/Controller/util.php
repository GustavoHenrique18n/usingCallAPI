<?php

namespace src\Controller;
use vendor;
require './vendor/autoload.php';

class util
{
    public function CallApi($url, $methodType, $headers, $json)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        
        if($methodType == "POST"){
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        }

        if($methodType == "PATCH"){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);

        $retorno = curl_exec($ch);
        $errRet = curl_error($ch);
        curl_close($ch);

        echo($errRet);
        return json_decode($retorno);
    }

    public function logMe($msg){
        // Abre ou cria o arquivo bloco1.txt
        // "a" representa que o arquivo é aberto para ser escrito
        $fp = fopen("log.txt", "a");
        
        // Escreve a mensagem passada através da variável $msg
        $escreve = fwrite($fp, $msg);
        
        // Fecha o arquivo
        fclose($fp);
    }

    public function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

    public function IsNullOrEmptyString($str){
        return (!isset($str) || trim($str) === '');
    }
}

?>