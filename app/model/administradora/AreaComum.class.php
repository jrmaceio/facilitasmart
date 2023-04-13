<?php
/**
 * AreaComum Active Record
 * @author  <your-name-here>
 */
class AreaComum extends TRecord
{
    const TABLENAME = 'area_comum';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('valor_taxa_locacao');
        parent::addAttribute('tem_taxa');
        parent::addAttribute('intervalo_locacao');
        parent::addAttribute('dias_antecedencia_locacao');
        parent::addAttribute('hora_inicio');
        parent::addAttribute('hora_fim');
        parent::addAttribute('obrigatorio_lista_presenca');
        parent::addAttribute('capacidade');
        parent::addAttribute('intervalo_entre_locacacoes');
        parent::addAttribute('disponibilidade_segunda');
        parent::addAttribute('disponibilidade_terca');
        parent::addAttribute('disponibilidade_quarta');
        parent::addAttribute('disponibilidade_quinta');
        parent::addAttribute('disponibilidade_sexta');
        parent::addAttribute('disponibilidade_sabado');
        parent::addAttribute('disponibilidade_domingo');
        parent::addAttribute('qtd_reservas_dia');
        parent::addAttribute('condominio_id');
    }


}
