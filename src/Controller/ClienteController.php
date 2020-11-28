<?php

namespace src\Controller;

use function GuzzleHttp\json_decode;

require('config.php');
require('util.php');

class ClienteController {

    private $requestMethod;
    private $requestEndPoint;
    private $config;
    private $util;

    public function __construct($requestMethod,$requestEndPoint)
    {
        $this->requestMethod = $requestMethod;
        $this->requestEndPoint = $requestEndPoint;
        $this->config = new config();
        $this->util = new util();
    }

    public function processRequest()
    {
        switch ($this->requestMethod) { 

            case 'POST':
               if($this->requestEndPoint === "consultarCep")
                {
                    $response = $this->cep();
                    break; 
                }

                if($this->requestEndPoint === "tts")
                {
                    $response = $this->tts();
                    break; 
                }
                if($this->requestEndPoint === "audio")
                {
                    $response = $this->audio();
                    break; 
                }
                 
                if($this->requestEndPoint === "usuarios")
                {
                    $response = $this->consultarusuario();
                    break; 
                }
            case 'GET':
                if($this->requestEndPoint === "consultar")
                {
                    $response = $this->consultar();
                    break;
                }
            case 'PUT':
                if($this->requestEndPoint === "atualizar")
                {
                    $response = $this->atualizar();
                    break;
                }
            case 'DELETE':
                if($this->requestEndPoint === "deletar")
                {
                    $response = $this->delete();
                    break;
                }               

            default:

                $response = $this->util->notFoundResponse();
                break;

        }

        header($response['status_code_header']);

        if ($response['body']) {
            echo $response['body'];
        }

    }
      
         private function tts()
       {

    //1- primeiro vou capturar todos os parâmetros de entrada
    $input=(array)json_decode(file_get_contents("php://input"),true);
    $numero_destino=$input['numero_destino'];
    $mensagem=$input['mensagem'];
    $resposta_usuario=$input['resposta_usuario'];
    $tipo_voz=$input['tipo_voz'];
    $bina=$input['bina'];
    $gravar_audio=$input['gravar_audio'];
    $detecta_caixa=$input['detecta_caixa'];
    $bina_inteligente=$input['bina_inteligente'];
    $token=$input['token'];

    //2 - Vou criar o json para enviar para a API externa
    $jsonExterno = array(
        "numero_destino" => $numero_destino,
        "mensagem" => $mensagem,
        "resposta_usuario" => $resposta_usuario == "true" ? true : false,
        "tipo_voz" => $tipo_voz,
        "bina" => $bina,
        "gravar_audio" => $gravar_audio  == "true" ? true : false,
        "detecta_caixa" => $detecta_caixa  == "true" ? true : false,
        "bina_inteligente" => $bina_inteligente == "true" ? true : false
    );

  
    //3 - Vou alterar o header e incluir o campo de token
    $header = array(
        "Access-Token:".$token
    );

    //4 - Vou fazer a chamada da API Externa 
    $uri = "https://api.totalvoice.com.br/tts";   
    $body = $this->util->CallApi($uri,'POST',$header,$jsonExterno);
    $json  = json_encode($body);
    $decode = json_decode($json,TRUE);

    //5 - Vou retornar no formato esperado
    /*
        {
            "variables":{
                "avi_id":"",
                "avi_status":"",
                "avi_mensagem":""
            }
        }
     */
    $retorno = array("variables" => array(
        "avi_id"=> $decode['dados']['id'],
        "avi_status" =>$decode['status'],
        "avi_mensagem" =>$decode['mensagem']
    ));
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($retorno);
    return $response;        
}

        private function audio()

{
    
        $input=(array)json_decode(file_get_contents("php://input"),true);
        $numero_destino=$input['numero_destino'];
        $url_audio=$input['url_audio'];
        $resposta_usuario=$input['resposta_usuario'];
        $bina=$input['bina'];
        $gravar_audio=$input['gravar_audio'];
        $detecta_caixa=$input['detecta_caixa'];
        $token=$input['token'];
        $bina_inteligente=$input['bina_inteligente'];
        
        $jsonEx = array(
        "numero_destino" => $numero_destino,
        "url_audio" => $url_audio,
        "resposta_usuario" => $resposta_usuario == "true" ? true : false,
        "bina" => $bina,
        "gravar_audio" => $gravar_audio == "true" ? true : false,
        "detecta_caixa" => $detecta_caixa  == "true" ? true : false,
        "bina_inteligente" => $bina_inteligente == "true" ? true : false

    );

        $header = array(
        "Access-Token:".$token
        
    );
    
        $uri = "https://api.totalvoice.com.br/audio";   
        $body = $this->util->CallApi($uri,'POST',$header,$jsonEx);
        $json  = json_encode($body);
        $decode = json_decode($json,TRUE);

        $retorno = array("variables" => array(
        "avi_status"=> $decode['status'],
        "avi_motivo" =>$decode['motivo'],
        "avi_mensagem" =>$decode['mensagem'],
        "avi_id" =>$decode['dados']['id']

    ));

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($retorno);
        return $response;     

}

    //nessa função monto um exemplo para consultar uma API externa
    //viacep.com.br/ws/01001000/json/

    private function cep()
    {
        //aqui capturo o body da requisição, nesse campo o campo cep do json
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $cep = !!isset($input['cep']) ? $input['cep'] : "";

        //aqui eu monto a URL
        $uri = "viacep.com.br/ws/".$cep."/json/";   

        //esse é o header da requisição, vou adicionar alguns itens padrão aqui 
        //somente para exemplificar, entretanto o header é mais utilizado quando tem 
        // authorization
        $header = array(
            "Content-Type:application/json; charset=utf-8"
        );

        //chamo uma função auxilizar que está na classe util, para fazer a comunicação com a API
        //via cCurl, ficar atento para o tipo de método, nesse caso é um GET
        try
        {
            $body = $this->util->CallApi($uri,'GET',$header,"");

            $retorno= array("variables"=> array(
                "cep_cliente"=> $body->cep,
                "logradouro"=> $body->cep,
                "bairro"=> $body->bairro,

            ));

            //se deu tudo certo retorno o http code como 200 e o retorno da api externa
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
    
            $response['body'] = json_encode($retorno);
        } 
        catch(Exception $e)
        {
            $jsonError = array("erro" => $e->getMessage());

            //caso tenha dado algum erro, retorno http code como 500 e a mensagem de erro
            $response['status_code_header'] = 'HTTP/1.1 500 INTERNAL SERVER ERROR';
            $response['body'] = json_encode($jsonError);
        }
        return $response;
    }

    
    private function consultarusuario()
    {
        //aqui capturo o body da requisição, nesse campo o campo cep do json
       

        //aqui eu monto a URL
        $uri = "https://gorest.co.in/public-api/comments";   

        //esse é o header da requisição, vou adicionar alguns itens padrão aqui 
        //somente para exemplificar, entretanto o header é mais utilizado quando tem 
        // authorization
        $header = array(
            "Content-Type:application/json; charset=utf-8"
        );

        //chamo uma função auxilizar que está na classe util, para fazer a comunicação com a API
        //via cCurl, ficar atento para o tipo de método, nesse caso é um GET
        try
        {
            $body = $this->util->CallApi($uri,'GET',$header,"");

           
            $retorno= array ("variables"=>$body);
            //se deu tudo certo retorno o http code como 200 e o retorno da api externa
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
    
            $response['body'] = json_encode($retorno);
        } 
        catch(Exception $e)
        {
            $jsonError = array("erro" => $e->getMessage());

            //caso tenha dado algum erro, retorno http code como 500 e a mensagem de erro
            $response['status_code_header'] = 'HTTP/1.1 500 INTERNAL SERVER ERROR';
            $response['body'] = json_encode($jsonError);
        }
        return $response;
    }

    private function consultar()

    {
        //aqui capturo os parâmetros da querystring
        $id = !!isset($_GET['id']) ? $_GET['id']: "";

        //verifico se o parâmtro está vazio, se não tiver monto um json, se tiver retorno http code 204
        if(!empty($id))
        {
            //monto um retorno qualquer somente para exemplificar
            $retorno = array("retorno" => array("id" => $id, "nome" => "Gabriel", "Sobrenome" => "E.Santo", "idade" => "32"));

            //se deu tudo certo retorno o http code como 200
            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($retorno);
        }
        else
        {
            $response['status_code_header'] = 'HTTP/1.1 204 No Content';
            $response['body'] = "";
        }
        return $response;
    }

    private function atualizar()
    {
        //aqui capturo o body da requisição, nesse campo o campo cep do json
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $id = !!isset($input['id']) ? $input['id'] : "";
        $nome= !!isset($input['nome']) ? $input['nome'] : "";
        $sobrenome= !!isset($input['sobrenome']) ? $input['sobrenome'] : "";
        $idade= !!isset($input['idade']) ? $input['idade'] : "";

        //se o campo id tiver vazio retornamos 204
        if(empty($id))
        {
            $response['status_code_header'] = 'HTTP/1.1 204 No Content';
            $response['body'] = "";
            return $response;
        }

        //aqui poderia ter o processo de atualização no banco de dados

        //aqui monto o json com os dados atualizados e com o http code 201
        $retorno = array("retorno" => array("id" => $id, "nome" => $nome,"Sobrenome" => $sobrenome, "idade" =>  $idade));

        //se deu tudo certo retorno o http code como 200 e o retorno da api externa
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode($retorno);

        return $response;
    }

    private function delete()
    {
         //aqui capturo os parâmetros da querystring
         $id = !!isset($_GET['id']) ? $_GET['id']: "";

         //verifico se o parâmtro está vazio, se não tiver monto um json, se tiver retorno http code 204
         if(!empty($id))
         {
            
             //se deu tudo certo retorno o http code como 200
             $response['status_code_header'] = 'HTTP/1.1 200 OK';
             $response['body'] = "";
         }
         else
         {
             $response['status_code_header'] = 'HTTP/1.1 204 No Content';
             $response['body'] = "";
         }
         return $response;
    }
}

?>