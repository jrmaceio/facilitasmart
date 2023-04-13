<?php
/**
 * Chart
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class DashboardViewFechamento extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        
        $div = new TElement('div');
        $div->add( $a=new GraficoReceitaDespesaResultadoDash(false) );
        $div->add( $b=new GraficoReceitaDespesaDash(false) );
        $div->add( $c=new GraficoReceitaClassificacaoContaDash(false) );
        $div->add( $d=new GraficoDespesaClassificacaoContaDash(false) );
        
        $div->add( $e=new GraficoInadimplenciaMesAnoDash(false) );
        $div->add( $f=new GraficoInadimplenciaBlocoQuadraDash(false) );
        
        $div->add( $g=new GraficoInadimplenciaClassificacaoContaDash(false) );
        $div->add( $h=new GraficoInadimplenciaClassificacaoContaDash(false) );
        
        $a->style = 'width:50%;float:left;padding:10px';
        $b->style = 'width:50%;float:left;padding:10px';
        $c->style = 'width:50%;float:left;padding:10px';
        $d->style = 'width:50%;float:left;padding:10px';
        
        $e->style = 'width:50%;float:left;padding:10px';
        $f->style = 'width:50%;float:left;padding:10px';
        
        $g->style = 'width:50%;float:left;padding:10px';
        $h->style = 'width:50%;float:left;padding:10px';
        
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($div);
        
        parent::add($vbox);
        
        try
        {
            TTransaction::open('facilitasmart');
            $condominio = new Condominio(TSession::getValue('id_condominio')); 
            //$logado = Imoveis::retornaImovel();
            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', '<b>Error</b> ' . $e->getMessage()); // shows the exception error message
        }
        
        parent::add(new TLabel('Mês de Referência : ' . TSession::getValue('mesref') . ' / Condomínio : ' . 
                        TSession::getValue('id_condominio')  . ' - ' . $condominio->resumo));
    }
}
