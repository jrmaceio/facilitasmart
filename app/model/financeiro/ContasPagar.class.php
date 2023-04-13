<?php
/**
 * ContasPagar Active Record
 * @author  <your-name-here>
 */
class ContasPagar extends TRecord
{
    const TABLENAME = 'contas_pagar';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
   
    //use SystemChangeLogTrait;
     
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('condominio_id');
        parent::addAttribute('mes_ref');
        parent::addAttribute('tipo_lancamento');
        parent::addAttribute('classe_id');
        parent::addAttribute('documento');
        parent::addAttribute('dt_lancamento');
        parent::addAttribute('dt_vencimento');
        parent::addAttribute('valor');
        parent::addAttribute('descricao');
        parent::addAttribute('situacao');
        parent::addAttribute('dt_pagamento');
        parent::addAttribute('dt_liquidacao');
        parent::addAttribute('valor_pago');
        parent::addAttribute('desconto');
        parent::addAttribute('juros');
        parent::addAttribute('multa');
        parent::addAttribute('correcao');
        parent::addAttribute('conta_fechamento_id');
        parent::addAttribute('tipo_pagamento_id');
        parent::addAttribute('numero_doc_pagamento');

        parent::addAttribute('linha_digitavel');
        parent::addAttribute('nome_favorecido');
        parent::addAttribute('cnpj_cpf_favorecido');
        parent::addAttribute('solicitante_pagamento');
        parent::addAttribute('status_conta_digital');
        parent::addAttribute('id_operacao_conta_digital');

        parent::addAttribute('parcela');
        parent::addAttribute('file');
        parent::addAttribute('usuario');
        parent::addAttribute('data_atualizacao');
    }


    public function get_classe_descricao()
      {
           if (empty($this->classe))
            $this->classe = new PlanoContas($this->classe_id);
    
           // returns the associated object
           return $this->classe->descricao;
      }

    /**
     * Valor do pagamento para conciliação do arquivo ofx
     */
    public static function getValorPago($documento, $condominio_id)
    {
        $conn = TTransaction::get();
        $result = $conn->query("SELECT sum(valor_pago) as valor_pago
                                 FROM contas_pagar
                                 WHERE numero_doc_pagamento = {$documento} and situacao = 1 and
                                 condominio_id = {$condominio_id}");

        $data = [];

        if ($result)
        {
            foreach ($result as $row)
            {
                $data = $row['valor_pago'];
            }
        }
        
        return $data;
    }
    
    /**
     * Despesas por mês
     */
    public static function getDespesaMes($ano,$condominio_id)
    {
        $conn = TTransaction::get();
        $result = $conn->query("SELECT MONTH(dt_liquidacao),
                                       sum(valor_pago)
                                 FROM contas_pagar
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
     * Despesas Analitica por mês
     */
    public static function getDespesaAnaliticaMes($ano,$condominio_id, $classe_id )
    {
        $conn = TTransaction::get();
        $result = $conn->query("SELECT MONTH(dt_liquidacao),
                                       sum(valor_pago)
                                 FROM contas_pagar
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
    
}
