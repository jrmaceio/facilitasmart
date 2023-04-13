<?php
/**
 * WelcomeView
 *
 */
class ConfiguraAcesso extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        //$html1 = new THtmlRenderer('app/resources/ConfiguraAcesso.html');
        $html2 = new THtmlRenderer('app/resources/ConfiguraAcesso.html');

        // replace the main section variables
        //$html1->enableSection('main', array());
        $html2->enableSection('main', array());
        
        //$panel1 = new TPanelGroup('Welcome!');
        //$panel1->add($html1);
        
        $panel2 = new TPanelGroup('Bem-vindo!');
        $panel2->add($html2);
        
        // open a transaction with database 'facilitasmart'
        TTransaction::open('facilitasmart');
            
        // verifica o nivel de acesso do usuario
        // * Usa o campo nivel_acesso_inf para definir que nivel de acesso a informações é o usuário :
        // * 0 - Desenvolvedor
        // * 1 - Administradora
        // * 2 - Gestor
        // * 3 - Portaria
        // * 4 - Morador
        $users = UsuarioCondominio::where('system_user_login', '=', TSession::getValue('login'))->load();
        foreach ($users as $user)
            {
                $nivel_acesso = $user->nivel_acesso_inf;
                $condominio_id = $user->condominio_id;
            }
        
        TTransaction::close();
        
        // grava resultado
        TSession::setValue('id_condominio', $condominio_id);
        
        $datahoje = date('Y-m-d');
        $partes = explode("-", $datahoje);
        
        $ano_hoje = $partes[0];
        $mes_hoje = $partes[1];
        
        $mes_ant  = ((int) $mes_hoje ) -1;
        $mes_ant  = str_pad($mes_ant, 2, "0", STR_PAD_LEFT); 
        
        $dia_hoje = $partes[2];
        
        if ( $mes_hoje == '1' ) {
          $mes_ant= '12';
          $ano_hoje = ((int) $ano_hoje ) -1;
        }
        
        TSession::setValue('mesref', $mes_ant . '/' . $ano_hoje);
            
        //$vbox = TVBox::pack($panel1, $panel2);
        $vbox = TVBox::pack($panel2);
        $vbox->style = 'display:block; width: 90%';
        
        // add the template to the page
        parent::add( $vbox );
    }
}

