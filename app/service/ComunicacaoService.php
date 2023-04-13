<?php
/**
 * Comunicacao REST service
 */
class ComunicacaoService extends AdiantiRecordService
{
    const DATABASE      = 'facilitasmart';
    const ACTIVE_RECORD = 'Comunicacao';
    
    public static function getComunicacao( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id']));     
        
        $limite = 50;
        $param1['order'] = 'data_lancamento'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
            
        // carrega 
        $all = Comunicacao::getObjects( $criteria );
        foreach ($all as $arquivo)
        {
            $response[] = $arquivo->toArray();
        }
        TTransaction::close();
        return $response;
    }
    
    public static function getAvisos( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id']));     
        $criteria->add(new TFilter('tipo', '=', 1)); 
        $criteria->add(new TFilter('status', '=', 'Y'));  //se está ativo 
        
        $limite = 50;
        $param1['order'] = 'data_lancamento'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
            
        // carrega 
        $all = Comunicacao::getObjects( $criteria );
        foreach ($all as $arquivo)
        {
            $response[] = $arquivo->toArray();
        }
        TTransaction::close();
        return $response;
    }
    
    public static function getServicos( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id']));     
        $criteria->add(new TFilter('tipo', '=', 2)); 
        $criteria->add(new TFilter('status', '=', 'Y'));  //se está ativo 
        
        $limite = 50;
        $param1['order'] = 'data_lancamento'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
            
        // carrega 
        $all = Comunicacao::getObjects( $criteria );
        foreach ($all as $arquivo)
        {
            $response[] = $arquivo->toArray();
        }
        TTransaction::close();
        return $response;
    }
    
 }
