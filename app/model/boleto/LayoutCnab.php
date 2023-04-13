<?php
/**
 * LayoutCnab Active Record
 * @author  <your-name-here>
 */
class LayoutCnab extends TRecord
{
    const TABLENAME = 'layout_cnab';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $banco;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo_transacao');
        parent::addAttribute('padrao_arquivo');
        parent::addAttribute('id_banco');
        parent::addAttribute('remesa_retorno');
        parent::addAttribute('tipo_registro');
        parent::addAttribute('seguimento');
        parent::addAttribute('sequencia');
        parent::addAttribute('descricao');
        parent::addAttribute('posicao_inicial');
        parent::addAttribute('posicao_final');
        parent::addAttribute('posicao_total');
        parent::addAttribute('formato');
        parent::addAttribute('padrao');
        parent::addAttribute('comando');
    }

    
    /**
     * Method set_banco
     * Sample of usage: $layout_cnab->banco = $object;
     * @param $object Instance of Banco
     */
    public function set_banco(Banco $object)
    {
        $this->banco = $object;
        $this->id_banco = $object->id;
    }
    
    /**
     * Method get_banco
     * Sample of usage: $layout_cnab->banco->attribute;
     * @returns Banco instance
     */
    public function get_banco()
    {
        // loads the associated object
        if (empty($this->banco))
            $this->banco = new Banco($this->id_banco);
    
        // returns the associated object
        return $this->banco;
    }
    


}
