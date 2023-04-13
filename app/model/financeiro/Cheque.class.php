<?php
/**
 * Cheque Active Record
 * @author  <your-name-here>
 */
class Cheque extends TRecord
{
    const TABLENAME = 'cheque';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('documento');
        parent::addAttribute('condominio_id');
        parent::addAttribute('mes_referencia');
        parent::addAttribute('dt_emissao');
        parent::addAttribute('dt_vencimento');
        parent::addAttribute('dt_liquidacao');
        parent::addAttribute('cheque');
        parent::addAttribute('valor');
        parent::addAttribute('nominal_a');
        parent::addAttribute('conta_fechamento_id');
    }

    /**
     *
     */
    public function addContasPagar(ContasPagar $cheque)
    {
        $object = new ChequeContaspagar;
        $object->contas_pagar_id = $cheque->id;
        $object->cheque_id = $this->id;
        $object->store();
    }
    
    /**
     *
     */
    public function getContasPagar()
    {
        $system_programs = array();
        
        // load the related System_program objects
        $repository = new TRepository('ChequeContaspagar');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cheque_id', '=', $this->id));
        
        $system_group_system_programs = $repository->load($criteria);
        
        if ($system_group_system_programs)
        {
            foreach ($system_group_system_programs as $system_group_system_program)
            {
                $system_programs[] = new ContasPagar( $system_group_system_program->contas_pagar_id );
            }
        }
        
        return $system_programs;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        // delete the related System_groupSystem_program objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cheque_id', '=', $this->id));
        $repository = new TRepository('ChequeContaspagar');
        $repository->delete($criteria);
    }
    
    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_groupSystem_program objects
        $id = isset($id) ? $id : $this->id;
        $repository = new TRepository('ChequeContaspagar');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cheque_id', '=', $id));
        $repository->delete($criteria);
        
        // delete the object itself
        parent::delete($id);
    }
    

}
