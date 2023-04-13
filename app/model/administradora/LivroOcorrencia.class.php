<?php
/**
 * LivroOcorrencia Active Record
 * @author  <your-name-here>
 */
class LivroOcorrencia extends TRecord
{
    const TABLENAME = 'livro_ocorrencia';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        //parent::addAttribute('datahora_cadastro');
        parent::addAttribute('condominio_id');
        parent::addAttribute('unidade_id');
        parent::addAttribute('pessoa');
        parent::addAttribute('data_ocorrencia');
        parent::addAttribute('hora_ocorrencia');
        parent::addAttribute('descricao');
        parent::addAttribute('system_user_login');
        parent::addAttribute('system_user_email');
        parent::addAttribute('status_tratamento');
        parent::addAttribute('data_tratamento');
        parent::addAttribute('conclusao_tratamento');
        //parent::addAttribute('atualizacao');
    }


}
