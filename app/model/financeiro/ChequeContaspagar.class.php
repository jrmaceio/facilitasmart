<?php
/**
 * ChequeContaspagar Active Record
 * @author  <your-name-here>
 */
class ChequeContaspagar extends TRecord
{
    const TABLENAME = 'cheque_contaspagar';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cheque_id');
        parent::addAttribute('contas_pagar_id');
    }


}
