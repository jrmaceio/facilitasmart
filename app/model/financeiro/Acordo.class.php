<?php
/**
 * Acordo Active Record
 * @author  <your-name-here>
 */
class Acordo extends TRecord
{
    const TABLENAME = 'acordo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('data_dia');
        parent::addAttribute('condominio_id');
        parent::addAttribute('unidade_id');
        parent::addAttribute('data_base_acordo');
        parent::addAttribute('parcelas');
        parent::addAttribute('observacao');
        parent::addAttribute('classe_id');
        parent::addAttribute('valor_lancado');
        parent::addAttribute('valor_projetado');
        parent::addAttribute('multa');
        parent::addAttribute('juros');
        parent::addAttribute('correcao');
        parent::addAttribute('acrescimo');
        parent::addAttribute('desconto');
        parent::addAttribute('honorario1');
        parent::addAttribute('honorario2');
        parent::addAttribute('descricao_honorario1');
        parent::addAttribute('descricao_honorario2');
        parent::addAttribute('atualizacao');
    }


}