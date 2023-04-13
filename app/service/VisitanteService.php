<?php
/**
 * Visitante REST service
 */
class VisitanteService extends AdiantiRecordService
{
    const DATABASE      = 'facilitasmart';
    const ACTIVE_RECORD = 'Visitante';
    
    public static function getVisitante( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id']));
        $criteria->add(new TFilter('unidade_id', '=', $param['unidade_id'])); // não mostra agenda de uma unidade, somente a publica 
        $limite = 50;
        $param1['order'] = 'id'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
 
        // carrega
        $all = Visitante::getObjects( $criteria );
        foreach ($all as $agenda)
        {
            $response[] = $agenda->toArray();
        }
        
        TTransaction::close();
        return $response;
    }
}
