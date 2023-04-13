<?php
/**
 * contas_receber Active Record
 * @author  <your-name-here>
 */
class ContasReceber extends TRecord
{
  
    const TABLENAME = 'contas_receber';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
    
    
    private $imoveis;
    private $plano_contas;
    private $unidades;

   // use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        //parent::addAttribute('id');
        parent::addAttribute('condominio_id');
        parent::addAttribute('mes_ref');
        parent::addAttribute('cobranca');
        parent::addAttribute('tipo_lancamento');
        parent::addAttribute('classe_id');
        parent::addAttribute('unidade_id');
        parent::addAttribute('nome_responsavel'); // nome da unidade na epoca
        parent::addAttribute('dt_lancamento');
        parent::addAttribute('dt_vencimento');
        parent::addAttribute('valor');
        parent::addAttribute('descricao');
        parent::addAttribute('situacao');
        parent::addAttribute('dt_pagamento');
        parent::addAttribute('dt_liquidacao');
        parent::addAttribute('conta_fechamento_id');
        parent::addAttribute('valor_pago');
        parent::addAttribute('desconto');
        parent::addAttribute('juros');
        parent::addAttribute('multa');
        parent::addAttribute('correcao');
        parent::addAttribute('tarifa');
        parent::addAttribute('valor_creditado');
        parent::addAttribute('nosso_numero');
        parent::addAttribute('arquivo_retorno');
        parent::addAttribute('numero_acordo');
        parent::addAttribute('parcela');
        parent::addAttribute('dt_vencimento_original');
        parent::addAttribute('dt_pagamento_banco');
        parent::addAttribute('boleto_status');
        
        parent::addAttribute('dt_primeira_cobranca');
        parent::addAttribute('tipo_primeira_cobranca');
        parent::addAttribute('dt_segunda_cobranca');
        parent::addAttribute('tipo_segunda_cobranca');
        parent::addAttribute('dt_terceira_cobranca');
        parent::addAttribute('tipo_terceira_cobranca');
        
        parent::addAttribute('mensagem_boleto_cobranca');
        parent::addAttribute('local_pagamento_boleto_cobranca');
        parent::addAttribute('documento_boleto_cobranca');
        parent::addAttribute('multa_boleto_cobranca');
        parent::addAttribute('juros_boleto_cobranca');
        parent::addAttribute('correcao_boleto_cobranca');
        parent::addAttribute('desconto_boleto_cobranca');
        parent::addAttribute('dt_limite_desconto_boleto_cobranca');
        
        parent::addAttribute('dt_boleto_enviado_email');

        parent::addAttribute('dt_ultima_alteracao');
        parent::addAttribute('usuario');
        
        parent::addAttribute('pjbank_id_unico');
        parent::addAttribute('pjbank_token_facilitador');
        parent::addAttribute('pjbank_credencial');
        parent::addAttribute('pjbank_linkBoleto');
        parent::addAttribute('pjbank_linkGrupo');
        parent::addAttribute('pjbank_linhaDigitavel');
        parent::addAttribute('pjbank_banco_numero');
        parent::addAttribute('pjbank_pedido_numero');
    
        parent::addAttribute('id_conta_corrente');
        
        // split de pagamento pjbank
        parent::addAttribute('split_active'); // y sim ou N não
        parent::addAttribute('split_administradora_id'); // é o id da cadastro de condominio (identificando a administradora que recebe o split da cobranca)
        parent::addAttribute('split_valor_fixo');
        parent::addAttribute('split_valor_porcentagem');
        parent::addAttribute('split_porcentagem_encargos');
        parent::addAttribute('split_porcentagem_multa');
        parent::addAttribute('split_porcentagem_juros');
        parent::addAttribute('split_porcentagem_desconto');
        
    }

    public function set_unidade(Unidade $object)
    {
        $this->unidade = $object;
        $this->unidade_id = $object->id;
    }

    public function get_unidade()
    {
        // loads the associated object
        if (empty($this->unidade))
            $this->unidade = new Unidade($this->unidade_id);
    
        // returns the associated object
        return $this->unidade;
    }

    public function set_condominio(Condominio $object)
    {
        $this->condominio = $object;
        $this->condominio_id = $object->id;
    }

    public function get_condominio()
    {
        // loads the associated object
        if (empty($this->condominio))
            $this->condominio = new Condominio($this->condominio_id);
    
        // returns the associated object
        return $this->condominio;
    }
    

    public function set_conta_corrente(ContaCorrente $object)
    {
        $this->conta_corrente = $object;
        $this->id_conta_corrente = $object->id;
    }

    public function get_conta_corrente()
    {
        // loads the associated object
        if (empty($this->conta_corrente))
            $this->conta_corrente = new ContaCorrente($this->id_conta_corrente);
    
        // returns the associated object
        return $this->conta_corrente;
    }
    
    
    
    /**
     * Valor do recebimento para conciliação
     */
    public static function getValorRecebido($dt_credito, $condominio_id)
    {
        $conn = TTransaction::get();
        $result = $conn->query("SELECT sum(valor_creditado) as valor_creditado
                                 FROM contas_receber
                                 WHERE dt_liquidacao = '{$dt_credito}' and situacao = 1 and
                                 condominio_id = {$condominio_id}");
        
        $data = [];
        
        if ($result)
        {
            foreach ($result as $row)
            {
                $data = $row['valor_creditado'];
            }
        }
        
        var_dump($data);
        return $data;
    }
    
    // confirmação se pode fazer o lancamento / edicao / exclusao
    // status 0 -> fechamento aberto possibilidade de fazer alterações
    // status 1 -> fechado, nao altera nada
    public static function retornaStatusFechamento($condominio_id, $mes_ref, $conta_fechamento_id)
    {        
        $conn = TTransaction::get();
        
        $result = $conn->query("select status
                               from fechamento 
                               where condominio_id = '{$condominio_id}' 
                               and mes_ref = '{$mes_ref}'
                               and conta_fechamento_id = '{$conta_fechamento_id}'
                               ");
        
        //default = 1 fechado, não permite nada
        $data = 1;
        
        //var_dump($result);
        
        foreach ($result as $row)
        {
            $data = $row['status'];
        }

        return $data;
   
    }

    public static function retornaLancamentosNossoNumero($nosso_numero)
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('nosso_numero', '=', $nosso_numero));
        $criteria->add(new TFilter('condominio_id', '=', TSession::getValue('id_condominio')));
        $criteria->add(new TFilter('mes_ref', '=', TSession::getValue('mesref')));
        
        $repository = new TRepository('ContasReceber');
        $lancamentos = $repository->load($criteria);
        
        $retorno = '';
        
        foreach($lancamentos as $lancamento)
        {
            $retorno = $lancamento;
        }
        
        return $retorno;
        
    }
  
 

    /**
     * Receita por mês
     */
    public static function getReceitaMes($ano,$condominio_id)
    {
        $conn = TTransaction::get();
        $result = $conn->query("SELECT MONTH(dt_liquidacao),
                                       sum(valor_pago)
                                 FROM contas_receber
                                 WHERE YEAR(dt_liquidacao) = '$ano' and situacao = 1 and
                                 condominio_id = {$condominio_id}
                                 GROUP BY 1
                                 ORDER BY 1");
        
        $data = [];
        if ($result)
        {
            foreach ($result as $row)
            {
                $mes   = $row[0];
                $valor = $row[1];
                
                $data[ $mes ] = $valor;
            }
        }
        
        return $data;
    }
    
    /**
     * Receita Analitica por mês
     */
    public static function getReceitaAnaliticaMes($ano,$condominio_id, $classe_id )
    {
        $conn = TTransaction::get();
        $result = $conn->query("SELECT MONTH(dt_liquidacao),
                                       sum(valor_pago)
                                 FROM contas_receber
                                 WHERE YEAR(dt_liquidacao) = '$ano' and situacao = 1 and
                                 condominio_id = {$condominio_id} and
                                 classe_id = {$classe_id}
                                 GROUP BY 1
                                 ORDER BY 1");
        
        $data = [];
        if ($result)
        {
            foreach ($result as $row)
            {
                $mes   = $row[0];
                $valor = $row[1];
                
                $data[ $mes ] = $valor;
            }
        }
        
        return $data;
    }
    
    /**
     * Receita Analitica por mês e unidade - usado na planilha de gerecimento
     */
    public static function getReceitaAnaliticaMesUnidade($ano, $condominio_id, $unidade_id )
    {
        $conn = TTransaction::get();
        $result = $conn->query("SELECT MONTH(dt_liquidacao),
                                       sum(valor_pago)
                                 FROM contas_receber
                                 WHERE YEAR(dt_liquidacao) = '$ano' and situacao = 1 and
                                 condominio_id = {$condominio_id} and
                                 unidade_id = {$unidade_id}
                                 GROUP BY 1
                                 ORDER BY 1");
        
        $data = [];
        if ($result)
        {
            foreach ($result as $row)
            {
                $mes   = $row[0];
                $valor = $row[1];
                
                $data[ $mes ] = $valor;
            }
        }
        
        return $data;
    }
}
