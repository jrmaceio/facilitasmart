     <?php
    class Mapa extends TPage
    {
        /**
         * Class constructor
         * Creates the page
         */
         function __construct()
        {
            parent::__construct();
            
            TPage::include_css('app/resources/styles.css');
            $html1 = new THtmlRenderer('app/resources/mapa.html');
            
            // replace the main section variables
            $html1->enableSection('main', array());
                   
            $panel1 = new TPanelGroup('Mapa!');
            $panel1->add($html1);
            
               
            // add the template to the page
            parent::add( TVBox::pack($panel1) );
        }
    }
    ?> 



