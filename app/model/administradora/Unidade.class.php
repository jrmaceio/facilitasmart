<?php
/**
 * Unidade Active Record
 * @author  <your-name-here>
 */
class Unidade extends TRecord
{
    const TABLENAME = 'unidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    //use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('bloco_quadra');
        parent::addAttribute('descricao');
        parent::addAttribute('condominio_id');
        parent::addAttribute('proprietario_id');
        parent::addAttribute('morador_id');
        parent::addAttribute('fracao_ideal');
        parent::addAttribute('observacao');
        parent::addAttribute('gera_titulo');
        parent::addAttribute('grupo_id');
        parent::addAttribute('valor_titulo');
        parent::addAttribute('desconto_titulo');
        parent::addAttribute('texto_adicional_titulo');
        parent::addAttribute('texto_complemento_titulo');
        parent::addAttribute('envio_boleto');
        parent::addAttribute('senha_enviada');
        parent::addAttribute('acesso_id');
    }

    public function get_proprietario_telefones()
    {
        // cria a classe proprietario
        if (empty($this->proprietario))
        {
            //                        classe modelo (model)
            $this->proprietario = new Pessoa($this->proprietario_id);
        }
        
        $telefone = $this->proprietario->telefone1;

        if (!empty($this->proprietario->telefone2)){
          $telefone = $telefone . '/' . $this->proprietario->telefone2;
        }

        if (!empty($this->proprietario->telefone3)){
          $telefone = $telefone . '/' . $this->proprietario->telefone3;
        }   

        return $telefone;
    }

    // traz o nome para o formulario
    public function get_proprietario_nome()
    {   
        // cria a classe proprietario
        if (empty($this->proprietario))
        {
            //                        classe modelo (model)
            $this->proprietario = new Pessoa($this->proprietario_id);
        }
        
        return $this->proprietario->id.'-'.$this->proprietario->nome;
        //return $this->proprietario->nome;
    }
    
    public function get_morador_nome()
    {   
        // cria a classe morador
        if (empty($this->morador))
        {
            // classe modelo (model)
            $this->morador = new Pessoa($this->morador_id);
        }
        
        return $this->morador->nome;
    } 
    
    public function get_condominio_resumo()
    {
        if (empty($this->condominio))
        {
            $this->condominio = new Condominio($this->condominio_id);
        }
        
        return $this->condominio->resumo;
    }
    
    public function get_tipo_envio_boleto()
    {
        $envio = array('1'=>'ND', '2'=>'Condomínio', '3'=>'E-mail','4'=>'Correio','5'=>'Whatsapp');
        return $envio[$this->envio_boleto];
    }
    
    public function get_proprietario_cpfcnpj()
    {
        // loads the associated object
        if (empty($this->pessoa))
        {
            $this->pessoa = new Pessoa($this->proprietario_id);
        }

        if (!empty($this->pessoa->cpf)){
          $parte_um     = substr($this->pessoa->cpf, 0, 3);
          $parte_dois   = substr($this->pessoa->cpf, 3, 3);
          $parte_tres   = substr($this->pessoa->cpf, 6, 3);
          $parte_quatro = substr($this->pessoa->cpf, 9, 2);

          $monta_cpf = "$parte_um.$parte_dois.$parte_tres-$parte_quatro";
          return $monta_cpf;
        }

        if (!empty($this->pessoa->cnpj)){
          $parte_um     = substr($this->pessoa->cnpj, 0, 2);
          $parte_dois   = substr($this->pessoa->cnpj, 2, 3);
          $parte_tres   = substr($this->pessoa->cnpj, 5, 3);
          $parte_quatro = substr($this->pessoa->cnpj, 8, 4);
          $parte_cinco  = substr($this->pessoa->cnpj, 12, 2);

          $monta_cpf = "$parte_um.$parte_dois.$parte_tres/$parte_quatro-$parte_cinco";
          return $monta_cpf;
        }   
    }
    
    public function get_proprietario_email()
    {
        // loads the associated object
        if (empty($this->pessoa))
        {
            $this->pessoa = new Pessoa($this->proprietario_id);
        }
        // returns the associated object
        return $this->pessoa->email;
    }
    
    public static function RetornaUnidadesCondominio($condominio_id)
    {
     
        $conn = TTransaction::get();
        $result = $conn->query("select 
                                id, 
                                descricao, 
                                gera_titulo,
                                grupo_id,
                                valor_titulo,
                                desconto_titulo,
                                texto_adicional_titulo 
                                from unidade
                                where condominio_id = {$condominio_id}
                               ");
      
        
              
        foreach ($result as $row)
        {
            $intervalo[] = $row;
        }
        
        //var_dump($intervalo);
        
        return $intervalo;
    }

}
