<?php
/**
 * @author  <your name here>
 */
class BoletoTesteRemessa extends TPage
{
    protected $form;     // search form
    //protected $datagrid; // listing
    //protected $pageNavigation;


    public function __construct() 
    { 
        parent::__construct(); 
        
        // creates the form
        $this->form = new TForm('form_BoletoTesteRetorno');
        $this->form->class = 'tform'; // CSS class
        $this->form->style = 'max-width:700px'; // style
        parent::include_css('app/resources/custom-frame.css');
        
        parent::add(new TLabel('Boleto')); 
        $this->onVerifica(); 

        // create the page container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);

    } 

public function onVerifica() 
{ 
///include 'vendor/autoload.php';

$codigo_banco = Cnab\Banco::ITAU;
$arquivo = new Cnab\Remessa\Cnab400\Arquivo($codigo_banco);
$arquivo->configure(array(
    'data_geracao'  => new DateTime(),
    'data_gravacao' => new DateTime(), 
    'nome_fantasia' => 'Nome Fantasia da sua empresa', // seu nome de empresa
    'razao_social'  => 'Razão social da sua empresa',  // sua razão social
    'cnpj'          => '111', // seu cnpj completo
    'banco'         => $codigo_banco, //código do banco
    'logradouro'    => 'Logradouro da Sua empresa',
    'numero'        => 'Número do endereço',
    'bairro'        => 'Bairro da sua empresa', 
    'cidade'        => 'Cidade da sua empresa',
    'uf'            => 'Sigla da cidade, ex SP',
    'cep'           => 'CEP do endereço da sua cidade',
    'agencia'       => '1111', 
    'conta'         => '22222', // número da conta
    'conta_dac'     => '2', // digito da conta
));

  // você pode adicionar vários boletos em uma remessa
  $arquivo->insertDetalhe(array(
    'codigo_ocorrencia' => 1, // 1 = Entrada de título, futuramente poderemos ter uma constante
    'nosso_numero'      => '1234567',
    'numero_documento'  => '1234567',
    'carteira'          => '109',
    'especie'           => Cnab\Especie::ITAU_DUPLICATA_DE_SERVICO, // Você pode consultar as especies Cnab\Especie
    'valor'             => 100.39, // Valor do boleto
    'instrucao1'        => 2, // 1 = Protestar com (Prazo) dias, 2 = Devolver após (Prazo) dias, futuramente poderemos ter uma constante
    'instrucao2'        => 0, // preenchido com zeros
    'sacado_nome'       => 'Nome do cliente', // O Sacado é o cliente, preste atenção nos campos abaixo
    'sacado_tipo'       => 'cpf', //campo fixo, escreva 'cpf' (sim as letras cpf) se for pessoa fisica, cnpj se for pessoa juridica
    'sacado_cpf'        => '111.111.111-11',
    'sacado_logradouro' => 'Logradouro do cliente',
    'sacado_bairro'     => 'Bairro do cliente',
    'sacado_cep'        => '11111222', // sem hífem
    'sacado_cidade'     => 'Cidade do cliente',
    'sacado_uf'         => 'SP',
    'data_vencimento'   => new DateTime('2014-06-08'),
    'data_cadastro'     => new DateTime('2014-06-01'),
    'juros_de_um_dia'     => 0.10, // Valor do juros de 1 dia'
    'data_desconto'       => new DateTime('2014-06-01'),
    'valor_desconto'      => 10.0, // Valor do desconto
    'prazo'               => 10, // prazo de dias para o cliente pagar após o vencimento
    'taxa_de_permanencia' => '0', //00 = Acata Comissão por Dia (recomendável), 51 Acata Condições de Cadastramento na CAIXA
    'mensagem'            => 'Descrição do boleto',
    'data_multa'          => new DateTime('2014-06-09'), // data da multa
    'valor_multa'         => 10.0, // valor da multa
    ));

    // para salvar    
    $arquivo->save('boletos/remessa/meunomedearquivo.txt');

}

}



