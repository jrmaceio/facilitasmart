<?php
/**
*
*/
class QuadroAvisoView extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
    
        TPage::include_css('app/resources/styles.css');
        
        $html1 = new THtmlRenderer('app/resources/quadroaviso.html');

        $panel1 = new TPanelGroup('Quadro de Avisos');
        
         // seta as configurações do usuario
        $cond = TSession::getValue('userunitids');
        $condominio_id = $cond[0];
        
        TTransaction::open('facilitasmart');
        $conn = TTransaction::get(); 
        $sql = "SELECT * FROM comunicacao 
                    where 
                    condominio_id = {$condominio_id} and 
                    tipo='1' and 
                    status='Y' 
                    order by data_lancamento desc LIMIT 3";
        
        $colunas = $conn->query($sql);
        TTransaction::close();
        
        $replaces = array();
        
        $i = 1;
        
        foreach ($colunas as $coluna) 
        { 
            $replaces['titulo'.$i] = $coluna['titulo'];
            $replaces['conteudo'.$i] = $coluna['conteudo'];
            $replaces['rodape'.$i] = $coluna['rodape'];
            
            $i++;
          
                          
        } 
       
        // replace the main section variables
        $html1->enableSection('main', $replaces);
        $panel1->add($html1);
        
        // add the template to the page
        parent::add( TVBox::pack($panel1) );
    }
}
