<?php
/**
 * Reserva Active Record
 * @author  <your-name-here>
 */
class Reserva extends TRecord
{
    const TABLENAME = 'reserva';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_area_comum');
        parent::addAttribute('id_unidade');
        parent::addAttribute('confirmacao_cadastro');
        parent::addAttribute('data_reserva');
        parent::addAttribute('hora_inicio');
        parent::addAttribute('hora_fim');
        parent::addAttribute('valor_reserva');
        parent::addAttribute('observacao');
        parent::addAttribute('condominio_id');
    }


}
