<?php
/**
 * UsuarioCondominio Active Record
 * @author  <your-name-here>
 *
 * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
 * 0 - Desenvolvedor
 * 1 - Administradora
 * 2 - Gestor
 * 3 - Portaria
 * 4 - Morador
 */
class UsuarioCondominio extends TRecord
{
    const TABLENAME = 'usuario_condominio';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('ativo');
        parent::addAttribute('pessoa_id');
        parent::addAttribute('system_user_login');
        parent::addAttribute('condominio_id');
        parent::addAttribute('dt_envio_senha');
        parent::addAttribute('dt_ult_cobranca');
        parent::addAttribute('dt_ult_aviso');
        parent::addAttribute('unidade_id');
        parent::addAttribute('nivel_acesso_inf');
    }


}
