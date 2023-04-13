<?php
/**
 * Fechamento Active Record
 * @author  <your-name-here>
 */
class Fechamento extends TRecord
{
    const TABLENAME = 'fechamento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('condominio_id');
        parent::addAttribute('conta_fechamento_id');
        parent::addAttribute('mes_ref');
        parent::addAttribute('previsao_arrecadacao');
        parent::addAttribute('taxa_inadimplencia');
        parent::addAttribute('dt_fechamento');
        parent::addAttribute('dt_inicial');
        parent::addAttribute('dt_final');
        parent::addAttribute('saldo_inicial');
        parent::addAttribute('receita');
        parent::addAttribute('despesa');
        parent::addAttribute('saldo_final');
        parent::addAttribute('nota_explicativa');
        parent::addAttribute('status');
        parent::addAttribute('mostra_fechamento');
        parent::addAttribute('atualizacao');
    }


}
