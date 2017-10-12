<?php

namespace Application\Service;

use Zend\Http\Client;
use Zend\Dom\Query;

class ClientHttp {
    
    /**
     * Retorna o documento html de uma url informada
     * @param type $url Url do site
     * @return type
     */
    public function getClient($url){
        
        $client = new Client();
        $client->setUri($url);
        
        $response = $client->send();

        $html = '';
        
        if($response->isSuccess()){
            $html = $response->getBody();
        }
        
        return $html;
    }
    
    /**
     * Busca a lista de veículos
     * @return Query
     */
    public function getList($tipo = 1, $pag = 1) {
        switch ($tipo) {
            case 1:
                $url = 'https://www.seminovosbh.com.br/resultadobusca/index/veiculo/carro/usuario/todos/pagina/'.$pag;
                break;
            case 2:
                $url = 'https://www.seminovosbh.com.br/resultadobusca/index/veiculo/moto/usuario/todos/pagina/'.$pag;
                break;
            case 3:
                $url = 'https://www.seminovosbh.com.br/resultadobusca/index/veiculo/caminhao/usuario/todos/pagina/'.$pag;
                break;
            default:
                $url = 'https://www.seminovosbh.com.br/resultadobusca/index/';
                break;
        }

        $html = $this->getClient($url);
        
        return new Query($html);
    }
    
    /**
     * Lista os detalhes dos veículos
     * @param type $id
     * @return Query
     */
    public function getListDetails($id) {
        $url = "https://www.seminovosbh.com.br/comprar/1/2/3/{$id}";
        $html = $this->getClient($url);
        
        return new Query($html);
    }
}
