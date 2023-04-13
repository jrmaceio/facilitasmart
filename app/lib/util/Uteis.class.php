<?php
class Uteis 
{
    
    /**
     * method numeroBrasil()
     * recebe um numero, pode ser float, do tipo do brasil 
     * e o transforma num numero do tipo ingles (ex. 1,524.36)
     * @param $num string com os numeros 
     * @returns string com o valor formatado
     */    
    public static function numeroBrasil($num, $decimal = 2) 
    {                
        if ( ($num) || ($num == 0) )
        {
            if ($num == '')
            {
                $num = 0;
            }
            return number_format($num, $decimal, ',', '.');
        }                    
    }
    
    
    /**
     * method numeroIngles()
     * recebe um numero, pode ser float, do tipo do brasileiro 
     * e o transforma num numero aceito pelo banco de dados (ex. 1524.36)
     * @param $num string com os numeros 
     * @returns string com os valor formatado
     */    
    public static function numeroIngles($num) 
    {        
        if ( ($num) || ($num == 0) )
        {        
            if ($num == '')
            {
                $num = 0;
            }
            $source  = array('.', ',');
            $replace = array('', '.');                                          
            return str_replace($source, $replace, $num); //remove os pontos e substitui a virgula pelo ponto    
        }    
    }
    
    
    /**
     * method pegarApenasNumeros()
     * retorna apenas os numeros de uma string 
     * @param $str string com os numeros 
     * @returns string apenas com os caracteres numericos
     */    
    public static function pegarApenasNumeros($str) {
        if ( ($str) || ($str == 0) )
        {
            return preg_replace("/[^0-9]/", "", $str);
        }
    }
    
    
    /**
     * method getFormatCPF()
     * retorna uma string formatada como CPF (999.999.999-99)
     * @param $str string que se quer formatar
     * @returns string formatada como CPF
     */
    public static function getFormatCPF($str) {
        if ( ($str) || ($str == 0) )
        {
            $formatado  = substr( $str, 0, 3 ) . '.';
            $formatado .= substr( $str, 3, 3 ) . '.';
            $formatado .= substr( $str, 6, 3 ) . '-';
            $formatado .= substr( $str, 9, 2 ) . '';
            return $formatado;
        }
    } 
    /**
     * method getFormatCNPJ()
     * retorna uma string formatada como CNPJ (99.999.999/9999-99)
     * @param $str string que se quer formatar
     * @returns string formatada como CNPJ
     */
    public static function getFormatCNPJ($str) {
        if ( ($str) || ($str == 0) )
        {
            $formatado  = substr( $str, 0, 2 ) . '.';
            $formatado .= substr( $str, 2, 3 ) . '.';
            $formatado .= substr( $str, 5, 3 ) . '/';
            $formatado .= substr( $str, 8, 4 ) . '-';
            $formatado .= substr( $str, 12, 2 ) . '';            
            return $formatado;
        }
    } 
    
    public static function getFormatRG($str) {
        if ( ($str) || ($str == 0) )
        {
            $formatado  = substr( $str, 0, 1 ) . '.';
            $formatado .= substr( $str, 1, 3 ) . '.';
            $formatado .= substr( $str, 4, 3 ) . '';

            return $formatado;
        }
    }           
    
    public static function getFormatCEP($str) {
        if ( ($str) || ($str == 0) )
        {
            $formatado  = substr( $str, 0, 2 ) . '.';
            $formatado .= substr( $str, 3, 3 ) . '.';
            $formatado .= substr( $str, 3, 3 ) . '-';
            return $formatado;
        }
    } 
    
    
    /**
     * method formataPeso()
     * formata $peso da TDataGrid com um decimal
     * @param $valor é o valor da coluna que se quer formatar
     * @param $objeto é o stdClass com os dados que estão representados na linha da datagrid
     * @param $row é a própria linha da datagrid
     * @returns $valor retorna o $valor formatado
     */
    public static function formataPeso($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return number_format($valor, 1);
        }    
    }
    
    
    /**
     * method formataData()
     * formata $data_cadastro da TDataGrid com o padrão brasileiro
     * @param $valor é o valor da coluna que se quer formatar
     * @param $objeto é o stdClass com os dados que estão representados na linha da datagrid
     * @param $row é a própria linha da datagrid
     * @returns $valor com a data formatada no padrão brasileiro
     */
    public static function formataData($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return TDate::date2br($valor);
        }        
    }
        

    public static function formataDataIngles($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return TDate::date2us($valor);
        }        
    }
    
    /**
     * method formataAltura()
     * formata $peso da TDataGrid com 2 decimais
     * @param $valor é o valor da coluna que se quer formatar
     * @param $objeto é o stdClass com os dados que estão representados na linha da datagrid
     * @param $row é a própria linha da datagrid
     * @returns $valor retorna o $valor formatado
     */
    public function formataAltura($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return number_format($valor, 2);
        }        
    }
    
    
    /**
     * method formataMoeda()
     * formata valores da TDataGrid com 2 decimais no padrao Brasil
     * @param $valor é o valor da coluna que se quer formatar
     * @param $objeto é o stdClass com os dados que estão representados na linha da datagrid
     * @param $row é a própria linha da datagrid
     * @returns $valor retorna o $valor formatado
     */
    public function formataMoeda($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return number_format($valor, 2, ',', '.');
        }        
    }
    
    
    /**
     * method formataAtivo()
     * formata $ativo da TDataGrid (Ativo/Inativo)
     * @param $valor é o valor da coluna que se quer formatar
     * @param $objeto é o stdClass com os dados que estão representados na linha da datagrid
     * @param $row é a própria linha da datagrid
     * @returns $valor com formatado
     */
    public function formataAtivo($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return 'Ativo';
        }
        else
        {
            return 'Inativo';
        }        
    }
    
    
    /**
     * method formataCPF()
     * formata valores da TDataGrid no padrao do CPF
     * @param $valor é o valor da coluna que se quer formatar
     * @param $objeto é o stdClass com os dados que estão representados na linha da datagrid
     * @param $row é a própria linha da datagrid
     * @returns $valor retorna o $valor formatado
     */
    public static function formataCPF($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return Uteis::getFormatCPF($valor);
        }        
    }
    
    /**
     * method formataCNPJ()
     * formata valores da TDataGrid no padrao do CNPJ
     * @param $valor é o valor da coluna que se quer formatar
     * @param $objeto é o stdClass com os dados que estão representados na linha da datagrid
     * @param $row é a própria linha da datagrid
     * @returns $valor retorna o $valor formatado
     */
    public static function formataCNPJ($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return Uteis::getFormatCNPJ($valor);
        }        
    }


    public static function formataRG($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return Uteis::getFormatRG($valor);
        }        
    }    

    public static function formataCEP($valor, $objeto, $row)
    {
        if ( ($valor) || ($valor == 0) )
        {
            return Uteis::getFormatCEP($valor);
        }        
    }
    
    /**
     * method dataProximoMes()
     * retorna uma data ajustada para a data do proximo mes
     * @param $data data tem no formato ingles (aaaa-mm-dd)
     * @returns $data projetada para o proximo mês
     */
    public static function dataProximoMes($data) {
        //verifica dia maior que 30
        $novaData = explode("-", $data);
        if($novaData[2] > 30) {
            $novaData[2] = 30;
        }
        //verifica se o mes é maior que 12 (dezembro) e se o dia de fevereiro é maior que 28
        $novaData[1]++;
        switch($novaData[1]) {
            case 13:
                $novaData[1] = '01';
                $novaData[0]++;
            break;
            case 14:
                $novaData[1] = '01';
                $novaData[0]++;
            break;
            case 2:
                if($novaData[2] > 28) {
                    $novaData[2] = 28;
                }
            break;
            case 3:
                if($novaData[2] == 28) {
                    $novaData[2] = 30;
                }
            break;
        }
        //preenche com zero à esquerda o dia e mês
        $novaData[2] = str_pad($novaData[2], 2, '0', STR_PAD_LEFT);
        $novaData[1] = str_pad($novaData[1], 2, '0', STR_PAD_LEFT);
        $novaDataVenc = implode("-", $novaData);
        return $novaDataVenc;
    }
    
    
    
    public static function numeroEsquerda($num, $tam = 6) 
    {                
        if ( ($num) || ($num == 0) )
        {
            if ($num == '')
            {
                $num = 0;
            }
            return str_pad($num , $tam , '0' , STR_PAD_LEFT);
        }                    
    }
    

    /**
     * method buscaNome()
     * retorna o nome de acordo com o id
     * $id -> chave que vc tem
     * $tabela = onde irá buscar
     * $campo = qual campo que vc tem ($id)
     * $tipo = qual campo irá encontrar
     */     
    public static function buscaNome($id,$tabela,$campo,$tipo) 
    {                
        if ($id)
        {
            TTransaction::open('facilitasmart');
            $reg_nome = $tabela::where($campo, '=', $id)->load();
            $nome = '';
            foreach($reg_nome as $value_nome)
            {
                $nome = $value_nome->$tipo;
            }
            TTransaction::close();
            return ($nome);
        }
    }
    
    
    public static function geraCampo($tipo,$editable = null,$objectX,$form,$value,$tamanho = NULL,$exit = null,$session = null,$combo = null,$decimal = 2,$pagina) 
    // 01 -> $tipo
    // 02 -> $editable = null
    // 03 -> $objectX
    // 04 -> $form
    // 05 -> $value
    // 06 -> $tamanho = NULL
    // 07 -> $exit = null
    // 08 -> $session = null
    // 09 -> $combo = null
    // 10 -> $decimal = 2
    // 11 -> $pagina
    {
        $exp = explode("-", $value);  
        $variavel = $exp[1];
        if ( ($decimal == '') || ($decimal == 0) ) 
        { 
            $decimal = 2; 
        }
        
        if ($tipo == 'TDBCombo') 
        { 
            $criteria_geral = new TCriteria; $criteria_geral->add(new TFilter('status','=','A')); 
            $criteria_geral->setProperty('order', $combo[5]); 
            $widget = new $tipo($value . '-' . $objectX->id, $combo[0], $combo[1], $combo[2], $combo[3], $combo[4], $criteria_geral); 
            $widget->EnableSearch(); 
        }else
        { 
            if ($tipo == 'TNumeric') 
            { 
                $widget = new $tipo($value . '-' . $objectX->id , $decimal , ',', '.',true); 
            }else 
            { 
                $widget = new $tipo($value . '-' . $objectX->id); 
            } 
        }
        
        if (!isset($objectX->$variavel)) 
        { 
            $objectX->$variavel = ''; 
        }
        $widget->setValue( $objectX->$variavel);
        $widget->setSize(100);
        $widget->setFormName($form);
        
        if ($tamanho) 
        { 
            $widget->setSize($tamanho); 
        }
        if ($tipo == 'TCombo') 
        { 
            $widget->addItems($combo);
        }
        if ($tipo == 'TDate') 
        { 
            $widget->setMask('dd/mm/yyyy'); 
            $widget->setDatabaseMask('yyyy-mm-dd'); 
        }
        if ($tipo == 'TDBCombo' or $tipo == 'TCombo') 
        { 
            $muda = 'setChangeAction';
        }else
        {
            $muda = 'setExitAction'; 
        }
        if ($exit) 
        { 
            $widget->$muda( new TAction( array($pagina, $exit)) ); 
        }
        if ($editable == 'FALSE') 
        { 
            $widget->setEditable(FALSE); 
        }


        if ($session) 
        { 
            if(null != ( TSession::getValue($session))) 
            { 
                $itens = TSession::getValue($session);
                $teste = $itens[$objectX->id];
                if ($pagina == 'NfSaidaForm')
                { 
                    $widget->setValue( $teste->$variavel); 
                }
                else
                { 
                    $widget->setValue( $teste[$variavel]); 
                } 
            } // fim  if(null != ( TSession::getValue($session)))
        } // fim  if ($session)
        
        return $widget;
    }
        
    public static function CriaPasta($caminho , $pasta)
    {
        $criar = $caminho . $pasta;
        if (!is_dir($criar)) { mkdir($criar); }
    }
  
  
      /**
     * method sanitizeString
     * $str --> string a ser analisada
     */    
    public static function sanitizeString($str) 
    {
        $str = preg_replace("/&([a-z])[a-z]+;/i", "", htmlentities(trim($str)));
        return $str;
    }

        
}    /* end of class Uteis */
?>