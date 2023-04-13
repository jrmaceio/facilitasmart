<?php
/**
 * AppController Active Record
 * @author  <your-name-here>
 */
class AppController extends TRecord
{
    const TABLENAME = 'app_controller';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('email');
        parent::addAttribute('unidade_id');
        parent::addAttribute('nome');
        parent::addAttribute('condominio_id');
        parent::addAttribute('modelo_telefone');
        parent::addAttribute('codigo_validacao');
        parent::addAttribute('status_liberacao');
        parent::addAttribute('unidade_informada');
        parent::addAttribute('telefone_informado');
        parent::addAttribute('condominio_informado');
        parent::addAttribute('facilitasmart_user_id');
        parent::addAttribute('ronda_user_id');
        parent::addAttribute('tipo'); // tipo 1 = morador, 2 = administradora, 3 - supervisão ronda
        parent::addAttribute('registro_atualizacao');
    }


}
