<?php
/**
 * Ronda Active Record
 * @author  <your-name-here>
 */
class Ronda extends TRecord
{
    const TABLENAME = 'ronda';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('unidade_id');
        parent::addAttribute('tipo_id');
        parent::addAttribute('hora_ronda');
        parent::addAttribute('data_ronda');
        parent::addAttribute('descricao');
        parent::addAttribute('status_tratamento');
        parent::addAttribute('patrulheiro_id');
        parent::addAttribute('ponto_ronda_id');
        parent::addAttribute('posto_id');
        parent::addAttribute('latitude');
        parent::addAttribute('longitude');
        parent::addAttribute('data_hora_atualizacao');
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
    
    public function set_patrulheiro(Patrulheiro $object)
    {
        $this->patrulheiro = $object;
        $this->patrulheiro_id = $object->id;
    }

    public function get_patrulheiro()
    {
        // loads the associated object
        if (empty($this->patrulheiro))
            $this->patrulheiro = new Patrulheiro($this->patrulheiro_id);
    
        // returns the associated object
        return $this->patrulheiro;
    }

    public function set_ponto_ronda(PontoRonda $object)
    {
        $this->ponto_ronda = $object;
        $this->ponto_ronda_id = $object->id;
    }

    public function get_ponto_ronda()
    {
        // loads the associated object
        if (empty($this->ponto_ronda))
            $this->ponto_ronda = new PontoRonda($this->ponto_ronda_id);
    
        // returns the associated object
        return $this->ponto_ronda;
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
