<?php
/**
 * OcorrenciaUnidade Active Record
 * @author  <your-name-here>
 */
class OcorrenciaUnidade extends TRecord
{
    const TABLENAME = 'ocorrencia_unidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    //use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('unidade_id');
        parent::addAttribute('tipo_id');
        parent::addAttribute('data_ocorrencia');
        parent::addAttribute('hora_ocorrencia');
        parent::addAttribute('descricao');
        parent::addAttribute('data_proximo_contato');
        parent::addAttribute('resposta');
        parent::addAttribute('status');
        parent::addAttribute('condominio_id');
        parent::addAttribute('system_user_login');
        parent::addAttribute('datahora_cadastro');
        //parent::addAttribute('atualizacao');
    }


}
