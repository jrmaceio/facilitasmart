<?php
/**
 * PontoRonda Active Record
 * @author  <your-name-here>
 */
class PontoRonda extends TRecord
{
    const TABLENAME = 'ponto_ronda';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('intevalo_minutos');
        parent::addAttribute('posto_id');
        parent::addAttribute('obrigatorio');
        parent::addAttribute('unidade_id');
        parent::addAttribute('latitude');
        parent::addAttribute('longitude');
    }

    public function set_posto(Posto $object)
    {
        $this->posto = $object;
        $this->posto_id = $object->id;
    }

    public function get_posto()
    {
        // loads the associated object
        if (empty($this->posto))
            $this->posto = new Posto($this->posto_id);
    
        // returns the associated object
        return $this->posto;
    }
    
    public function set_unit(SystemUnit $object)
    {
        $this->unit = $object;
        $this->unidade_id = $object->id;
    }
    
    /**
     * Returns the unit
     */
    public function get_unit()
    {
        // loads the associated object
        if (empty($this->unit))
            $this->unit = new SystemUnit($this->unidade_id);
    
        // returns the associated object
        return $this->unit;
    }

}
