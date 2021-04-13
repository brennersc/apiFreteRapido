<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Cotacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CotacaoController extends Controller
{
    public function quote()
    {
        $arrayDados = array(
            'remetente' => array(
                'cnpj' => '17184406000174'
            ),
            'destinatario' => array(
                'endereco' => array(
                    'cep' => '01311000'
                ),
            ),
            'volumes' => array(
                [
                    'tipo' => 7,
                    'quantidade' => 1,
                    'peso' => 5,
                    'valor' => 349,
                    'sku' => 'abc-teste-123',
                    'altura' => 0.2,
                    'largura' => 0.2,
                    'comprimento' => 0.2
                ],
                [
                    'tipo' => 7,
                    'quantidade' => 2,
                    'peso' => 4,
                    'valor' => 556,
                    'sku' => 'abc-teste-527',
                    'altura' => 0.4,
                    'largura' => 0.6,
                    'comprimento' => 0.15
                ]
            ),
            'codigo_plataforma' => '588604ab3',
            'token' => 'c8359377969ded682c3dba5cb967c07b'
        );

        try {
            $client = new Client();

            $response = $client->post('https://freterapido.com/api/external/embarcador/v1/quote-simulator', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($arrayDados)
            ]);

            $result             = $response->getBody();
            $arrayRetornos      = json_decode($result);
            $arrayMontarLista   = array();


            foreach ((object)$arrayRetornos->transportadoras as $transportadora) {
                $arrayDetalhe = array(

                    'nome'              => $transportadora->nome,
                    'servico'           => $transportadora->servico,
                    'prazo_entrega'     => $transportadora->prazo_entrega,
                    'custo_frete'       => $transportadora->custo_frete,

                );

                array_push($arrayMontarLista, $arrayDetalhe);

                $cotacao = new Cotacao();
                $cotacao->oferta        = $transportadora->oferta;
                $cotacao->cnpj          = $transportadora->cnpj;
                $cotacao->logotipo      = $transportadora->logotipo;
                $cotacao->nome          = $transportadora->nome;
                $cotacao->servico       =  $transportadora->servico;
                $cotacao->prazo_entrega = $transportadora->prazo_entrega;
                $cotacao->entrega_estimada  = $transportadora->entrega_estimada;
                $cotacao->validade          = $transportadora->validade;
                $cotacao->custo_frete       = $transportadora->custo_frete;
                $cotacao->preco_frete       = $transportadora->preco_frete;
                $cotacao->save();
            }

            $arrayFinal = array("transportadoras" => $arrayMontarLista);
            return response()->json($arrayFinal);

        } catch (RequestException $e) {
            $error = json_decode($e->getResponse()->getBody(), true);
            return $error;
        }
    }
    public function metrics(Request $request)
    {
        //return Cotacao::all(); //Todas Cotações

        $query = 'SELECT nome as Transportadora, 
                        count(*) as Quantidade, 
                        format(sum(preco_frete),2) as Preço_total, 
                        format(avg(preco_frete),2) as Media,
                        min(preco_frete) as minimo,
                        max(preco_frete) as maximo 
                    FROM cotacaos                                   
                    group by nome 
                    '.($request->last_quotes != '' ? "LIMIT $request->last_quotes" : null).' ';

        $transportadora = DB::SELECT($query);

        return $transportadora;
    }
}