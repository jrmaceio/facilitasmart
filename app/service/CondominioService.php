<?php
/**
 * Visitante REST service
 */
class CondominoService extends AdiantiRecordService
{
    const DATABASE      = 'facilitasmart';
    const ACTIVE_RECORD = 'Pessoas';
    
    public static function getCondomino( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('email', '=', $param['email']));
        
        $limite = 1;
        $param1['order'] = 'id'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
 
        // carrega
        $all = Pessoas::getObjects( $criteria );
        foreach ($all as $pessoa)
        {
            $response[] = $pessoa->toArray();
        }
        
        TTransaction::close();
        return $response;
    }
}
