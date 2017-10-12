<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Service\ClientHttp;

class IndexController extends AbstractRestfulController {

    public function indexAction() {
        return new ViewModel();
    }

    /**
     * Obtem as informações dos veículos
     * @return JsonModel
     */
    public function listAction() {
        //Total de paginas de veiculos a ser buscada, padrão busca somente uma
        $tipo = ($this->params()->fromRoute("tipo"))?$this->params()->fromRoute("tipo"):1;
        $total = ($this->params()->fromRoute("total"))?$this->params()->fromRoute("total"):1;
        
        $client = new ClientHttp();
        
        $dados = array();
        $veiculos = array();
        for($i = 1; $i <= $total; $i++){
            $dom = $client->getList($tipo,$i);

            $total_pag = $dom->execute('.total')[0]->textContent;
            $res = $this->organizaArrayJsonList($dom);
            $dados[$i] = $res;
        }
        
        //somente organizando os indices
        $i = 0;
        foreach ($dados as $cada_dado) {
            foreach ($cada_dado as $value) {
                $veiculos[$i] = $value;
                $i++;
            }
        }

        return new JsonModel($veiculos);
    }

    /**
     * Obtem as informações dos detalhes do veículo
     * @return JsonModel
     */
    public function listDetailsAction() {
        $id = $this->params()->fromRoute("id");

        $client = new ClientHttp();

        $dom = $client->getListDetails($id);

        $res = $this->organizaArrayJsonListDetails($dom);

        return new JsonModel($res);
    }

    /**
     * Função para tratar organizar as informações dos veiculos
     * @param Zend\Dom\Query $dom
     * @return array
     */
    private function organizaArrayJsonList($dom) {

        $result = $dom->execute('.bg-busca');

        $array = array();
        foreach ($result as $i => $result) {

            $h4 = explode('R$', $result->getElementsByTagName('h4')[0]->textContent);

            $array[$i]['nome'] = $h4[0];
            $array[$i]['preco'] = $h4[1];
            $array[$i]['imagem'] = $result->getElementsByTagName('img')[0]->getAttribute('src');

            foreach ($result->getElementsByTagName('p') as $j => $opcoes) {
                $array[$i]['opcoes'][] = trim($opcoes->textContent);
            }

            foreach ($result->getElementsByTagName('span') as $j => $opcoes) {
                //removendo a opção aceito troca e preço
                if ($j == 0 || $j == 1) {
                    continue;
                }
                $array[$i]['acessorios'][] = trim($opcoes->textContent);
            }
        }

        return $array;
    }

    /**
     * Função para tratar organizar as informações de detalhe do veiculos
     * @param Zend\Dom\Query $dom
     * @return array
     */
    private function organizaArrayJsonListDetails($dom) {

        $array = array();
        $result = $dom->execute('#textoBoxVeiculo');

        //dados veiculo
        $array['nome'] = $result[0]->getElementsByTagName('h5')[0]->textContent;
        $array['preco'] = $result[0]->getElementsByTagName('p')[0]->textContent;

        //Busca as fotos do veículo
        $fotos = $dom->execute('#conteudoVeiculo');
        foreach ($fotos[0]->getElementsByTagName('img') as $i => $ft) {
            $array['imagens'][$i] = $ft->getAttribute('src');
        }

        //Busca as detalhes do veículo
        $detalhes = $dom->execute('#infDetalhes');
        foreach ($detalhes[0]->getElementsByTagName('li') as $i => $dt) {
            $array['detalhes'][$i] = $dt->textContent;
        }

        //Busca as acessorios do veículo
        $acessorios = $dom->execute('#infDetalhes2');
        foreach ($acessorios[0]->getElementsByTagName('li') as $i => $dt) {
            $array['acessorios'][$i] = $dt->textContent;
        }

        //Busca as observações do veículo
        $observacoes = $dom->execute('#infDetalhes3');
        $array['observacoes'] = $observacoes[0]->getElementsByTagName('p')[0]->textContent;

        //Busca o contato
        $contato = $dom->execute('#infDetalhes4')[0]->getElementsByTagName('ul')[0];
        foreach ($contato->getElementsByTagName('li') as $i => $ct) {
            $array['contato'][$i] = trim($ct->textContent);
        }

        return $array;
    }

}